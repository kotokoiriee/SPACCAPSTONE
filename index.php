<?php
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

$role_type = $_POST['role_type'] ?? '';
if ($role_type === 'superadmin') {
    session_name('SPAC_SUPERADMIN');
} elseif ($role_type === 'cityhall') {
    session_name('SPAC_CITYHALL');
} elseif ($role_type === 'barangay') {
    session_name('SPAC_BARANGAY');
} else {
    session_name('SPAC_SESSION');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';

$error   = '';
$success = '';

// ── Fetch barangay list ──────────────────────────────────────────────────────
$barangays    = $conn->query("SELECT barangay_id, name FROM barangays ORDER BY name ASC");
$barangay_list = [];
while ($row = $barangays->fetch_assoc()) {
    $barangay_list[] = $row;
}

// ════════════════════════════════════════════════════════════════════════════
//  FORGOT PASSWORD — Step 1: Email submission
// ════════════════════════════════════════════════════════════════════════════
if (isset($_POST['action']) && $_POST['action'] === 'forgot_send_otp') {
    $email = trim($_POST['fp_email'] ?? '');

    $stmt = $conn->prepare("SELECT user_id, full_name FROM users WHERE email = ? AND is_active = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $fp_user = $stmt->get_result()->fetch_assoc();

    if (!$fp_user) {
        $error      = "No active account found with that email address.";
        $show_step  = 'fp_email';
    } else {
        // Generate 6-digit OTP, valid for 15 minutes
        $otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $uid     = $fp_user['user_id'];

    // Invalidate old tokens then insert new one
        $conn->query("UPDATE password_reset_tokens SET used = 1 WHERE user_id = $uid");
        $ins = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
        $ins->bind_param("is", $uid, $otp);
        $ins->execute();


        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';   // ← change to your SMTP host
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom($_ENV['MAIL_FROM'], 'SPAC System');
            $mail->addAddress($email, $fp_user['full_name']);
            $mail->isHTML(true);
            $mail->Subject = 'SPAC – Your Password Reset Code';
            $mail->Body    = "
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

// ════════════════════════════════════════════════════════════════════════════
//  FORGOT PASSWORD — Step 2: Verify OTP
// ════════════════════════════════════════════════════════════════════════════
if (isset($_POST['action']) && $_POST['action'] === 'forgot_verify_otp') {
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

// ════════════════════════════════════════════════════════════════════════════
//  FORGOT PASSWORD — Step 3: Set new password
// ════════════════════════════════════════════════════════════════════════════
if (isset($_POST['action']) && $_POST['action'] === 'forgot_set_password') {
    $new_pass    = $_POST['fp_newpass']     ?? '';
    $confirm     = $_POST['fp_confirmpass'] ?? '';
    $uid         = $_SESSION['fp_uid']      ?? 0;
    $token_id    = $_SESSION['fp_token_id'] ?? 0;

    if (strlen($new_pass) < 8) {
        $error     = "Password must be at least 8 characters.";
        $show_step = 'fp_newpass';
    } elseif ($new_pass !== $confirm) {
        $error     = "Passwords do not match.";
        $show_step = 'fp_newpass';
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?")->bind_param("si", $hash, $uid) && $conn->query("UPDATE users SET password_hash = '$hash' WHERE user_id = $uid");

        // Cleaner update
        $upd = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $upd->bind_param("si", $hash, $uid);
        $upd->execute();

        // Mark token used
        $conn->query("UPDATE password_reset_tokens SET used = 1 WHERE id = $token_id");

        // Clear session fp data
        unset($_SESSION['fp_email'], $_SESSION['fp_uid'], $_SESSION['fp_token_id']);

        $success   = "Password updated successfully. You can now sign in.";
        $show_step = 'fp_success';
    }
}

// ════════════════════════════════════════════════════════════════════════════
//  NORMAL LOGIN
// ════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
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

        if ($role_type === 'superadmin')       session_name('SPAC_SUPERADMIN');
        elseif ($role_type === 'cityhall')     session_name('SPAC_CITYHALL');
        elseif ($role_type === 'barangay')     session_name('SPAC_BARANGAY');
        else                                   session_name('SPAC_SESSION');

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
        $show_step = 'login';
    }
}

// Determine which forgot-password step to restore after a POST error/success
$show_step = $show_step ?? 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <title>SPAC – Login</title>
    <style>
        :root {
            --white:      #ffffff;
            --surface-2:  #f1f4f8;
            --border:     #e2e8f0;
            --border-2:   #cbd5e1;
            --muted:      #64748b;
            --text:       #1e293b;
            --navy:       #0f172a;
            --navy-mid:   #1e3a5f;
            --navy-light: #eef2f7;
            --red:        #dc2626;
            --red-l:      #fef2f2;
            --green:      #16a34a;
            --green-l:    #f0fdf4;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--white);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-wrapper { width: 100%; max-width: 420px; padding: 24px; }
        .logo-area { text-align: center; margin-bottom: 28px; }
        .logo-area h1 {
            color: var(--navy);
            font-size: 36px;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-family: 'DM Mono', monospace;
        }
        .logo-area p { color: var(--muted); font-size: 12px; margin-top: 6px; font-weight: 400; letter-spacing: 0.02em; }
        .card {
            background: var(--white);
            border-radius: 10px;
            padding: 28px;
            border: 1px solid var(--border);
        }
        .steps { display: flex; justify-content: center; gap: 8px; margin-bottom: 24px; align-items: center; }
        .step-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--border); transition: 0.3s; }
        .step-dot.active { background: var(--navy); transform: scale(1.4); }
        .step-dot.done   { background: var(--border-2); }
        .step-line { width: 24px; height: 1px; background: var(--border); }

        /* Login steps dots */
        .dots-login  { display: flex; }
        /* Forgot password steps dots */
        .dots-forgot { display: none; }

        .card h2 { color: var(--navy); font-size: 15px; font-weight: 500; margin-bottom: 18px; }
        .card h2 small { display: block; color: var(--muted); font-size: 12px; font-weight: 400; margin-top: 4px; }

        .role-grid { display: flex; flex-direction: column; gap: 8px; }
        .role-btn {
            display: flex; align-items: center; gap: 12px;
            background: var(--white); border: 1px solid var(--border);
            border-radius: 8px; padding: 13px 16px; cursor: pointer;
            transition: all 0.15s; text-align: left; width: 100%;
            font-family: 'DM Sans', sans-serif;
        }
        .role-btn:hover { background: var(--navy-light); border-color: var(--navy); }
        .role-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--border); flex-shrink: 0; transition: background 0.15s; }
        .role-btn:hover .role-dot { background: var(--navy); }
        .role-text h3 { color: var(--navy); font-size: 13px; font-weight: 500; }
        .role-text p  { color: var(--muted); font-size: 12px; margin-top: 2px; }

        .form-step { display: none; }
        .form-step.active { display: block; }
        .form-group { margin-bottom: 14px; }
        label { display: block; color: var(--text); font-size: 12px; font-weight: 500; margin-bottom: 5px; }
        input, select {
            width: 100%; padding: 9px 12px;
            background: var(--white); border: 1px solid var(--border);
            border-radius: 6px; color: var(--text); font-size: 13px; font-weight: 400;
            outline: none; transition: border-color 0.15s;
            font-family: 'DM Sans', sans-serif;
        }
        input:focus, select:focus { border-color: var(--navy); }
        input::placeholder { color: var(--muted); }

        .btn-primary {
            width: 100%; padding: 10px; background: var(--navy); color: #fff;
            border: none; border-radius: 6px; font-size: 13px; font-weight: 500;
            cursor: pointer; margin-top: 4px; transition: background 0.15s;
            letter-spacing: 0.02em; font-family: 'DM Sans', sans-serif;
        }
        .btn-primary:hover { background: var(--navy-mid); }
        /* legacy aliases */
        .btn-next, .btn-login { composes: btn-primary; }

        .btn-back {
            width: 100%; padding: 9px; background: transparent; color: var(--muted);
            border: 1px solid var(--border); border-radius: 6px; font-size: 13px;
            font-weight: 400; cursor: pointer; margin-top: 8px; transition: all 0.15s;
            font-family: 'DM Sans', sans-serif;
        }
        .btn-back:hover { border-color: var(--navy); color: var(--navy); background: var(--navy-light); }

        .selected-badge {
            background: var(--surface-2); border: 1px solid var(--border);
            border-radius: 8px; padding: 10px 14px; margin-bottom: 16px;
            display: flex; align-items: center; gap: 10px;
        }
        .badge-pip { width: 5px; height: 5px; border-radius: 50%; background: var(--navy); flex-shrink: 0; }
        .badge-text { color: var(--muted); font-size: 11px; font-weight: 400; }
        .badge-name { color: var(--navy); font-size: 13px; font-weight: 500; }

        .error {
            background: var(--red-l); border: 1px solid #fecaca; color: var(--red);
            padding: 10px 14px; border-radius: 6px; font-size: 13px;
            margin-bottom: 14px; text-align: center; font-weight: 400;
        }
        .success-msg {
            background: var(--green-l); border: 1px solid #bbf7d0; color: var(--green);
            padding: 10px 14px; border-radius: 6px; font-size: 13px;
            margin-bottom: 14px; text-align: center; font-weight: 400;
        }

        /* OTP input row */
        .otp-row { display: flex; gap: 8px; justify-content: center; margin: 6px 0 14px; }
        .otp-row input {
            width: 44px; height: 52px; text-align: center; font-size: 22px;
            font-weight: 700; border-radius: 8px; padding: 0;
            font-family: 'DM Mono', monospace; letter-spacing: 0;
        }
        .otp-row input:focus { border-color: var(--navy); }

        /* Forgot password link */
        .fp-link-wrap { text-align: center; margin-top: -8px; margin-bottom: 14px; }
        .fp-link {
            color: var(--muted); font-size: 12px; cursor: pointer;
            text-decoration: underline; background: none; border: none;
            font-family: 'DM Sans', sans-serif; padding: 0;
        }
        .fp-link:hover { color: var(--navy); }

        /* Resend row */
        .resend-row { text-align: center; margin-top: 10px; }
        .resend-btn {
            background: none; border: none; color: var(--muted); font-size: 12px;
            cursor: pointer; font-family: 'DM Sans', sans-serif; text-decoration: underline;
        }
        .resend-btn:hover { color: var(--navy); }
        .resend-timer { color: var(--muted); font-size: 12px; }

        /* Password strength */
        .pw-strength { height: 3px; border-radius: 2px; margin-top: 6px; background: var(--border); overflow: hidden; }
        .pw-strength-bar { height: 100%; width: 0; border-radius: 2px; transition: width 0.3s, background 0.3s; }

        .footer { text-align: center; color: var(--muted); font-size: 12px; margin-top: 16px; }
    </style>
</head>
<body>
<div class="login-wrapper">

    <div class="logo-area">
        <h1>SPAC</h1>
        <p>San Pedro Assistance Card System</p>
    </div>

    <div class="card">

        <!-- LOGIN step indicators -->
        <div class="steps dots-login" id="dots-login">
            <div class="step-dot active" id="dot1"></div>
            <div class="step-line"></div>
            <div class="step-dot" id="dot2"></div>
            <div class="step-line"></div>
            <div class="step-dot" id="dot3"></div>
        </div>

        <!-- FORGOT PASSWORD step indicators -->
        <div class="steps dots-forgot" id="dots-forgot" style="display:none;">
            <div class="step-dot active" id="fdot1"></div>
            <div class="step-line"></div>
            <div class="step-dot" id="fdot2"></div>
            <div class="step-line"></div>
            <div class="step-dot" id="fdot3"></div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!--  LOGIN STEPS                                                    -->
        <!-- ═══════════════════════════════════════════════════════════════ -->

        <!-- STEP 1: Choose Role -->
        <div class="form-step <?= ($show_step === 'login' && !isset($_POST['action'])) ? 'active' : '' ?>"
             id="step1" data-group="login">
            <h2>Who are you signing in as?</h2>
            <div class="role-grid">
                <button type="button" class="role-btn" onclick="selectRole('barangay','Barangay Official')">
                    <span class="role-dot"></span>
                    <div class="role-text"><h3>Barangay Official</h3><p>Access your barangay dashboard</p></div>
                </button>
                <button type="button" class="role-btn" onclick="selectRole('cityhall','City Hall')">
                    <span class="role-dot"></span>
                    <div class="role-text"><h3>City Hall</h3><p>City-level management</p></div>
                </button>
                <button type="button" class="role-btn" onclick="selectRole('superadmin','Super Admin')">
                    <span class="role-dot"></span>
                    <div class="role-text"><h3>Super Admin</h3><p>Full system access</p></div>
                </button>
            </div>
        </div>

        <!-- STEP 2: Barangay Selection -->
        <div class="form-step" id="step2" data-group="login">
            <h2>Select Your Barangay</h2>
            <div class="form-group">
                <label>Barangay Name</label>
                <select id="brgy_select" onchange="setBrgy()">
                    <option value="">— Select your barangay —</option>
                    <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 foreach ($barangay_list as $b): ?>
                        <option value="<?= $b['barangay_id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                    <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 endforeach; ?>
                </select>
            </div>
            <button type="button" class="btn-primary" onclick="goToCredentials()">Continue</button>
            <button type="button" class="btn-back" onclick="goToStep(1)">← Back</button>
        </div>

        <!-- STEP 3: Email & Password -->
        <div class="form-step" id="step3" data-group="login">
            <h2>Enter Your Credentials</h2>

            <div class="selected-badge">
                <span class="badge-pip"></span>
                <div>
                    <div class="badge-text">Signing in as</div>
                    <div class="badge-name" id="badge-name">—</div>
                </div>
            </div>

            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 if ($error && $show_step === 'login'): ?>
                <div class="error">⚠ <?= htmlspecialchars($error) ?></div>
            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 endif; ?>
            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 if ($success && $show_step === 'login'): ?>
                <div class="success-msg">✓ <?= $success ?></div>
            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 endif; ?>
            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 if (isset($_GET['timeout'])): ?>
                <div class="error">Session expired. Please log in again.</div>
            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 endif; ?>

            <form method="POST" id="login-form">
                <input type="hidden" name="role_type"    id="input-role">
                <input type="hidden" name="barangay_id"  id="input-brgy">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="fp-link-wrap">
                    <button type="button" class="fp-link" onclick="startForgotPassword()">Forgot password?</button>
                </div>
                <button type="submit" class="btn-primary">Sign In</button>
            </form>
            <button type="button" class="btn-back" onclick="goToStep(1)">← Back</button>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!--  FORGOT PASSWORD STEPS                                          -->
        <!-- ═══════════════════════════════════════════════════════════════ -->

        <!-- FP STEP 1: Enter email -->
        <div class="form-step <?= ($show_step === 'fp_email') ? 'active' : '' ?>"
             id="fp-step1" data-group="forgot">
            <h2>Reset Your Password
                <small>Enter the email linked to your account.</small>
            </h2>

            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 if ($error && $show_step === 'fp_email'): ?>
                <div class="error">⚠ <?= htmlspecialchars($error) ?></div>
            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="forgot_send_otp">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="fp_email" placeholder="Enter your account email" required
                           value="<?= htmlspecialchars($_POST['fp_email'] ?? '') ?>">
                </div>
                <button type="submit" class="btn-primary">Send Reset Code</button>
            </form>
            <button type="button" class="btn-back" onclick="cancelForgotPassword()">← Back to Sign In</button>
        </div>

        <!-- FP STEP 2: Enter OTP -->
        <div class="form-step <?= ($show_step === 'fp_otp') ? 'active' : '' ?>"
             id="fp-step2" data-group="forgot">
            <h2>Enter Verification Code
                <small>Check your email for the 6-digit code.</small>
            </h2>

            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 if ($error && $show_step === 'fp_otp'): ?>
                <div class="error">⚠ <?= htmlspecialchars($error) ?></div>
            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 endif; ?>
            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 if ($success && $show_step === 'fp_otp'): ?>
                <div class="success-msg">✓ <?= $success ?></div>
            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 endif; ?>

            <form method="POST" id="otp-form">
                <input type="hidden" name="action" value="forgot_verify_otp">
                <input type="hidden" name="fp_otp"  id="otp-combined">

                <div class="otp-row" id="otp-inputs">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]">
                    <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]">
                </div>

                <button type="submit" class="btn-primary" onclick="combineOtp()">Verify Code</button>
            </form>

            <div class="resend-row">
                <span class="resend-timer" id="resend-timer"></span>
                <form method="POST" style="display:inline;" id="resend-form">
                    <input type="hidden" name="action"   value="forgot_send_otp">
                    <input type="hidden" name="fp_email" value="<?= htmlspecialchars($_SESSION['fp_email'] ?? '') ?>">
                    <button type="submit" class="resend-btn" id="resend-btn" style="display:none;">Resend code</button>
                </form>
            </div>
            <button type="button" class="btn-back" onclick="cancelForgotPassword()">← Back to Sign In</button>
        </div>

        <!-- FP STEP 3: New password -->
        <div class="form-step <?= ($show_step === 'fp_newpass') ? 'active' : '' ?>"
             id="fp-step3" data-group="forgot">
            <h2>Set New Password
                <small>Choose a strong password (min. 8 characters).</small>
            </h2>

            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 if ($error && $show_step === 'fp_newpass'): ?>
                <div class="error">⚠ <?= htmlspecialchars($error) ?></div>
            <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="forgot_set_password">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="fp_newpass" id="fp_newpass"
                           placeholder="Enter new password" required
                           oninput="checkStrength(this.value)">
                    <div class="pw-strength"><div class="pw-strength-bar" id="pw-bar"></div></div>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="fp_confirmpass" placeholder="Re-enter new password" required>
                </div>
                <button type="submit" class="btn-primary">Update Password</button>
            </form>
            <button type="button" class="btn-back" onclick="cancelForgotPassword()">← Cancel</button>
        </div>

        <!-- FP SUCCESS -->
        <div class="form-step <?= ($show_step === 'fp_success') ? 'active' : '' ?>"
             id="fp-success" data-group="forgot">
            <div style="text-align:center; padding: 16px 0;">
                <div style="font-size:40px; margin-bottom:12px;">✓</div>
                <h2 style="color:var(--green); margin-bottom:8px;">Password Updated!</h2>
                <p style="color:var(--muted); font-size:13px; margin-bottom:20px;">
                    Your password has been changed successfully.
                </p>
                <button type="button" class="btn-primary" onclick="cancelForgotPassword()">
                    Back to Sign In
                </button>
            </div>
        </div>

    </div><!-- /.card -->

    <div class="footer">City Government of San Pedro, Laguna</div>
</div>

<script>
    // ── Login flow ─────────────────────────────────────────────────────────
    let selectedRole     = '';
    let selectedBrgyId   = '';
    let selectedBrgyName = '';
    let roleName         = '';

    function selectRole(role, name) {
        selectedRole = role;
        roleName     = name;
        if (role === 'barangay') {
            goToStep(2);
        } else {
            selectedBrgyId = selectedBrgyName = '';
            updateBadge(name);
            goToStep(3);
        }
    }

    function setBrgy() {
        var sel          = document.getElementById('brgy_select');
        selectedBrgyId   = sel.value;
        selectedBrgyName = sel.options[sel.selectedIndex].text;
    }

    function goToCredentials() {
        if (!selectedBrgyId) { alert('Please select your barangay first.'); return; }
        updateBadge('Barangay — ' + selectedBrgyName);
        goToStep(3);
    }

    function updateBadge(name) {
        document.getElementById('badge-name').textContent      = name;
        document.getElementById('input-role').value            = selectedRole;
        document.getElementById('input-brgy').value            = selectedBrgyId;
    }

    function goToStep(step) {
        // Hide all login steps
        document.querySelectorAll('[data-group="login"]').forEach(function(s) { s.classList.remove('active'); });
        document.getElementById('step' + step).classList.add('active');
        // Show login dots, hide forgot dots
        document.getElementById('dots-login').style.display  = 'flex';
        document.getElementById('dots-forgot').style.display = 'none';
        updateLoginDots(step);
    }

    function updateLoginDots(step) {
        for (var i = 1; i <= 3; i++) {
            var dot = document.getElementById('dot' + i);
            dot.classList.remove('active', 'done');
            if (i < step)        dot.classList.add('done');
            else if (i === step) dot.classList.add('active');
        }
    }

    // ── Forgot password flow ───────────────────────────────────────────────
    function startForgotPassword() {
        document.querySelectorAll('[data-group="login"]').forEach(function(s)   { s.classList.remove('active'); });
        document.querySelectorAll('[data-group="forgot"]').forEach(function(s)  { s.classList.remove('active'); });
        document.getElementById('fp-step1').classList.add('active');
        document.getElementById('dots-login').style.display  = 'none';
        document.getElementById('dots-forgot').style.display = 'flex';
        updateForgotDots(1);
    }

    function cancelForgotPassword() {
        document.querySelectorAll('[data-group="forgot"]').forEach(function(s) { s.classList.remove('active'); });
        goToStep(1);
    }

    function updateForgotDots(step) {
        for (var i = 1; i <= 3; i++) {
            var dot = document.getElementById('fdot' + i);
            dot.classList.remove('active', 'done');
            if (i < step)        dot.classList.add('done');
            else if (i === step) dot.classList.add('active');
        }
    }

    // ── OTP inputs: auto-advance & backspace ───────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        var boxes = document.querySelectorAll('.otp-box');
        boxes.forEach(function (box, idx) {
            box.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '');
                if (this.value && idx < boxes.length - 1) boxes[idx + 1].focus();
            });
            box.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && idx > 0) boxes[idx - 1].focus();
            });
            box.addEventListener('paste', function (e) {
                e.preventDefault();
                var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                pasted.split('').forEach(function (ch, i) {
                    if (boxes[idx + i]) boxes[idx + i].value = ch;
                });
                var next = idx + pasted.length;
                if (boxes[next]) boxes[next].focus();
            });
        });

        // ── Resend countdown (60 s) ────────────────────────────────────────
        <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 if ($show_step === 'fp_otp'): ?>
        startResendTimer(60);
        <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 endif; ?>
    });

    function combineOtp() {
        var val = '';
        document.querySelectorAll('.otp-box').forEach(function (b) { val += b.value; });
        document.getElementById('otp-combined').value = val;
    }

    function startResendTimer(seconds) {
        var timer  = document.getElementById('resend-timer');
        var btn    = document.getElementById('resend-btn');
        btn.style.display   = 'none';
        timer.style.display = 'inline';
        var count = seconds;
        var iv = setInterval(function () {
            timer.textContent = 'Resend in ' + count + 's';
            count--;
            if (count < 0) {
                clearInterval(iv);
                timer.style.display = 'none';
                btn.style.display   = 'inline';
            }
        }, 1000);
    }

    // ── Password strength ──────────────────────────────────────────────────
    function checkStrength(val) {
        var bar = document.getElementById('pw-bar');
        var score = 0;
        if (val.length >= 8)              score++;
        if (/[A-Z]/.test(val))            score++;
        if (/[0-9]/.test(val))            score++;
        if (/[^A-Za-z0-9]/.test(val))    score++;
        var widths = ['0%', '25%', '50%', '75%', '100%'];
        var colors = ['#e2e8f0', '#ef4444', '#f97316', '#eab308', '#22c55e'];
        bar.style.width      = widths[score];
        bar.style.background = colors[score];
    }

    // ── Restore state after PHP POST errors ───────────────────────────────
    <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


    // Login error: restore step 3
    if (!isset($_POST['action']) && $error):
        $posted_role   = $_POST['role_type']   ?? '';
        $posted_brgy   = $_POST['barangay_id'] ?? '';
        $restored_name = $posted_role === 'superadmin' ? 'Super Admin'
                       : ($posted_role === 'cityhall'  ? 'City Hall' : 'Barangay Official');
    ?>
        selectedRole   = '<?= htmlspecialchars($posted_role) ?>';
        selectedBrgyId = '<?= htmlspecialchars($posted_brgy) ?>';
        roleName       = '<?= htmlspecialchars($restored_name) ?>';
        updateBadge(roleName);
        goToStep(3);
    <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 endif; ?>

    <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 if ($show_step === 'fp_otp'): ?>
        document.getElementById('dots-login').style.display  = 'none';
        document.getElementById('dots-forgot').style.display = 'flex';
        updateForgotDots(2);
    <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 elseif ($show_step === 'fp_newpass'): ?>
        document.getElementById('dots-login').style.display  = 'none';
        document.getElementById('dots-forgot').style.display = 'flex';
        updateForgotDots(3);
    <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 elseif ($show_step === 'fp_email'): ?>
        document.getElementById('dots-login').style.display  = 'none';
        document.getElementById('dots-forgot').style.display = 'flex';
        updateForgotDots(1);
    <?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

 endif; ?>
</script>
</body>
</html>
