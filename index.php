<?php
// ═══════════════════════════════════════════════════════════════════════════
//  SPAC — Single-file entry point
//  GET  → Landing page with login modal
//  POST → Authentication / Forgot-password handler
// ═══════════════════════════════════════════════════════════════════════════
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
ini_set('session.cookie_path', '/');

// ── Session name depends on role ─────────────────────────────────────────────
$role_type = $_POST['role_type'] ?? '';
if ($role_type === 'superadmin')    session_name('SPAC_SUPERADMIN');
elseif ($role_type === 'cityhall')  session_name('SPAC_CITYHALL');
elseif ($role_type === 'barangay')  session_name('SPAC_BARANGAY');
else                                session_name('SPAC_SESSION');

if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'config/db.php';

// ── Barangay list ─────────────────────────────────────────────────────────────
$barangay_list = [];
$brgy_result = $conn->query("SELECT barangay_id, name FROM barangays ORDER BY name ASC");
while ($row = $brgy_result->fetch_assoc()) {
    $barangay_list[] = $row;
}

$error     = '';
$success   = '';
$show_step = 'login'; // used only when POST renders the page (fp errors)

// ════════════════════════════════════════════════════════════════════════════
//  POST HANDLERS
// ════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Forgot password — Step 1: send OTP ───────────────────────────────────
    if (isset($_POST['action']) && $_POST['action'] === 'forgot_send_otp') {
        $email = trim($_POST['fp_email'] ?? '');

        $stmt = $conn->prepare("SELECT user_id, full_name FROM users WHERE email = ? AND is_active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $fp_user = $stmt->get_result()->fetch_assoc();

        if (!$fp_user) {
            $error     = "No active account found with that email address.";
            $show_step = 'fp_email';
        } else {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $uid = $fp_user['user_id'];

            $conn->query("UPDATE password_reset_tokens SET used = 1 WHERE user_id = $uid");
            $ins = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
            $ins->bind_param("is", $uid, $otp);
            $ins->execute();

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $_ENV['MAIL_USERNAME'];
                $mail->Password   = $_ENV['MAIL_PASSWORD'];
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;
                $mail->setFrom($_ENV['MAIL_FROM'], 'SPAC System');
                $mail->addAddress($email, $fp_user['full_name']);
                $mail->isHTML(true);
                $mail->Subject = 'SPAC – Your Password Reset Code';
                $mail->Body = "
                    <div style='font-family:sans-serif;max-width:480px;margin:auto;padding:32px;'>
                      <h2 style='color:#0f172a;margin-bottom:8px;'>Password Reset</h2>
                      <p style='color:#64748b;font-size:14px;margin-bottom:24px;'>
                        Hi {$fp_user['full_name']}, use the code below to reset your password.
                        It expires in <strong>15 minutes</strong>.
                      </p>
                      <div style='background:#f1f4f8;border-radius:8px;padding:24px;text-align:center;
                                  letter-spacing:0.25em;font-size:32px;font-weight:700;color:#0f172a;'>
                        {$otp}
                      </div>
                      <p style='color:#94a3b8;font-size:12px;margin-top:20px;'>
                        If you did not request this, ignore this email.
                      </p>
                    </div>";
                $mail->send();
                $_SESSION['fp_email'] = $email;
                $_SESSION['fp_uid']   = $uid;
                $show_step = 'fp_otp';
                $success   = "A 6-digit code was sent to <strong>{$email}</strong>.";
            } catch (Exception $e) {
                $error     = "Could not send email. Mailer error: {$mail->ErrorInfo}";
                $show_step = 'fp_email';
            }
        }
    }

    // ── Forgot password — Step 2: verify OTP ─────────────────────────────────
    elseif (isset($_POST['action']) && $_POST['action'] === 'forgot_verify_otp') {
        $entered_otp = trim($_POST['fp_otp'] ?? '');
        $uid         = $_SESSION['fp_uid'] ?? 0;

        $stmt = $conn->prepare("
            SELECT id FROM password_reset_tokens
            WHERE user_id = ? AND token = ? AND used = 0 AND expires_at > NOW()
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->bind_param("is", $uid, $entered_otp);
        $stmt->execute();
        $token_row = $stmt->get_result()->fetch_assoc();

        if (!$token_row) {
            $error     = "Invalid or expired code. Please try again.";
            $show_step = 'fp_otp';
        } else {
            $_SESSION['fp_token_id'] = $token_row['id'];
            $show_step = 'fp_newpass';
        }
    }

    // ── Forgot password — Step 3: set new password ────────────────────────────
    elseif (isset($_POST['action']) && $_POST['action'] === 'forgot_set_password') {
        $new_pass = $_POST['fp_newpass']     ?? '';
        $confirm  = $_POST['fp_confirmpass'] ?? '';
        $uid      = $_SESSION['fp_uid']      ?? 0;
        $token_id = $_SESSION['fp_token_id'] ?? 0;

        if (strlen($new_pass) < 8) {
            $error     = "Password must be at least 8 characters.";
            $show_step = 'fp_newpass';
        } elseif ($new_pass !== $confirm) {
            $error     = "Passwords do not match.";
            $show_step = 'fp_newpass';
        } else {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $upd  = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            $upd->bind_param("si", $hash, $uid);
            $upd->execute();
            $conn->query("UPDATE password_reset_tokens SET used = 1 WHERE id = $token_id");
            unset($_SESSION['fp_email'], $_SESSION['fp_uid'], $_SESSION['fp_token_id']);
            $success   = "Password updated successfully. You can now sign in.";
            $show_step = 'fp_success';
        }
    }

    // ── Normal login ──────────────────────────────────────────────────────────
    elseif (!isset($_POST['action'])) {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $brgy_id  = $_POST['barangay_id']   ?? null;

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        $valid = false;
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($role_type === 'superadmin' && $user['role'] === 'superadmin') $valid = true;
            elseif ($role_type === 'cityhall' && $user['role'] === 'cityhall')  $valid = true;
            elseif ($role_type === 'barangay' && $user['role'] === 'barangay' && $user['barangay_id'] == $brgy_id) $valid = true;
        }

        if ($valid) {
            session_unset();
            session_destroy();

            if ($role_type === 'superadmin')      session_name('SPAC_SUPERADMIN');
            elseif ($role_type === 'cityhall')    session_name('SPAC_CITYHALL');
            elseif ($role_type === 'barangay')    session_name('SPAC_BARANGAY');
            else                                  session_name('SPAC_SESSION');

            session_start();
            session_regenerate_id(true);

            $_SESSION['user_id']       = $user['user_id'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['full_name']     = $user['full_name'];
            $_SESSION['barangay_id']   = $user['barangay_id'];
            $_SESSION['last_activity'] = time();

            $ip  = $_SERVER['REMOTE_ADDR'];
            $uid = $user['user_id'];
            $conn->query("UPDATE users SET is_online = 1, last_seen = NOW() WHERE user_id = $uid");
            $conn->query("INSERT INTO audit_logs (user_id, action, ip_address) VALUES ($uid, 'LOGIN', '$ip')");

            if ($user['role'] === 'superadmin')    header("Location: http://localhost/SPAC/dashboards/superadmin/index.php");
            elseif ($user['role'] === 'cityhall')  header("Location: http://localhost/SPAC/dashboards/cityhall/index.php");
            else                                   header("Location: http://localhost/SPAC/dashboards/barangay/index.php");
            exit();
        } else {
            $error     = "Invalid credentials or mismatched role/barangay.";
            $show_step = 'login_error'; // signal to JS to re-open modal on step 3
        }
    }
}
// After all POST logic, fall through to render the landing page
// (only reached if no redirect happened — i.e., an error occurred)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SPAC – Social Protection Assistance Card | San Pedro, Laguna</title>
  


  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --navy:   #0d1b3e;
      --blue:   #1a56db;
      --sky:    #38bdf8;
      --white:  #ffffff;
      --light:  #f0f6ff;
      --muted:  #64748b;
      --beam:   rgba(56,189,248,0.55);
    }

    html { scroll-behavior: smooth; }
    body { font-family: 'Inter', sans-serif; background: var(--white); color: var(--navy); overflow-x: hidden; }

    /* ─── NAVBAR ─── */
    nav {
      position: fixed; top: 0; left: 0; width: 100%; z-index: 100;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 40px; height: 70px;
      background: rgba(13,27,62,0.96);
      backdrop-filter: blur(8px);
      border-bottom: 1px solid rgba(56,189,248,0.15);
    }
    .logo-wrap { display: flex; align-items: center; gap: 14px; text-decoration: none; }

    /* ─── Scanner logo (navbar & modal share this class) ─── */
    .scanner-logo { position: relative; width: 44px; height: 44px; }
    .scanner-logo .bracket { position: absolute; width: 44px; height: 44px; }
    .scanner-logo .bracket::before,
    .scanner-logo .bracket::after {
      content: ''; position: absolute; width: 14px; height: 14px;
      border-color: var(--sky); border-style: solid;
    }
    .scanner-logo .bracket.tl::before { top:0; left:0;   border-width:3px 0 0 3px; }
    .scanner-logo .bracket.tr::after  { top:0; right:0;  border-width:3px 3px 0 0; }
    .scanner-logo .bracket.bl::before { bottom:0; left:0;  border-width:0 0 3px 3px; }
    .scanner-logo .bracket.br::after  { bottom:0; right:0; border-width:0 3px 3px 0; }
    .scanner-logo .qr-dots { position:absolute; inset:10px; display:grid; grid-template-columns:repeat(3,1fr); gap:3px; }
    .scanner-logo .qr-dots span { background:rgba(56,189,248,.35); border-radius:1px; }
    .scanner-logo .qr-dots span:nth-child(1),
    .scanner-logo .qr-dots span:nth-child(3),
    .scanner-logo .qr-dots span:nth-child(7),
    .scanner-logo .qr-dots span:nth-child(9) { background:rgba(56,189,248,.8); }
    .scanner-logo .beam {
      position:absolute; left:4px; right:4px; height:2px;
      background:linear-gradient(90deg,transparent,var(--sky),transparent);
      box-shadow:0 0 8px 3px var(--beam);
      animation:scanBeam 1.8s ease-in-out infinite; top:4px;
    }
    .scanner-logo .blink-dot {
      position:absolute; bottom:-4px; right:-4px; width:7px; height:7px;
      background:#22d3ee; border-radius:50%; animation:blink 1.2s ease-in-out infinite;
    }
    @keyframes scanBeam { 0%{top:4px;opacity:0} 10%{opacity:1} 90%{opacity:1} 100%{top:37px;opacity:0} }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.1} }

    .logo-text { display:flex; flex-direction:column; line-height:1; }
    .logo-text .sp { font-family:'Sora',sans-serif; font-weight:800; font-size:1.35rem; color:var(--white); letter-spacing:.04em; }
    .logo-text .sp span { color:var(--sky); }
    .logo-text .sub { font-size:.62rem; color:rgba(255,255,255,.5); letter-spacing:.1em; text-transform:uppercase; margin-top:3px; }

    .nav-right { display:flex; align-items:center; gap:20px; }
    .nav-link { color:rgba(255,255,255,.7); font-size:.88rem; text-decoration:none; transition:color .2s; }
    .nav-link:hover { color:var(--sky); }
    .btn-nav-login {
      background:var(--blue); color:var(--white); padding:9px 24px; border-radius:8px;
      font-size:.88rem; font-weight:600; border:1.5px solid transparent;
      cursor:pointer; font-family:'Inter',sans-serif;
      transition:background .2s, border-color .2s, transform .15s;
    }
    .btn-nav-login:hover { background:transparent; border-color:var(--sky); color:var(--sky); transform:translateY(-1px); }

    /* ─── HERO ─── */
    .hero {
      min-height: 75vh;
      background: linear-gradient(135deg, #0d1b3e 0%, #0f2b5b 50%, #0e3a6e 100%);
      display:flex; align-items:center; justify-content:center;
      text-align:center; padding:100px 24px 70px;
      position:relative; overflow:hidden;
    }
    .hero::before {
      content:''; position:absolute; inset:0;
      background:
        radial-gradient(ellipse 600px 400px at 20% 80%,rgba(26,86,219,.18),transparent),
        radial-gradient(ellipse 500px 500px at 80% 20%,rgba(56,189,248,.12),transparent);
    }
    .hero-grid {
      position:absolute; inset:0; opacity:.04;
      background-image:
        linear-gradient(var(--sky) 1px,transparent 1px),
        linear-gradient(90deg,var(--sky) 1px,transparent 1px);
      background-size:60px 60px;
    }
    .hero-content { position:relative; max-width:800px; }
    .hero-badge {
      display:inline-flex; align-items:center; gap:8px;
      background:rgba(56,189,248,.12); border:1px solid rgba(56,189,248,.3);
      color:var(--sky); font-size:.78rem; font-weight:600; letter-spacing:.1em; text-transform:uppercase;
      padding:7px 18px; border-radius:50px; margin-bottom:28px;
    }
    .hero-badge span { width:7px; height:7px; background:var(--sky); border-radius:50%; animation:blink 1.2s infinite; }
    .hero h1 { font-family:'Sora',sans-serif; font-weight:800; font-size:clamp(2.2rem,6vw,4.2rem); line-height:1.1; color:var(--white); margin-bottom:12px; }
    .hero h1 em { color:var(--sky); font-style:normal; }
    .accent-line { width:80px; height:4px; background:linear-gradient(90deg,var(--sky),var(--blue)); border-radius:2px; margin:22px auto; }
    .hero p { color:rgba(255,255,255,.72); font-size:clamp(1rem,2.5vw,1.2rem); line-height:1.7; max-width:580px; margin:0 auto 40px; }
    .hero-btns { display:flex; gap:16px; justify-content:center; flex-wrap:wrap; }
    .btn-primary {
      background:var(--blue); color:var(--white); padding:15px 38px; border-radius:10px;
      font-size:1rem; font-weight:700; text-decoration:none; font-family:'Sora',sans-serif;
      box-shadow:0 8px 30px rgba(26,86,219,.4); border:none; cursor:pointer;
      transition:transform .2s, box-shadow .2s;
    }
    .btn-primary:hover { transform:translateY(-3px); box-shadow:0 14px 40px rgba(26,86,219,.55); }
    .btn-ghost {
      background:transparent; color:var(--white); padding:15px 38px; border-radius:10px;
      font-size:1rem; font-weight:600; text-decoration:none; border:1.5px solid rgba(255,255,255,.3);
      font-family:'Sora',sans-serif; transition:border-color .2s, color .2s, transform .2s;
    }
    .btn-ghost:hover { border-color:var(--sky); color:var(--sky); transform:translateY(-3px); }
    .hero-stats { display:flex; justify-content:center; gap:50px; flex-wrap:wrap; margin-top:40px; padding-top:30px; border-top:1px solid rgba(255,255,255,.1); }
    .stat { text-align:center; }
    .stat .num { font-family:'Sora',sans-serif; font-size:2.2rem; font-weight:800; color:var(--sky); }
    .stat .lbl { font-size:.78rem; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.08em; margin-top:4px; }
    .scroll-hint {
      position:absolute; bottom:22px; left:50%; transform:translateX(-50%);
      display:flex; flex-direction:column; align-items:center; gap:6px;
      color:rgba(255,255,255,.4); font-size:.7rem; letter-spacing:.12em;
      text-transform:uppercase; text-decoration:none;
      animation:floatHint 2s ease-in-out infinite;
    }
    .scroll-hint svg { width:22px; height:22px; stroke:rgba(56,189,248,.6); fill:none; stroke-width:2; }
    @keyframes floatHint { 0%,100%{transform:translateX(-50%) translateY(0);opacity:.6} 50%{transform:translateX(-50%) translateY(7px);opacity:1} }

    /* ─── SECTIONS ─── */
    section { padding:100px 24px; }
    .container { max-width:1140px; margin:0 auto; }
    .section-label { font-size:.75rem; font-weight:700; letter-spacing:.15em; text-transform:uppercase; color:var(--blue); margin-bottom:14px; }
    .section-title { font-family:'Sora',sans-serif; font-weight:800; font-size:clamp(1.8rem,4vw,2.8rem); color:var(--navy); line-height:1.2; margin-bottom:20px; }
    .section-title em { color:var(--blue); font-style:normal; }
    .section-desc { color:var(--muted); font-size:1.05rem; line-height:1.75; max-width:540px; }

    /* HOW IT WORKS */
    .how { background:var(--light); padding-top:60px; }
    .how .inner { display:grid; grid-template-columns:1fr 1fr; gap:80px; align-items:center; }
    .steps { display:flex; flex-direction:column; gap:32px; margin-top:40px; }
    .step { display:flex; gap:20px; align-items:flex-start; }
    .step-num { flex-shrink:0; width:46px; height:46px; background:var(--blue); color:var(--white); border-radius:12px; display:flex; align-items:center; justify-content:center; font-family:'Sora',sans-serif; font-weight:800; font-size:1.1rem; }
    .step-text h4 { font-family:'Sora',sans-serif; font-weight:700; font-size:1.05rem; color:var(--navy); margin-bottom:6px; }
    .step-text p { color:var(--muted); font-size:.92rem; line-height:1.6; }
    .scan-visual {
      background:var(--navy); border-radius:24px; padding:48px;
      display:flex; align-items:center; justify-content:center;
      min-height:420px; position:relative; overflow:hidden;
      box-shadow:0 8px 40px rgba(13,27,62,.18);
    }
    .scan-visual::before { content:''; position:absolute; inset:0; background:radial-gradient(circle at 50% 50%,rgba(56,189,248,.1),transparent 70%); }
    .big-scanner { position:relative; width:220px; height:220px; }
    .big-scanner::before { content:''; position:absolute; top:0; left:0; width:50px; height:50px; border-top:4px solid var(--sky); border-left:4px solid var(--sky); border-radius:4px 0 0 0; }
    .big-scanner::after  { content:''; position:absolute; top:0; right:0; width:50px; height:50px; border-top:4px solid var(--sky); border-right:4px solid var(--sky); border-radius:0 4px 0 0; }
    .big-scanner .b::before { content:''; position:absolute; bottom:0; left:0; width:50px; height:50px; border-bottom:4px solid var(--sky); border-left:4px solid var(--sky); border-radius:0 0 0 4px; }
    .big-scanner .b::after  { content:''; position:absolute; bottom:0; right:0; width:50px; height:50px; border-bottom:4px solid var(--sky); border-right:4px solid var(--sky); border-radius:0 0 4px 0; }
    .big-scanner .big-beam { position:absolute; left:0; right:0; height:3px; background:linear-gradient(90deg,transparent,var(--sky),transparent); box-shadow:0 0 16px 6px var(--beam); animation:bigBeam 2s ease-in-out infinite; top:0; }
    @keyframes bigBeam { 0%{top:0;opacity:0} 10%{opacity:1} 90%{opacity:1} 100%{top:220px;opacity:0} }
    .scan-label { position:absolute; bottom:28px; left:50%; transform:translateX(-50%); color:var(--sky); font-size:.75rem; letter-spacing:.15em; text-transform:uppercase; white-space:nowrap; opacity:.75; }

    /* FEATURES */
    .features { background:var(--white); }
    .feat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:28px; margin-top:56px; }
    .feat-card { background:var(--light); border-radius:18px; padding:36px 28px; border:1.5px solid transparent; transition:border-color .25s, box-shadow .25s, transform .25s; }
    .feat-card:hover { border-color:rgba(26,86,219,.25); box-shadow:0 12px 40px rgba(13,27,62,.08); transform:translateY(-6px); }
    .feat-icon { width:52px; height:52px; border-radius:14px; background:linear-gradient(135deg,var(--blue),#1e40af); display:flex; align-items:center; justify-content:center; font-size:1.5rem; margin-bottom:22px; }
    .feat-card h4 { font-family:'Sora',sans-serif; font-weight:700; font-size:1.05rem; color:var(--navy); margin-bottom:10px; }
    .feat-card p { color:var(--muted); font-size:.9rem; line-height:1.65; }

    /* CTA */
    .cta-section { background:linear-gradient(135deg,var(--navy),#0f2b5b); text-align:center; position:relative; overflow:hidden; }
    .cta-section::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse 700px 400px at 50% 100%,rgba(56,189,248,.1),transparent 70%); }
    .cta-section .section-title { color:var(--white); position:relative; }
    .cta-section .section-desc  { color:rgba(255,255,255,.65); margin:0 auto 40px; position:relative; }

    /* FOOTER */
    footer { background:#080f22; color:rgba(255,255,255,.45); text-align:center; padding:36px 24px; font-size:.83rem; line-height:1.8; }
    footer strong { color:rgba(255,255,255,.7); }

    /* ─── GUEST LINK ─── */
    .guest-link-bar { background:#080f22; text-align:center; padding-bottom:8px; }
    .guest-link-bar a { color:rgba(255,255,255,.4); font-size:.78rem; text-decoration:underline; }
    .guest-link-bar a:hover { color:rgba(255,255,255,.7); }

    /* RESPONSIVE */
    @media (max-width:900px) { .how .inner { grid-template-columns:1fr; } .scan-visual { min-height:260px; } .feat-grid { grid-template-columns:1fr 1fr; } }
    @media (max-width:600px) { nav { padding:0 20px; } .feat-grid { grid-template-columns:1fr; } .hero-stats { gap:30px; } }

    /* ══════════════════════════════════════════════════════════════
       LOGIN MODAL
    ══════════════════════════════════════════════════════════════ */
    .modal-overlay {
      position:fixed; inset:0; z-index:999;
      background:rgba(8,15,34,.55);
      display:flex; align-items:center; justify-content:center;
      opacity:0; pointer-events:none; transition:opacity .25s ease;
    }
    .modal-overlay.active { opacity:1; pointer-events:all; }
    .modal {
      background:var(--white); border-radius:20px;
      width:100%; max-width:420px; position:relative;
      box-shadow:0 24px 80px rgba(13,27,62,.35);
      transform:translateY(24px) scale(.97);
      transition:transform .28s ease, opacity .25s ease; opacity:0;
      overflow-y:auto; max-height:90vh;
    }
    .modal-overlay.active .modal { transform:translateY(0) scale(1); opacity:1; }
    .modal-inner { padding:36px 36px 30px; }
    .modal-close { position:absolute; top:14px; right:16px; background:none; border:none; cursor:pointer; color:var(--muted); font-size:1.4rem; line-height:1; transition:color .2s; }
    .modal-close:hover { color:var(--navy); }

    /* modal scanner logo — blue tint on white */
    .modal .scanner-logo .bracket::before,
    .modal .scanner-logo .bracket::after { border-color:var(--blue); }
    .modal .scanner-logo .qr-dots span   { background:rgba(26,86,219,.2); }
    .modal .scanner-logo .qr-dots span:nth-child(1),
    .modal .scanner-logo .qr-dots span:nth-child(3),
    .modal .scanner-logo .qr-dots span:nth-child(7),
    .modal .scanner-logo .qr-dots span:nth-child(9) { background:rgba(26,86,219,.75); }
    .modal .scanner-logo .beam { background:linear-gradient(90deg,transparent,var(--blue),transparent); box-shadow:0 0 8px 3px rgba(26,86,219,.3); }
    .modal .scanner-logo .blink-dot { background:var(--blue); }

    .modal-logo { display:flex; align-items:center; gap:10px; margin-bottom:20px; }
    .modal-logo .logo-text .sp { color:var(--navy) !important; }
    .modal-logo .logo-text .sub { color:var(--muted) !important; }

    /* step dots */
    .m-dots { display:flex; align-items:center; gap:6px; margin-bottom:22px; }
    .m-dot  { width:7px; height:7px; border-radius:50%; background:#dde4f0; transition:.2s; }
    .m-dot.active { background:var(--blue); transform:scale(1.35); }
    .m-dot.done   { background:#94a3b8; }
    .m-line { flex:1; height:1px; background:#dde4f0; }

    /* steps */
    .m-step { display:none; }
    .m-step.active { display:block; }
    .m-title { font-family:'Sora',sans-serif; font-weight:700; font-size:1rem; color:var(--navy); margin-bottom:16px; }

    /* role buttons */
    .m-roles { display:flex; flex-direction:column; gap:10px; }
    .m-role-btn { display:flex; align-items:center; gap:12px; background:var(--light); border:1.5px solid #dde4f0; border-radius:10px; padding:13px 16px; cursor:pointer; text-align:left; width:100%; font-family:'Inter',sans-serif; transition:border-color .15s, background .15s; }
    .m-role-btn:hover { background:#e8f0fe; border-color:var(--blue); }
    .m-role-btn strong { display:block; font-size:.9rem; color:var(--navy); font-weight:600; }
    .m-role-btn small  { display:block; font-size:.78rem; color:var(--muted); margin-top:2px; }
    .m-role-dot { width:6px; height:6px; border-radius:50%; background:#dde4f0; flex-shrink:0; transition:background .15s; }
    .m-role-btn:hover .m-role-dot { background:var(--blue); }

    /* badge */
    .m-badge { display:flex; align-items:center; gap:10px; background:var(--light); border:1.5px solid #dde4f0; border-radius:10px; padding:10px 14px; margin-bottom:16px; }
    .m-badge-pip { width:6px; height:6px; border-radius:50%; background:var(--blue); flex-shrink:0; }
    .m-badge-label { font-size:.72rem; color:var(--muted); }
    .m-badge-name  { font-size:.9rem; font-weight:700; color:var(--navy); font-family:'Sora',sans-serif; }

    /* error / success */
    .m-error   { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; padding:10px 14px; border-radius:8px; font-size:.84rem; margin-bottom:14px; }
    .m-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; padding:10px 14px; border-radius:8px; font-size:.84rem; margin-bottom:14px; }

    /* fields */
    .m-field { margin-bottom:14px; }
    .m-field label { display:block; font-size:.78rem; font-weight:600; color:var(--navy); margin-bottom:6px; }
    .m-field input, .m-field select { width:100%; padding:10px 14px; border:1.5px solid #dde4f0; border-radius:8px; font-size:.9rem; font-family:'Inter',sans-serif; color:var(--navy); background:var(--light); outline:none; transition:border-color .2s, box-shadow .2s; }
    .m-field input:focus, .m-field select:focus { border-color:var(--blue); background:var(--white); box-shadow:0 0 0 3px rgba(26,86,219,.1); }

    /* OTP */
    .otp-row { display:flex; gap:8px; justify-content:center; margin:6px 0 14px; }
    .otp-row input { width:44px; height:52px; text-align:center; font-size:22px; font-weight:700; border-radius:8px; padding:0; font-family:'Sora',sans-serif; border:1.5px solid #dde4f0; outline:none; background:var(--light); color:var(--navy); transition:border-color .2s; }
    .otp-row input:focus { border-color:var(--blue); background:var(--white); }

    /* password strength */
    .pw-strength { height:3px; border-radius:2px; margin-top:6px; background:#dde4f0; overflow:hidden; }
    .pw-strength-bar { height:100%; width:0; border-radius:2px; transition:width .3s, background .3s; }

    /* resend row */
    .resend-row { text-align:center; margin-top:10px; }
    .resend-timer { color:var(--muted); font-size:.78rem; }
    .resend-btn { background:none; border:none; color:var(--muted); font-size:.78rem; cursor:pointer; font-family:'Inter',sans-serif; text-decoration:underline; }
    .resend-btn:hover { color:var(--navy); }

    /* fp link */
    .fp-link-wrap { text-align:center; margin-top:-6px; margin-bottom:12px; }
    .fp-link { background:none; border:none; color:var(--muted); font-size:.78rem; cursor:pointer; text-decoration:underline; font-family:'Inter',sans-serif; }
    .fp-link:hover { color:var(--blue); }

    /* buttons */
    .m-btn-primary { width:100%; padding:12px; background:var(--blue); color:var(--white); border:none; border-radius:8px; font-family:'Sora',sans-serif; font-weight:700; font-size:.95rem; cursor:pointer; margin-top:4px; box-shadow:0 4px 16px rgba(26,86,219,.3); transition:background .2s, transform .15s; }
    .m-btn-primary:hover { background:#1648c0; transform:translateY(-1px); }
    .m-btn-back { width:100%; padding:10px; background:transparent; color:var(--muted); border:1.5px solid #dde4f0; border-radius:8px; font-family:'Inter',sans-serif; font-size:.88rem; cursor:pointer; margin-top:8px; transition:border-color .2s, color .2s; }
    .m-btn-back:hover { border-color:var(--blue); color:var(--blue); }
    .m-note { text-align:center; font-size:.74rem; color:var(--muted); margin-top:14px; }
    .fp-success-icon { font-size:3rem; text-align:center; margin-bottom:12px; }
    .fp-success-title { font-family:'Sora',sans-serif; font-weight:800; font-size:1.2rem; color:#16a34a; text-align:center; margin-bottom:8px; }
    .fp-success-sub { color:var(--muted); font-size:.88rem; text-align:center; margin-bottom:22px; }
  </style>
<link rel="stylesheet" href="/SPAC/assets/fonts/fonts.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></head>
<body>

<!-- ─── NAVBAR ─── -->
<nav>
  <a href="/" class="logo-wrap">
    <div class="scanner-logo">
      <div class="bracket tl"></div><div class="bracket tr"></div>
      <div class="bracket bl"></div><div class="bracket br"></div>
      <div class="qr-dots"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
      <div class="beam"></div><div class="blink-dot"></div>
    </div>
    <div class="logo-text">
      <span class="sp">SP<span>AC</span></span>
      <span class="sub">San Pedro, Laguna</span>
    </div>
  </a>
  <div class="nav-right">
    <a href="#how"      class="nav-link">How It Works</a>
    <a href="#features" class="nav-link">Features</a>
    <button class="btn-nav-login" onclick="openModal()">Log In</button>
  </div>
</nav>

<!-- ─── HERO ─── -->
<section class="hero" id="home">
  <div class="hero-grid"></div>
  <div class="hero-content">
    <div class="hero-badge"><span></span> City of San Pedro — Official Ayuda System</div>
    <h1>Fast. Verified. <em>Ayuda</em> for Every Resident.</h1>
    <div class="accent-line"></div>
    <p>The Social Protection Assistance Card system lets barangay and municipal hall staff scan QR codes to instantly verify and distribute government assistance — no paper, no delay.</p>
    <div class="hero-btns">
      <button class="btn-primary" onclick="openModal()">Log In to SPAC</button>
      <a href="#" onclick="document.getElementById('reportModal').style.display='flex'" class="btn-ghost" style="background:#f0a500;border-color:#f0a500;color:#1a1200;"><i class="fa-regular fa-flag" style="margin-right:6px;"></i>Report Missing Assistance</a>
    </div>
    <div class="hero-stats">
      <div class="stat"><div class="num">27</div><div class="lbl">Barangays</div></div>
      <div class="stat"><div class="num">QR</div><div class="lbl">Instant Scan</div></div>
      <div class="stat"><div class="num">0</div><div class="lbl">Paper Forms</div></div>
      <div class="stat"><div class="num">24/7</div><div class="lbl">System Access</div></div>
    </div>
  </div>
  <a href="#how" class="scroll-hint">
    <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
  </a>
</section>

<!-- ─── HOW IT WORKS ─── -->
<section class="how" id="how">
  <div class="container">
    <div class="inner">
      <div>
        <div class="section-label">Process</div>
        <h2 class="section-title">How <em>Ayuda</em> Distribution Works</h2>
        <p class="section-desc">A streamlined end-to-end process — from registration to distribution — powered by QR technology.</p>
        <div class="steps">
          <div class="step"><div class="step-num">1</div><div class="step-text"><h4>Beneficiary Registration</h4><p>Residents are registered and issued a unique SPAC QR code linked to their profile and eligibility.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-text"><h4>QR Code Scanning</h4><p>During distribution, staff scan the beneficiary's QR code using any device with the SPAC system.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-text"><h4>Instant Verification</h4><p>The system checks eligibility in real time — preventing duplicate claims and ensuring only qualified residents receive aid.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-text"><h4>Assistance Recorded</h4><p>Every transaction is logged automatically, giving barangay and city hall full transparency and audit trails.</p></div></div>
        </div>
      </div>
      <div class="scan-visual">
        <div class="big-scanner">
          <div class="b"></div>
          <div class="big-beam"></div>
        </div>
        <div class="scan-label">Scanning QR Code…</div>
      </div>
    </div>
  </div>
</section>

<!-- ─── FEATURES ─── -->
<section class="features" id="features">
  <div class="container">
    <div class="section-label">Features</div>
    <h2 class="section-title">Everything You Need for <em>Smarter</em> Aid Distribution</h2>
    <div class="feat-grid">
      <div class="feat-card"><div class="feat-icon">📲</div><h4>QR-Based Scanning</h4><p>Each beneficiary has a unique QR code. Scanning it instantly pulls up their profile and distribution status.</p></div>
      <div class="feat-card"><div class="feat-icon">🛡️</div><h4>Duplicate Prevention</h4><p>The system automatically blocks double-claiming, ensuring aid goes only to verified, eligible residents.</p></div>
      <div class="feat-card"><div class="feat-icon">🏛️</div><h4>Multi-Barangay Support</h4><p>Manage all 27 barangays of San Pedro from one platform. Each office has role-based access.</p></div>
      <div class="feat-card"><div class="feat-icon">📊</div><h4>Real-Time Reports</h4><p>Live dashboards show how many beneficiaries have claimed, by barangay, by program, by date.</p></div>
      <div class="feat-card"><div class="feat-icon">🔐</div><h4>Secure Role Access</h4><p>Admins, encoders, and barangay staff each have defined permissions to keep data safe.</p></div>
      <div class="feat-card"><div class="feat-icon">📋</div><h4>Full Audit Trail</h4><p>Every scan, claim, and update is logged with timestamps — full accountability for every transaction.</p></div>
    </div>
  </div>
</section>

<!-- ─── CTA ─── -->
<section class="cta-section">
  <div class="container">
    <h2 class="section-title">Ready to Distribute <em>Smarter?</em></h2>
    <p class="section-desc">Log in to the SPAC system and start scanning. Fast, verified, and paperless ayuda distribution for San Pedro, Laguna.</p>
    <button class="btn-primary" onclick="openModal()">Log In to SPAC →</button>
  </div>
</section>

<!-- ─── FOOTER ─── -->
<div class="guest-link-bar">
  <a href="#" onclick="document.getElementById('reportModal').style.display='flex'">Report unreceived assistance as guest</a>
</div>
<footer>
  <strong>SPAC — Social Protection Assistance Card System</strong><br>
  City Government of San Pedro, Laguna &nbsp;|&nbsp; Municipal Hall &amp; Barangay Use Only<br>
  &copy; <?= date('Y') ?> All rights reserved. Unauthorized access is prohibited.
</footer>

<!-- ══════════════════════════════════════════════════════════════
     LOGIN MODAL (5 inner steps: role → barangay? → credentials
                                  + forgot: email → otp → newpass → success)
══════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="loginModal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()" title="Close">&times;</button>
    <div class="modal-inner">

      <!-- Logo -->
      <div class="modal-logo">
        <div class="scanner-logo">
          <div class="bracket tl"></div><div class="bracket tr"></div>
          <div class="bracket bl"></div><div class="bracket br"></div>
          <div class="qr-dots"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
          <div class="beam"></div><div class="blink-dot"></div>
        </div>
        <div class="logo-text">
          <span class="sp">SP<span>AC</span></span>
          <span class="sub">San Pedro, Laguna</span>
        </div>
      </div>

      <!-- LOGIN step dots -->
      <div class="m-dots" id="dots-login">
        <div class="m-dot active" id="ld1"></div>
        <div class="m-line"></div>
        <div class="m-dot" id="ld2"></div>
        <div class="m-line"></div>
        <div class="m-dot" id="ld3"></div>
      </div>

      <!-- FORGOT step dots -->
      <div class="m-dots" id="dots-forgot" style="display:none;">
        <div class="m-dot active" id="fd1"></div>
        <div class="m-line"></div>
        <div class="m-dot" id="fd2"></div>
        <div class="m-line"></div>
        <div class="m-dot" id="fd3"></div>
      </div>

      <!-- ── LOGIN STEPS ── -->

      <!-- L1: Role selection -->
      <div class="m-step active" id="ls1" data-g="login">
        <p class="m-title">Who are you signing in as?</p>
        <div class="m-roles">
          <button type="button" class="m-role-btn" onclick="lSelectRole('barangay','Barangay Official')">
            <span class="m-role-dot"></span>
            <div><strong>Barangay Official</strong><small>Access your barangay dashboard</small></div>
          </button>
          <button type="button" class="m-role-btn" onclick="lSelectRole('cityhall','City Hall')">
            <span class="m-role-dot"></span>
            <div><strong>City Hall</strong><small>City-level management</small></div>
          </button>
          <button type="button" class="m-role-btn" onclick="lSelectRole('superadmin','Super Admin')">
            <span class="m-role-dot"></span>
            <div><strong>Super Admin</strong><small>Full system access</small></div>
          </button>
        </div>
      </div>

      <!-- L2: Barangay selection -->
      <div class="m-step" id="ls2" data-g="login">
        <p class="m-title">Select Your Barangay</p>
        <div class="m-field">
          <label>Barangay Name</label>
          <select id="m-brgy-select" onchange="lSetBrgy()">
            <option value="">— Select your barangay —</option>
            <?php foreach ($barangay_list as $b): ?>
              <option value="<?= $b['barangay_id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="button" class="m-btn-primary" onclick="lGoToCredentials()">Continue →</button>
        <button type="button" class="m-btn-back"    onclick="lGoToStep(1)">← Back</button>
      </div>

      <!-- L3: Credentials -->
      <div class="m-step" id="ls3" data-g="login">
        <div class="m-badge">
          <span class="m-badge-pip"></span>
          <div><div class="m-badge-label">Signing in as</div><div class="m-badge-name" id="m-badge-name">—</div></div>
        </div>
        <?php if ($error && $show_step === 'login_error'): ?>
          <div class="m-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['timeout'])): ?>
          <div class="m-error">Session expired. Please log in again.</div>
        <?php endif; ?>
        <div id="m-login-error" class="m-error" style="display:none;"></div>
        <form method="POST" action="index.php" id="m-login-form">
          <input type="hidden" name="role_type"   id="m-input-role">
          <input type="hidden" name="barangay_id" id="m-input-brgy">
          <div class="m-field">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required autocomplete="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
          </div>
          <div class="m-field">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required autocomplete="current-password" />
          </div>
          <div class="fp-link-wrap">
            <button type="button" class="fp-link" onclick="startForgot()">Forgot password?</button>
          </div>
          <button type="submit" class="m-btn-primary">Sign In →</button>
        </form>
        <button type="button" class="m-btn-back" onclick="lGoToStep(1)">← Back</button>
        <p class="m-note">For authorized municipal &amp; barangay staff only.</p>
      </div>

      <!-- ── FORGOT PASSWORD STEPS ── -->

      <!-- F1: Enter email -->
      <div class="m-step" id="fs1" data-g="forgot">
        <p class="m-title">Reset Your Password</p>
        <?php if ($error && $show_step === 'fp_email'): ?>
          <div class="m-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="index.php">
          <input type="hidden" name="action" value="forgot_send_otp">
          <div class="m-field">
            <label>Your Account Email</label>
            <input type="email" name="fp_email" placeholder="Enter your email" required
                   value="<?= htmlspecialchars($_POST['fp_email'] ?? '') ?>" />
          </div>
          <button type="submit" class="m-btn-primary">Send Reset Code →</button>
        </form>
        <button type="button" class="m-btn-back" onclick="cancelForgot()">← Back to Sign In</button>
      </div>

      <!-- F2: Enter OTP -->
      <div class="m-step" id="fs2" data-g="forgot">
        <p class="m-title">Enter Verification Code</p>
        <p style="font-size:.82rem;color:var(--muted);margin-bottom:14px;">Check your email for the 6-digit code.</p>
        <?php if ($error && $show_step === 'fp_otp'): ?>
          <div class="m-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success && $show_step === 'fp_otp'): ?>
          <div class="m-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST" action="index.php" id="otp-form">
          <input type="hidden" name="action" value="forgot_verify_otp">
          <input type="hidden" name="fp_otp" id="otp-combined">
          <div class="otp-row" id="otp-inputs">
            <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]">
            <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]">
            <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]">
            <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]">
            <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]">
            <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]">
          </div>
          <button type="submit" class="m-btn-primary" onclick="combineOtp()">Verify Code →</button>
        </form>
        <div class="resend-row">
          <span class="resend-timer" id="resend-timer"></span>
          <form method="POST" action="index.php" style="display:inline;" id="resend-form">
            <input type="hidden" name="action"   value="forgot_send_otp">
            <input type="hidden" name="fp_email" value="<?= htmlspecialchars($_SESSION['fp_email'] ?? '') ?>">
            <button type="submit" class="resend-btn" id="resend-btn" style="display:none;">Resend code</button>
          </form>
        </div>
        <button type="button" class="m-btn-back" onclick="cancelForgot()">← Back to Sign In</button>
      </div>

      <!-- F3: New password -->
      <div class="m-step" id="fs3" data-g="forgot">
        <p class="m-title">Set New Password</p>
        <?php if ($error && $show_step === 'fp_newpass'): ?>
          <div class="m-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="index.php">
          <input type="hidden" name="action" value="forgot_set_password">
          <div class="m-field">
            <label>New Password</label>
            <input type="password" name="fp_newpass" placeholder="Min. 8 characters" required oninput="checkStrength(this.value)">
            <div class="pw-strength"><div class="pw-strength-bar" id="pw-bar"></div></div>
          </div>
          <div class="m-field">
            <label>Confirm Password</label>
            <input type="password" name="fp_confirmpass" placeholder="Re-enter new password" required>
          </div>
          <button type="submit" class="m-btn-primary">Update Password →</button>
        </form>
        <button type="button" class="m-btn-back" onclick="cancelForgot()">← Cancel</button>
      </div>

      <!-- F-success -->
      <div class="m-step" id="fs-ok" data-g="forgot">
        <div class="fp-success-icon">✅</div>
        <div class="fp-success-title">Password Updated!</div>
        <div class="fp-success-sub">Your password has been changed. You can now sign in.</div>
        <button type="button" class="m-btn-primary" onclick="cancelForgot()">Back to Sign In</button>
      </div>

    </div><!-- /.modal-inner -->
  </div>
</div><!-- /.modal-overlay -->

<script>
// ══════════════════════════════════════════════════════════════
//  MODAL OPEN / CLOSE
// ══════════════════════════════════════════════════════════════
function openModal() {
  var sw = window.innerWidth - document.documentElement.clientWidth;
  document.body.style.paddingRight = sw + 'px';
  document.body.style.overflow = 'hidden';
  document.getElementById('loginModal').classList.add('active');
}
function closeModal() {
  document.getElementById('loginModal').classList.remove('active');
  document.body.style.overflow = '';
  document.body.style.paddingRight = '';
}
document.getElementById('loginModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

// ══════════════════════════════════════════════════════════════
//  LOGIN FLOW
// ══════════════════════════════════════════════════════════════
var lRole = '', lBrgyId = '', lBrgyName = '', lRoleName = '';

function lGoToStep(n) {
  document.querySelectorAll('[data-g="login"]').forEach(function(s){ s.classList.remove('active'); });
  document.getElementById('ls' + n).classList.add('active');
  document.getElementById('dots-login').style.display  = 'flex';
  document.getElementById('dots-forgot').style.display = 'none';
  for (var i = 1; i <= 3; i++) {
    var d = document.getElementById('ld' + i);
    d.classList.remove('active','done');
    if (i < n)        d.classList.add('done');
    else if (i === n) d.classList.add('active');
  }
}

function lSelectRole(role, name) {
  lRole = role; lRoleName = name;
  if (role === 'barangay') { lGoToStep(2); }
  else { lBrgyId = ''; lBrgyName = ''; lUpdateBadge(name); lGoToStep(3); }
}

function lSetBrgy() {
  var sel = document.getElementById('m-brgy-select');
  lBrgyId   = sel.value;
  lBrgyName = sel.options[sel.selectedIndex].text;
}

function lGoToCredentials() {
  if (!lBrgyId) { alert('Please select your barangay first.'); return; }
  lUpdateBadge('Barangay — ' + lBrgyName);
  lGoToStep(3);
}

function lUpdateBadge(name) {
  document.getElementById('m-badge-name').textContent = name;
  document.getElementById('m-input-role').value       = lRole;
  document.getElementById('m-input-brgy').value       = lBrgyId;
}

// ══════════════════════════════════════════════════════════════
//  FORGOT PASSWORD FLOW
// ══════════════════════════════════════════════════════════════
function startForgot() {
  document.querySelectorAll('[data-g="login"],[data-g="forgot"]').forEach(function(s){ s.classList.remove('active'); });
  document.getElementById('fs1').classList.add('active');
  document.getElementById('dots-login').style.display  = 'none';
  document.getElementById('dots-forgot').style.display = 'flex';
  updateFDots(1);
}

function cancelForgot() {
  document.querySelectorAll('[data-g="forgot"]').forEach(function(s){ s.classList.remove('active'); });
  lGoToStep(1);
}

function updateFDots(n) {
  for (var i = 1; i <= 3; i++) {
    var d = document.getElementById('fd' + i);
    d.classList.remove('active','done');
    if (i < n)        d.classList.add('done');
    else if (i === n) d.classList.add('active');
  }
}

// ══════════════════════════════════════════════════════════════
//  OTP INPUTS
// ══════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function () {
  var boxes = document.querySelectorAll('.otp-box');
  boxes.forEach(function(box, idx) {
    box.addEventListener('input', function() {
      this.value = this.value.replace(/\D/g,'');
      if (this.value && idx < boxes.length - 1) boxes[idx + 1].focus();
    });
    box.addEventListener('keydown', function(e) {
      if (e.key === 'Backspace' && !this.value && idx > 0) boxes[idx - 1].focus();
    });
    box.addEventListener('paste', function(e) {
      e.preventDefault();
      var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'');
      pasted.split('').forEach(function(ch, i) { if (boxes[idx + i]) boxes[idx + i].value = ch; });
      var next = idx + pasted.length;
      if (boxes[next]) boxes[next].focus();
    });
  });

  <?php if ($show_step === 'fp_otp'): ?>
  startResendTimer(60);
  <?php endif; ?>
});

function combineOtp() {
  var val = '';
  document.querySelectorAll('.otp-box').forEach(function(b){ val += b.value; });
  document.getElementById('otp-combined').value = val;
}

function startResendTimer(s) {
  var timer = document.getElementById('resend-timer');
  var btn   = document.getElementById('resend-btn');
  btn.style.display   = 'none';
  timer.style.display = 'inline';
  var iv = setInterval(function() {
    timer.textContent = 'Resend in ' + s + 's';
    s--;
    if (s < 0) { clearInterval(iv); timer.style.display = 'none'; btn.style.display = 'inline'; }
  }, 1000);
}

// ══════════════════════════════════════════════════════════════
//  PASSWORD STRENGTH
// ══════════════════════════════════════════════════════════════
function checkStrength(val) {
  var bar = document.getElementById('pw-bar'), score = 0;
  if (val.length >= 8)           score++;
  if (/[A-Z]/.test(val))         score++;
  if (/[0-9]/.test(val))         score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  bar.style.width      = ['0%','25%','50%','75%','100%'][score];
  bar.style.background = ['#e2e8f0','#ef4444','#f97316','#eab308','#22c55e'][score];
}

// ══════════════════════════════════════════════════════════════
//  RESTORE STATE AFTER PHP POST ERRORS
// ══════════════════════════════════════════════════════════════
<?php if ($show_step === 'login_error'): ?>
  // Login failed — re-open modal at step 3 with correct role/brgy restored
  lRole     = '<?= htmlspecialchars($_POST['role_type']   ?? '') ?>';
  lBrgyId   = '<?= htmlspecialchars($_POST['barangay_id'] ?? '') ?>';
  lRoleName = lRole === 'superadmin' ? 'Super Admin' : (lRole === 'cityhall' ? 'City Hall' : 'Barangay Official');
  lUpdateBadge(lRole === 'barangay' ? 'Barangay Official' : lRoleName);
  lGoToStep(3);
  openModal();
<?php elseif ($show_step === 'fp_email'): ?>
  startForgot(); openModal(); updateFDots(1);
<?php elseif ($show_step === 'fp_otp'): ?>
  document.querySelectorAll('[data-g]').forEach(function(s){ s.classList.remove('active'); });
  document.getElementById('fs2').classList.add('active');
  document.getElementById('dots-login').style.display  = 'none';
  document.getElementById('dots-forgot').style.display = 'flex';
  updateFDots(2); openModal();
<?php elseif ($show_step === 'fp_newpass'): ?>
  document.querySelectorAll('[data-g]').forEach(function(s){ s.classList.remove('active'); });
  document.getElementById('fs3').classList.add('active');
  document.getElementById('dots-login').style.display  = 'none';
  document.getElementById('dots-forgot').style.display = 'flex';
  updateFDots(3); openModal();
<?php elseif ($show_step === 'fp_success'): ?>
  document.querySelectorAll('[data-g]').forEach(function(s){ s.classList.remove('active'); });
  document.getElementById('fs-ok').classList.add('active');
  document.getElementById('dots-login').style.display  = 'none';
  document.getElementById('dots-forgot').style.display = 'flex';
  updateFDots(3); openModal();
<?php endif; ?>
</script>


<div id="reportModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;overflow-y:auto;">
  <div style="background:#fff;border-radius:12px;padding:2rem;max-width:520px;width:90%;margin:auto;position:relative;">
    <button onclick="document.getElementById('reportModal').style.display='none'" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:22px;cursor:pointer;color:#333;">&times;</button>
    <h2 style="margin:0 0 4px;font-size:18px;color:#0f172a;">Report Missing Assistance</h2>
    <p style="margin:0 0 16px;font-size:13px;color:#64748b;">No login required. Our team will follow up with your barangay.</p>
    <form method="POST" action="guest_report.php">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div style="grid-column:1/-1">
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Full Name <span style="color:red">*</span></label>
          <input type="text" name="full_name" placeholder="Enter your full name" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Contact Number <span style="color:red">*</span></label>
          <input type="text" name="contact_number" placeholder="09xx xxx xxxx" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Zone Number <span style="color:red">*</span></label>
          <input type="number" name="zone_number" placeholder="e.g. 1" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
        </div>
        <div style="grid-column:1/-1">
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Address <span style="color:red">*</span></label>
          <input type="text" name="address" placeholder="House no., Street, Purok" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Barangay <span style="color:red">*</span></label>
          <select name="barangay_id" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
            <option value="">— Select barangay —</option>
            <?php foreach($barangay_list as $b): ?>
            <option value="<?= $b['barangay_id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Month of Assistance <span style="color:red">*</span></label>
          <select name="assistance_month" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
            <option value="">— Select month —</option>
            <option value="2026-01">January 2026</option>
            <option value="2026-02">February 2026</option>
            <option value="2026-03">March 2026</option>
            <option value="2026-04">April 2026</option>
            <option value="2026-05">May 2026</option>
            <option value="2026-06">June 2026</option>
            <option value="2026-07">July 2026</option>
            <option value="2026-08">August 2026</option>
            <option value="2026-09">September 2026</option>
            <option value="2026-10">October 2026</option>
            <option value="2026-11">November 2026</option>
            <option value="2026-12">December 2026</option>
          </select>
        </div>
        <div style="grid-column:1/-1">
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Type of Assistance <span style="color:red">*</span></label>
          <select name="assistance_type" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
            <option value="">— Select type —</option>
            <option value="Food Assistance">Food Assistance</option>
            <option value="Medical / Health">Medical / Health</option>
            <option value="Financial Aid">Financial Aid</option>
            <option value="Livelihood">Livelihood</option>
            <option value="Educational">Educational</option>
            <option value="Disaster Relief">Disaster Relief</option>
            <option value="Senior Citizen">Senior Citizen Benefit</option>
            <option value="PWD">PWD Assistance</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div style="grid-column:1/-1">
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Description</label>
          <textarea name="description" placeholder="Describe what happened..." rows="3" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;resize:vertical;"></textarea>
        </div>
      </div>
      <button type="submit" style="width:100%;padding:11px;background:#f0a500;border:none;border-radius:6px;font-size:14px;font-weight:600;color:#1a1200;cursor:pointer;margin-top:12px;">Submit Report</button>
    </form>
  </div>
</div>

</body>
</html>
