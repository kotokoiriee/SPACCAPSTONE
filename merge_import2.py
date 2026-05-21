with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

php_logic = """
// -- Import Residents Logic --
function ir_normalize_date($val) {
    if (empty($val)) return null;
    $val = trim($val);
    if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $val)) return $val;
    $ts = strtotime($val);
    if ($ts === false) return null;
    return date('Y-m-d', $ts);
}
function ir_parse_zone($area) {
    $area = trim($area);
    if (empty($area)) return [0, ''];
    if (preg_match('/^ZONE\\s*(\\d+)$/i', $area, $m)) return [(int)$m[1], $area];
    return [0, $area];
}
$ir_message = ''; $ir_message_type = ''; $ir_preview_data = []; $ir_import_done = false; $ir_import_stats = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ir_action'])) {
    if ($_POST['ir_action'] === 'preview') {
        $hhid_file = $_FILES['hhid_csv'] ?? null;
        $hhmem_file = $_FILES['hhmem_csv'] ?? null;
        if (!$hhid_file || $hhid_file['error'] !== UPLOAD_ERR_OK) {
            $ir_message = 'Please upload the HHID CSV file.'; $ir_message_type = 'error';
        } else {
            $hhid_rows = [];
            if (($fh = fopen($hhid_file['tmp_name'], 'r')) !== false) {
                $hdrs = array_map('trim', fgetcsv($fh));
                while (($row = fgetcsv($fh)) !== false) {
                    if (count($row) < 2) continue;
                    $hhid_rows[] = array_combine(array_map('strtoupper', $hdrs), array_pad($row, count($hdrs), ''));
                }
                fclose($fh);
            }
            $hhmem_rows = [];
            if ($hhmem_file && $hhmem_file['error'] === UPLOAD_ERR_OK) {
                if (($fh = fopen($hhmem_file['tmp_name'], 'r')) !== false) {
                    $hdrs = array_map('trim', fgetcsv($fh));
                    while (($row = fgetcsv($fh)) !== false) {
                        if (count($row) < 2) continue;
                        $hhmem_rows[] = array_combine(array_map('strtoupper', $hdrs), array_pad($row, count($hdrs), ''));
                    }
                    fclose($fh);
                }
            }
            $fk_col = null;
            if (!empty($hhmem_rows)) {
                $hhmem_cols = array_keys($hhmem_rows[0]);
                foreach (['ID','HHIDID','HHID_ID','HHID','HH_ID'] as $c) { if (in_array($c, $hhmem_cols)) { $fk_col = $c; break; } }
            }
            foreach (array_slice($hhid_rows, 0, 10) as $hh) {
                $hhid = $hh['ID'] ?? '';
                $name = trim(implode(' ', array_filter([$hh['FIRSTNAME']??'',$hh['MIDNAME']??'',$hh['LASTNAME']??'',$hh['SUFFIXNAME']??''])));
                [,$zone_name] = ir_parse_zone($hh['AREA'] ?? '');
                $members = [];
                if ($fk_col) foreach ($hhmem_rows as $mem) { if (rtrim($mem[$fk_col]??'','.0')==rtrim($hhid,'.0')) $members[] = trim(implode(' ', array_filter([$mem['FIRSTNAME']??'',$mem['MIDNAME']??'',$mem['LASTNAME']??'',$mem['SUFFIXNAME']??'']))); }
                $ir_preview_data[] = ['name'=>$name,'zone'=>$zone_name?:'-','address'=>trim(implode(', ',array_filter([($hh['BLK']??'')?'Blk '.$hh['BLK']:'',($hh['LOT']??'')?'Lot '.$hh['LOT']:'',$hh['STREET']??'']))),'members'=>$members,'member_count'=>count($members)];
            }
            $_SESSION['import_hhid'] = $hhid_rows; $_SESSION['import_hhmem'] = $hhmem_rows; $_SESSION['import_fk'] = $fk_col;
            $ir_message = 'Preview ready - showing first '.min(10,count($hhid_rows)).' of '.count($hhid_rows).' households.';
            $ir_message_type = 'info';
        }
    }
    if ($_POST['ir_action'] === 'import') {
        $hhid_rows = $_SESSION['import_hhid'] ?? []; $hhmem_rows = $_SESSION['import_hhmem'] ?? []; $fk_col = $_SESSION['import_fk'] ?? null;
        if (empty($hhid_rows)) { $ir_message = 'No data in session. Please preview again.'; $ir_message_type = 'error'; }
        else {
            $fi=0;$fs=0;$ri=0;$rs=0;
            $conn->begin_transaction();
            try {
                $area_cache = [];
                $get_area_id = function($zn) use ($conn,$bid,&$area_cache) {
                    if (empty($zn)) return null;
                    $key = strtoupper(trim($zn));
                    if (isset($area_cache[$key])) return $area_cache[$key];
                    $esc = $conn->real_escape_string($key);
                    $r = $conn->query("SELECT area_id FROM barangay_areas WHERE barangay_id=$bid AND UPPER(area_name)='$esc' AND is_active=1");
                    if ($r && $r->num_rows > 0) { $aid = $r->fetch_assoc()['area_id']; }
                    else {
                        if (preg_match('/^ZONE/i',$key)) $at='Zone'; elseif (preg_match('/PUROK/i',$key)) $at='Purok'; elseif (preg_match('/SITIO/i',$key)) $at='Sitio'; elseif (preg_match('/STREET/i',$key)) $at='Street'; else $at='Area';
                        $mx_r=$conn->query("SELECT MAX(sort_order) as mx FROM barangay_areas WHERE barangay_id=$bid");
                        $mx=($mx_r->fetch_assoc()['mx']??-1)+1;
                        $orig=$conn->real_escape_string(trim($zn));
                        $conn->query("INSERT INTO barangay_areas (barangay_id,area_name,area_type,sort_order) VALUES ($bid,'$orig','$at',$mx)");
                        $aid=$conn->insert_id;
                    }
                    $area_cache[$key]=$aid; return $aid;
                };
                $conn->query("SET FOREIGN_KEY_CHECKS=0");
                $conn->query("DELETE FROM residents WHERE barangay_id=$bid");
                $conn->query("DELETE FROM families WHERE barangay_id=$bid");
                $conn->query("SET FOREIGN_KEY_CHECKS=1");
                foreach ($hhid_rows as $hh) {
                    $hhid=$hh['ID']??'';
                    $full=$conn->real_escape_string(trim(implode(' ',array_filter([$hh['FIRSTNAME']??'',$hh['MIDNAME']??'',$hh['LASTNAME']??'',$hh['SUFFIXNAME']??'']))));
                    if (!$full){$fs++;continue;}
                    [$zone,$znr]=ir_parse_zone($hh['AREA']??'');
                    $aid=$get_area_id($znr); $zne=$conn->real_escape_string($znr);
                    $contact=$conn->real_escape_string($hh['CONTACTNO']??'');
                    $bday=ir_normalize_date($hh['BDAY']??''); $bsql=$bday?"'$bday'":'NULL';
                    $addr=$conn->real_escape_string(implode(', ',array_filter([($hh['BLK']??'')?'Blk '.$hh['BLK']:'',($hh['LOT']??'')?'Lot '.$hh['LOT']:'',$hh['STREET']??''])));
                    $gender=$conn->real_escape_string($hh['GENDER']??'');
                    $conn->query("INSERT INTO families (barangay_id,head_name,zone_number,zone_name,area_id,address,member_count) VALUES ($bid,'$full',$zone,'$zne',".($aid??'NULL').",'$addr',0)");
                    $fid=$conn->insert_id; if(!$fid){$fs++;continue;} $fi++;
                    $conn->query("INSERT INTO residents (barangay_id,family_id,full_name,birth_date,contact_number,zone_number,zone_name,area_id,relationship,gender,is_active) VALUES ($bid,$fid,'$full',$bsql,'$contact',$zone,'$zne',".($aid??'NULL').",'Head','$gender',1)");
                    if($conn->insert_id) $ri++;
                    if($fk_col) foreach($hhmem_rows as $mem) {
                        if(rtrim($mem[$fk_col]??'','.0')!=rtrim($hhid,'.0')) continue;
                        $mf=$conn->real_escape_string(trim(implode(' ',array_filter([$mem['FIRSTNAME']??'',$mem['MIDNAME']??'',$mem['LASTNAME']??'',$mem['SUFFIXNAME']??'']))));
                        if(!$mf){$rs++;continue;}
                        $mb=ir_normalize_date($mem['BDAY']??$mem['BIRTHDATE']??''); $mbsql=$mb?"'$mb'":'NULL';
                        $mc=$conn->real_escape_string($mem['CONTACTNO']??$mem['CONTACT']??'');
                        $mr=$conn->real_escape_string($mem['RELATIONSHIP']??'');
                        $mg=$conn->real_escape_string($mem['GENDER']??'');
                        $conn->query("INSERT INTO residents (barangay_id,family_id,full_name,birth_date,contact_number,zone_number,zone_name,area_id,relationship,gender,is_active) VALUES ($bid,$fid,'$mf',$mbsql,'$mc',$zone,'$zne',".($aid??'NULL').",'$mr','$mg',1)");
                        if($conn->insert_id) $ri++; else $rs++;
                    }
                    $conn->query("UPDATE families SET member_count=(SELECT COUNT(*) FROM residents WHERE family_id=$fid AND is_active=1) WHERE id=$fid");
                }
                $conn->commit();
                unset($_SESSION['import_hhid'],$_SESSION['import_hhmem'],$_SESSION['import_fk']);
                $ir_import_done=true; $ir_import_stats=['families_inserted'=>$fi,'families_skipped'=>$fs,'residents_inserted'=>$ri,'residents_skipped'=>$rs];
                $ir_message='Import complete.'; $ir_message_type='success';
            } catch(Exception $e) { $conn->rollback(); $ir_message='Import failed: '.$e->getMessage(); $ir_message_type='error'; }
        }
    }
}
"""

# Insert before ?> that precedes <!DOCTYPE
target = "?>\n\n\n\n<!DOCTYPE html>"
replacement = php_logic + "?>\n\n\n\n<!DOCTYPE html>"
content = content.replace(target, replacement, 1)
print("PHP logic inserted:", "ir_normalize_date" in content)

with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "w", encoding="utf-8") as f:
    f.write(content)
