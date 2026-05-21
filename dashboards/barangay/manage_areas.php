<?php
require_once '../../config/auth_guard.php';
require_role('barangay');
require_once '../../config/db.php';
$bid = (int)$_SESSION['barangay_id'];
$uid = (int)($_SESSION['user_id'] ?? 0);

// Sidebar variables
$active_alerts=0;$total_residents=0;$total_families=0;$total_zone_leaders=0;$total_officials=0;
$r=$conn->query("SELECT
  (SELECT COUNT(*) FROM alerts WHERE barangay_id=$bid AND resolved=0) as alerts,
  (SELECT COUNT(*) FROM residents WHERE barangay_id=$bid AND is_active=1) as residents,
  (SELECT COUNT(*) FROM families WHERE barangay_id=$bid AND is_active=1) as families,
  (SELECT COUNT(*) FROM zone_leaders WHERE barangay_id=$bid AND is_active=1) as zone_leaders,
  (SELECT COUNT(*) FROM barangay_officials WHERE barangay_id=$bid AND is_active=1) as officials
");
if($r){$_c=$r->fetch_assoc();$active_alerts=$_c['alerts'];$total_residents=$_c['residents'];$total_families=$_c['families'];$total_zone_leaders=$_c['zone_leaders'];$total_officials=$_c['officials'];}

$brgy = $conn->query("SELECT * FROM barangays WHERE barangay_id=$bid")->fetch_assoc();
$area_label = $brgy['area_label'] ?? 'Zone';
$msg = ''; $msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_area'])) {
        $name = trim($_POST['area_name'] ?? '');
        $type = trim($_POST['area_type'] ?? $area_label);
        if ($name) {
            $r = $conn->query("SELECT MAX(sort_order) as mx FROM barangay_areas WHERE barangay_id=$bid");
            $mx = ($r->fetch_assoc()['mx'] ?? -1) + 1;
            $esc_name = $conn->real_escape_string($name);
            $esc_type = $conn->real_escape_string($type);
            $conn->query("INSERT INTO barangay_areas (barangay_id, area_name, area_type, sort_order) VALUES ($bid, '$esc_name', '$esc_type', $mx)");
            $msg = "Area \"$name\" added."; $msg_type = 'success';
        } else { $msg = "Area name cannot be empty."; $msg_type = 'error'; }
    }
    if (isset($_POST['edit_area'])) {
        $aid = (int)$_POST['area_id'];
        $name = trim($_POST['area_name'] ?? '');
        $type = trim($_POST['area_type'] ?? '');
        if ($name && $aid) {
            $en = $conn->real_escape_string($name);
            $et = $conn->real_escape_string($type);
            $conn->query("UPDATE barangay_areas SET area_name='$en', area_type='$et' WHERE area_id=$aid AND barangay_id=$bid");
            $conn->query("UPDATE residents SET zone_name='$en' WHERE area_id=$aid AND barangay_id=$bid");
            $conn->query("UPDATE families SET zone_name='$en' WHERE area_id=$aid AND barangay_id=$bid");
            $msg = "Area updated."; $msg_type = 'success';
        }
    }
    if (isset($_POST['delete_area'])) {
        $aid = (int)$_POST['area_id'];
        $cnt = $conn->query("SELECT COUNT(*) as c FROM residents WHERE area_id=$aid AND barangay_id=$bid")->fetch_assoc()['c'];
        if ($cnt > 0) { $msg = "Cannot delete — $cnt residents assigned. Reassign first."; $msg_type = 'error'; }
        else { $conn->query("DELETE FROM barangay_areas WHERE area_id=$aid AND barangay_id=$bid"); $msg = "Area deleted."; $msg_type = 'success'; }
    }
    if (isset($_POST['update_label'])) {
        $label = $conn->real_escape_string(trim($_POST['area_label'] ?? 'Zone'));
        $conn->query("UPDATE barangays SET area_label='$label' WHERE barangay_id=$bid");
        $area_label = $label;
        $msg = "Label updated to \"$label\"."; $msg_type = 'success';
    }
    if (isset($_POST['reorder'])) {
        $ids = explode(',', $_POST['order'] ?? '');
        foreach ($ids as $i => $aid) { $aid=(int)$aid; if($aid) $conn->query("UPDATE barangay_areas SET sort_order=$i WHERE area_id=$aid AND barangay_id=$bid"); }
        echo json_encode(['ok'=>true]); exit;
    }
}

$areas = [];
$r = $conn->query("SELECT a.*, (SELECT COUNT(*) FROM residents r WHERE r.area_id=a.area_id AND r.is_active=1) as rc, (SELECT COUNT(*) FROM families f WHERE f.area_id=a.area_id AND f.is_active=1) as fc FROM barangay_areas a WHERE a.barangay_id=$bid AND a.is_active=1 ORDER BY a.sort_order, a.area_name");
if ($r) while ($row=$r->fetch_assoc()) $areas[] = $row;
$total_zones = count($areas);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Areas — SPAC</title>
<?php include __DIR__.'/shared_style.css.php'; ?>
<style>
.con{width:100%;padding:24px 28px;box-sizing:border-box;align-self:stretch;min-width:0}.con .card{width:100%;box-sizing:border-box}
.alert{padding:11px 16px;border-radius:8px;margin-bottom:18px;font-size:13px;font-weight:500}
.success{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}
.error{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
.ch{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.ch h2{font-size:14px;font-weight:600;color:var(--navy)}
.cb{padding:18px 20px}
.btn.bp{background:#2563eb;color:#fff;border-color:#2563eb}
.btn.bn{background:var(--navy);color:#fff;border-color:var(--navy)}
.btn.be{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe}
.btn.bd{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
.btn.bsm{padding:5px 10px;font-size:12px}
.area-row{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid var(--border);border-radius:10px;padding:11px 14px;cursor:grab;margin-bottom:7px;transition:box-shadow .15s}
.area-row:hover{box-shadow:0 3px 10px rgba(0,0,0,.08)}
.dh{color:#cbd5e1;cursor:grab;font-size:15px;user-select:none}
.aname{font-weight:600;font-size:13px;flex:1}
.acnt{font-size:12px;color:var(--muted);white-space:nowrap}
.ef{display:none;gap:8px;align-items:center;flex-wrap:wrap;width:100%;margin-top:8px}
.ef.open{display:flex}
.stats{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.sp{background:#fff;border:1px solid var(--border);border-radius:8px;padding:9px 14px;font-size:12px;color:var(--muted)}
.sp strong{color:var(--navy);font-size:15px;display:block}
.badge{font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
.bzone{background:#eff6ff;color:#1d4ed8}.bcmpd{background:#fef3c7;color:#92400e}.barea{background:#f0fdf4;color:#166534}.bother{background:#f1f5f9;color:#475569}
</style>

</head>
<body>
<?php $brgy_info = $brgy; ?>
<?php include 'shared_sidebar.php'; ?>



<div class="main">
<div class="topbar">
<div>
<div class="topbar-title">Manage Areas</div>
<div class="topbar-date" id="topbar-date"></div>
</div>
<div class="topbar-right">
<span class="brgy-chip"><?= htmlspecialchars($brgy['name'] ?? '') ?></span>
<div class="avatar-btn"><?= strtoupper(substr($brgy['name'] ?? 'B', 0, 1)) ?></div>
</div>
</div>
<div class="con">
<?php if($msg):?><div class="alert <?=$msg_type?>"><?=htmlspecialchars($msg)?></div><?php endif;?>

<div class="card">
  <div class="ch">
    <div>
      <h2>Area Label</h2>
      <p style="font-size:12px;color:#64748b;margin-top:2px">What do you call your areas? e.g. Zone, Purok, Street</p>
    </div>
    <span style="font-size:12px;color:#64748b">Current: <strong style="color:#0f172a"><?=htmlspecialchars($area_label)?></strong></span>
  </div>
  <div class="cb">
    <form method="POST">
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <input type="text" name="area_label" value="<?=htmlspecialchars($area_label)?>" placeholder="Zone, Purok, Street..." style="flex:1;max-width:260px;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none">
        <button type="submit" name="update_label" class="btn bp" style="padding:8px 18px">Update Label</button>
      </div>
      <p style="margin-top:8px;font-size:11px;color:#94a3b8">Examples: Zone, Purok, Street, Sitio, Subdivision, Block, Barangka</p>
    </form>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div>
      <h2>Add New Area</h2>
      <p style="font-size:12px;color:#64748b;margin-top:2px">Add a new area to this barangay</p>
    </div>
  </div>
  <div class="cb">
    <form method="POST">
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <input type="text" name="area_name" placeholder="e.g. Zone 1, Mabini St, Purok 3" required style="flex:1;min-width:220px;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none">
        <select name="area_type" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;background:#fff;color:#1e293b;outline:none">
          <?php foreach(['Zone','Compound','Subdivision','Street','Purok','Sitio','Block','Area'] as $t):?>
          <option value="<?=$t?>"><?=$t?></option>
          <?php endforeach;?>
        </select>
        <button type="submit" name="add_area" class="btn bp" style="padding:8px 18px;white-space:nowrap">+ Add Area</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="ch"><h2>All Areas <span style="font-weight:400;color:#64748b;font-size:12px">(<?=count($areas)?>)</span></h2>
    <button class="btn bsm bn" onclick="saveOrder()">Save Order</button>
  </div>
  <div class="cb">
    <div class="stats">
      <div class="sp"><strong><?=count($areas)?></strong>Areas</div>
      <div class="sp"><strong><?=number_format(array_sum(array_column($areas,'rc')))?></strong>Residents</div>
      <div class="sp"><strong><?=number_format(array_sum(array_column($areas,'fc')))?></strong>Families</div>
    </div>
    <?php if(empty($areas)):?><div class="empty">No areas yet. Add one above.</div>
    <?php else:?>
    <p class="hint">⠿ Drag to reorder · click Save Order when done</p>
    <div id="aList">
    <?php foreach($areas as $a):
      $t=$a['area_type'];
      $bc=match(strtolower($t)){'zone'=>'bzone','compound'=>'bcmpd','area','sitio','purok'=>'barea',default=>'bother'};
    ?>
    <div class="area-row" data-id="<?=$a['area_id']?>">
      <span class="dh">⠿</span>
      <span class="badge <?=$bc?>"><?=htmlspecialchars($t)?></span>
      <span class="aname"><?=htmlspecialchars($a['area_name'])?></span>
      <span class="acnt"><?=number_format($a['rc'])?> residents · <?=number_format($a['fc'])?> families</span>
      <button class="btn bsm be" onclick="toggleEdit(<?=$a['area_id']?>)">Edit</button>
      <form method="POST" style="display:inline" onsubmit="return confirm('Delete this area?')">
        <input type="hidden" name="area_id" value="<?=$a['area_id']?>">
        <button type="submit" name="delete_area" class="btn bsm bd">Delete</button>
      </form>
      <form method="POST" class="ef" id="ef<?=$a['area_id']?>">
        <input type="hidden" name="area_id" value="<?=$a['area_id']?>">
        <input type="text" name="area_name" value="<?=htmlspecialchars($a['area_name'])?>" required style="flex:1;min-width:120px">
        <select name="area_type">
          <?php foreach(['Zone','Compound','Subdivision','Street','Purok','Sitio','Block','Area'] as $t2):?>
          <option value="<?=$t2?>" <?=$a['area_type']===$t2?'selected':''?>><?=$t2?></option>
          <?php endforeach;?>
        </select>
        <button type="submit" name="edit_area" class="btn bsm bn">Save</button>
        <button type="button" class="btn bsm" style="background:#f1f5f9" onclick="toggleEdit(<?=$a['area_id']?>)">Cancel</button>
      </form>
    </div>
    <?php endforeach;?>
    </div>
    <?php endif;?>
  </div>
</div>
<script>
function toggleEdit(id){document.getElementById('ef'+id).classList.toggle('open')}
let dragEl=null;
document.querySelectorAll('.area-row').forEach(r=>{
  r.setAttribute('draggable',true);
  r.addEventListener('dragstart',e=>{dragEl=r;r.classList.add('dragging')});
  r.addEventListener('dragend',e=>r.classList.remove('dragging'));
  r.addEventListener('dragover',e=>{
    e.preventDefault();if(!dragEl||dragEl===r)return;
    const list=document.getElementById('aList');
    const rows=[...list.querySelectorAll('.area-row')];
    const i=rows.indexOf(r),di=rows.indexOf(dragEl);
    if(di<i)list.insertBefore(dragEl,r.nextSibling);else list.insertBefore(dragEl,r);
  });
});
function saveOrder(){
  const ids=[...document.querySelectorAll('.area-row')].map(r=>r.dataset.id).join(',');
  fetch('',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'reorder=1&order='+ids})
  .then(r=>r.json()).then(d=>{if(d.ok){const b=document.querySelector('[onclick="saveOrder()"]');b.textContent='Saved ✓';b.style.background='#16a34a';setTimeout(()=>{b.textContent='Save Order';b.style.background=''},2000)}});
}
</script>

</div>
</div>
</div>
</div>

<script>
document.querySelectorAll('.menu-item').forEach(function(el){
    el.classList.remove('active');
    var href = el.getAttribute('href') || '';
    if(href === 'manage_areas.php' || href.endsWith('/manage_areas.php')) el.classList.add('active');
});
</script>
</body>
</html>