<?php
session_start();
require_once "config/db.php";

/* =========================================================
   ANTI-BOT FIREWALL - PROTECTED FORM
   ========================================================= */

date_default_timezone_set("Asia/Kolkata");

$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Client';

$message = "";
$messageType = "";

$formName = "";
$formEmail = "";
$formMessage = "";

/* =========================================================
   SECURITY CONFIGURATION
   ========================================================= */

$RATE_LIMIT_COUNT = 5;
$RATE_LIMIT_SECONDS = 60;

/* =========================================================
   GET CAPTCHA
   ========================================================= */

if (!isset($_SESSION['captcha_a']) || !isset($_SESSION['captcha_b'])) {
    $_SESSION['captcha_a'] = rand(1, 9);
    $_SESSION['captcha_b'] = rand(1, 9);
}

$captchaQuestion =
    $_SESSION['captcha_a'] . " + " . $_SESSION['captcha_b'] . " = ?";

/* =========================================================
   CSRF TOKEN
   ========================================================= */

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =========================================================
   HELPER FUNCTIONS
   ========================================================= */

function securityLog($conn, $eventType, $ip, $severity, $action, $userAgent)
{
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO security_logs
        (event_type, ip_address, severity, action_taken, user_agent)
        VALUES (?, ?, ?, ?, ?)"
    );

    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            "sssss",
            $eventType,
            $ip,
            $severity,
            $action,
            $userAgent
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function saveSubmission($conn, $ip, $formType, $status, $score)
{
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO submissions
        (ip_address, form_type, verification_status, threat_score)
        VALUES (?, ?, ?, ?)"
    );

    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $ip,
            $formType,
            $status,
            $score
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function regenerateCaptcha()
{
    $_SESSION['captcha_a'] = rand(1, 9);
    $_SESSION['captcha_b'] = rand(1, 9);
}

/* =========================================================
   FORM PROCESSING
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $threatScore = 0;
    $blocked = false;
    $suspicious = false;

    $formName = trim($_POST["name"] ?? "");
    $formEmail = trim($_POST["email"] ?? "");
    $formMessage = trim($_POST["message"] ?? "");

    $honeypot = trim($_POST["website"] ?? "");
    $captchaAnswer = trim($_POST["captcha"] ?? "");
    $csrfToken = $_POST["csrf_token"] ?? "";

    /* =====================================================
       1. CSRF VALIDATION
       ===================================================== */

    if (
        empty($csrfToken) ||
        !hash_equals($_SESSION['csrf_token'], $csrfToken)
    ) {
        $threatScore += 90;

        securityLog(
            $conn,
            "CSRF TOKEN VIOLATION",
            $ip,
            "CRITICAL",
            "Request Blocked",
            $userAgent
        );

        saveSubmission(
            $conn,
            $ip,
            "Contact Form",
            "Blocked",
            $threatScore
        );

        $message = "Security validation failed. Request blocked.";
        $messageType = "danger";
        $blocked = true;
    }

    /* =====================================================
       2. IP SOURCE MONITORING
       ===================================================== */

    if (!$blocked) {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM blocked_ips
             WHERE ip_address = ?
             AND status = 'Active'
             LIMIT 1"
        );

        $isBlockedIP = false;

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $ip);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $isBlockedIP = true;
            }

            mysqli_stmt_close($stmt);
        }

        if ($isBlockedIP) {

            $threatScore += 95;

            securityLog(
                $conn,
                "BLOCKED IP REQUEST",
                $ip,
                "CRITICAL",
                "Request Blocked",
                $userAgent
            );

            saveSubmission(
                $conn,
                $ip,
                "Contact Form",
                "Blocked",
                $threatScore
            );

            $message = "Your IP source is restricted by the security firewall.";
            $messageType = "danger";
            $blocked = true;
        }
    }

    /* =====================================================
       3. REQUEST RATE LIMITING
       ===================================================== */

    if (!$blocked) {

        $timeLimit = date(
            "Y-m-d H:i:s",
            time() - $RATE_LIMIT_SECONDS
        );

        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) FROM submissions
             WHERE ip_address = ?
             AND created_at >= ?"
        );

        $requestCount = 0;

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ss",
                $ip,
                $timeLimit
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $requestCount);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
        }

        if ($requestCount >= $RATE_LIMIT_COUNT) {

            $threatScore += 50;

            securityLog(
                $conn,
                "RATE LIMIT VIOLATION",
                $ip,
                "HIGH",
                "Request Throttled",
                $userAgent
            );

            saveSubmission(
                $conn,
                $ip,
                "Contact Form",
                "Blocked",
                $threatScore
            );

            $message = "Request rate exceeded. Please wait before trying again.";
            $messageType = "danger";
            $blocked = true;
        }
    }

    /* =====================================================
       4. HONEYPOT BOT TRAP
       ===================================================== */

    if (!$blocked && !empty($honeypot)) {

        $threatScore += 90;

        securityLog(
            $conn,
            "HONEYPOT TRIGGERED",
            $ip,
            "CRITICAL",
            "Bot Request Blocked",
            $userAgent
        );

        saveSubmission(
            $conn,
            $ip,
            "Contact Form",
            "Blocked",
            $threatScore
        );

        $message = "Automated request detected and blocked.";
        $messageType = "danger";
        $blocked = true;
    }

    /* =====================================================
       5. CAPTCHA / HUMAN VERIFICATION
       ===================================================== */

    if (!$blocked) {

        $correctCaptcha =
            (int)$_SESSION['captcha_a'] +
            (int)$_SESSION['captcha_b'];

        if (
            $captchaAnswer === "" ||
            !is_numeric($captchaAnswer) ||
            (int)$captchaAnswer !== $correctCaptcha
        ) {

            $threatScore += 70;

            securityLog(
                $conn,
                "CAPTCHA FAILURE",
                $ip,
                "HIGH",
                "Human Verification Failed",
                $userAgent
            );

            saveSubmission(
                $conn,
                $ip,
                "Contact Form",
                "Blocked",
                $threatScore
            );

            regenerateCaptcha();

            $message = "Human verification failed. Please solve the security challenge correctly.";
            $messageType = "danger";
            $blocked = true;
        }
    }

    /* =====================================================
       6. INPUT INTEGRITY VALIDATION
       ===================================================== */

    if (!$blocked) {

        if (
            $formName === "" ||
            strlen($formName) < 2 ||
            strlen($formName) > 100
        ) {
            $threatScore += 20;
        }

        if (
            !filter_var($formEmail, FILTER_VALIDATE_EMAIL) ||
            strlen($formEmail) > 150
        ) {
            $threatScore += 20;
        }

        if (
            $formMessage === "" ||
            strlen($formMessage) < 10 ||
            strlen($formMessage) > 2000
        ) {
            $threatScore += 20;
        }
    }

    /* =====================================================
       7. SUSPICIOUS CONTENT DETECTION
       ===================================================== */

    if (!$blocked) {

        $combinedInput = strtolower(
            $formName . " " .
            $formEmail . " " .
            $formMessage
        );

        $suspiciousPatterns = [
            "<script",
            "</script>",
            "javascript:",
            "onerror=",
            "onload=",
            "union select",
            "drop table",
            "insert into",
            "delete from",
            "../",
            "wget ",
            "curl ",
            "base64_decode",
            "eval(",
            "shell_exec"
        ];

        foreach ($suspiciousPatterns as $pattern) {

            if (strpos($combinedInput, $pattern) !== false) {

                $threatScore += 35;

                $suspicious = true;

                break;
            }
        }

        /* URL / spam detection */

        preg_match_all(
            '/https?:\/\/|www\./i',
            $combinedInput,
            $urlMatches
        );

        $urlCount = count($urlMatches[0]);

        if ($urlCount >= 2) {
            $threatScore += 25;
            $suspicious = true;
        }

        /* Excessive repeated characters */

        if (preg_match('/(.)\1{7,}/', $formMessage)) {
            $threatScore += 20;
            $suspicious = true;
        }

        /* Excessive message size */

        if (strlen($formMessage) > 1500) {
            $threatScore += 15;
            $suspicious = true;
        }
    }

    /* =====================================================
       8. FINAL SECURITY DECISION
       ===================================================== */

    if (!$blocked) {

        if (
            $formName === "" ||
            !filter_var($formEmail, FILTER_VALIDATE_EMAIL) ||
            $formMessage === ""
        ) {

            $threatScore += 25;

            securityLog(
                $conn,
                "FORM VALIDATION FAILURE",
                $ip,
                "MEDIUM",
                "Invalid Request Rejected",
                $userAgent
            );

            saveSubmission(
                $conn,
                $ip,
                "Contact Form",
                "Suspicious",
                $threatScore
            );

            $message = "Input integrity validation failed. Please check your form details.";
            $messageType = "warning";

        } elseif ($threatScore >= 70) {

            securityLog(
                $conn,
                "THREAT DETECTED",
                $ip,
                "HIGH",
                "Request Blocked",
                $userAgent
            );

            saveSubmission(
                $conn,
                $ip,
                "Contact Form",
                "Blocked",
                $threatScore
            );

            $message = "Security threat detected. Your request has been blocked.";
            $messageType = "danger";

        } elseif ($threatScore >= 40 || $suspicious) {

            securityLog(
                $conn,
                "SUSPICIOUS REQUEST DETECTED",
                $ip,
                "MEDIUM",
                "Request Flagged",
                $userAgent
            );

            saveSubmission(
                $conn,
                $ip,
                "Contact Form",
                "Suspicious",
                $threatScore
            );

            $message = "Request flagged by the security layer and placed under security review.";
            $messageType = "warning";

        } else {

            securityLog(
                $conn,
                "VALID REQUEST VERIFIED",
                $ip,
                "LOW",
                "Request Allowed",
                $userAgent
            );

            saveSubmission(
                $conn,
                $ip,
                "Contact Form",
                "Verified",
                $threatScore
            );

            $message =
                "Request verified successfully. Your submission passed all security checks.";

            $messageType = "success";

            $formName = "";
            $formEmail = "";
            $formMessage = "";

            regenerateCaptcha();

            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Protected Web Form | Anti-Bot Firewall</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    min-height: 100vh;
    font-family: Arial, Helvetica, sans-serif;
    background:
        radial-gradient(circle at 15% 20%, rgba(0, 229, 255, 0.10), transparent 28%),
        radial-gradient(circle at 85% 75%, rgba(0, 119, 255, 0.10), transparent 30%),
        linear-gradient(135deg, #020812, #03121f 50%, #01050b);
    color: #e9fbff;
    overflow-x: hidden;
}

/* Animated grid */

body::before {
    content: "";
    position: fixed;
    inset: 0;
    background-image:
        linear-gradient(rgba(0, 229, 255, 0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 229, 255, 0.035) 1px, transparent 1px);
    background-size: 45px 45px;
    animation: gridMove 12s linear infinite;
    pointer-events: none;
}

@keyframes gridMove {
    from {
        transform: translate(0, 0);
    }

    to {
        transform: translate(45px, 45px);
    }
}

.container {
    width: min(1150px, 94%);
    margin: auto;
    padding: 35px 0 90px;
}

/* Header */

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 14px;
}

.logo {
    width: 52px;
    height: 52px;
    border: 1px solid #00e5ff;
    border-radius: 15px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 25px;
    color: #00e5ff;
    background: rgba(0, 229, 255, 0.08);
    box-shadow: 0 0 25px rgba(0, 229, 255, 0.18);
}

.brand h1 {
    font-size: 20px;
    letter-spacing: 2px;
}

.brand p {
    color: #7fa5b5;
    font-size: 11px;
    margin-top: 4px;
    letter-spacing: 1px;
}

.status {
    padding: 10px 15px;
    border: 1px solid rgba(0, 255, 180, 0.35);
    border-radius: 30px;
    color: #00ffb3;
    background: rgba(0, 255, 180, 0.06);
    font-size: 11px;
    letter-spacing: 1px;
}

/* Main grid */

.main-grid {
    display: grid;
    grid-template-columns: 1.4fr 0.8fr;
    gap: 25px;
}

.card {
    background: rgba(5, 18, 30, 0.82);
    border: 1px solid rgba(0, 229, 255, 0.16);
    border-radius: 22px;
    padding: 30px;
    backdrop-filter: blur(16px);
    box-shadow:
        0 25px 80px rgba(0, 0, 0, 0.35),
        inset 0 1px rgba(255,255,255,0.025);
}

.card-title {
    font-size: 17px;
    letter-spacing: 1.5px;
    margin-bottom: 8px;
}

.card-subtitle {
    color: #789aaa;
    font-size: 12px;
    line-height: 1.7;
    margin-bottom: 25px;
}

/* Form */

.field {
    margin-bottom: 18px;
}

label {
    display: block;
    font-size: 11px;
    color: #8db2c0;
    letter-spacing: 1px;
    margin-bottom: 8px;
}

input,
textarea {
    width: 100%;
    border: 1px solid rgba(0, 229, 255, 0.18);
    background: rgba(0, 8, 15, 0.65);
    color: #eaffff;
    border-radius: 12px;
    padding: 13px 14px;
    outline: none;
    font-size: 13px;
    transition: 0.25s;
}

input:focus,
textarea:focus {
    border-color: #00e5ff;
    box-shadow: 0 0 18px rgba(0, 229, 255, 0.12);
}

textarea {
    min-height: 135px;
    resize: vertical;
}

/* Honeypot */

.honeypot {
    position: absolute !important;
    left: -9999px !important;
    width: 1px !important;
    height: 1px !important;
    opacity: 0 !important;
}

/* Captcha */

.captcha-box {
    border: 1px solid rgba(0, 229, 255, 0.18);
    border-radius: 14px;
    padding: 17px;
    background: rgba(0, 229, 255, 0.035);
    margin-bottom: 20px;
}

.captcha-title {
    color: #00e5ff;
    font-size: 11px;
    letter-spacing: 1px;
    margin-bottom: 10px;
}

.captcha-question {
    font-size: 20px;
    font-weight: bold;
    letter-spacing: 2px;
    margin-bottom: 12px;
}

/* Button */

button {
    width: 100%;
    padding: 14px;
    border: 1px solid #00e5ff;
    border-radius: 12px;
    background: linear-gradient(
        135deg,
        rgba(0, 229, 255, 0.18),
        rgba(0, 100, 255, 0.16)
    );
    color: #dfffff;
    font-weight: bold;
    letter-spacing: 1px;
    cursor: pointer;
    transition: 0.25s;
}

button:hover {
    transform: translateY(-2px);
    background: rgba(0, 229, 255, 0.22);
    box-shadow: 0 0 25px rgba(0, 229, 255, 0.18);
}

/* Messages */

.alert {
    padding: 14px 16px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 12px;
    line-height: 1.6;
}

.alert.success {
    border: 1px solid rgba(0,255,170,.35);
    background: rgba(0,255,170,.07);
    color: #7dffd6;
}

.alert.warning {
    border: 1px solid rgba(255,190,0,.35);
    background: rgba(255,190,0,.07);
    color: #ffd86b;
}

.alert.danger {
    border: 1px solid rgba(255,70,90,.35);
    background: rgba(255,70,90,.07);
    color: #ff8997;
}

/* Security layers */

.layers {
    display: grid;
    gap: 12px;
}

.layer {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    border: 1px solid rgba(0,229,255,.10);
    border-radius: 13px;
    background: rgba(0,0,0,.18);
}

.layer-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #00e5ff;
    background: rgba(0,229,255,.08);
    font-size: 16px;
}

.layer strong {
    display: block;
    font-size: 12px;
    margin-bottom: 3px;
}

.layer span {
    font-size: 10px;
    color: #6e8c9b;
}

.active-dot {
    margin-left: auto;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #00ffb3;
    box-shadow: 0 0 10px #00ffb3;
}

/* Info */

.info {
    margin-top: 22px;
    padding: 16px;
    border-left: 2px solid #00e5ff;
    background: rgba(0,229,255,.035);
    color: #7f9fac;
    font-size: 11px;
    line-height: 1.7;
}

/* Back button */

.back-btn {
    position: fixed;
    right: 22px;
    bottom: 20px;
    text-decoration: none;
    color: #bdf8ff;
    background: rgba(3,16,27,.92);
    border: 1px solid rgba(0,229,255,.35);
    padding: 11px 16px;
    border-radius: 30px;
    font-size: 11px;
    letter-spacing: .8px;
    backdrop-filter: blur(10px);
    box-shadow: 0 10px 30px rgba(0,0,0,.35);
    z-index: 10;
}

.back-btn:hover {
    border-color: #00e5ff;
    box-shadow: 0 0 20px rgba(0,229,255,.15);
}

/* Responsive */

@media (max-width: 850px) {

    .main-grid {
        grid-template-columns: 1fr;
    }

    .header {
        align-items: flex-start;
        flex-direction: column;
    }

    .status {
        align-self: flex-start;
    }
}

</style>

</head>

<body>

<div class="container">

    <div class="header">

        <div class="brand">

            <div class="logo">⌬</div>

            <div>
                <h1>ANTI-BOT FIREWALL</h1>
                <p>PROTECTED WEB FORM GATEWAY</p>
            </div>

        </div>

        <div class="status">
            ● FIREWALL PROTECTION ACTIVE
        </div>

    </div>


    <div class="main-grid">

        <!-- FORM -->

        <div class="card">

            <div class="card-title">
                SECURE FORM SUBMISSION
            </div>

            <div class="card-subtitle">
                This request is protected by multiple security validation
                layers before it can enter the application.
            </div>

            <?php if ($message !== ""): ?>

                <div class="alert <?php echo htmlspecialchars($messageType); ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>


            <form method="POST" autocomplete="off">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                >

                <!-- Honeypot Bot Trap -->

                <input
                    type="text"
                    name="website"
                    class="honeypot"
                    tabindex="-1"
                    autocomplete="off"
                >

                <div class="field">

                    <label>FULL NAME</label>

                    <input
                        type="text"
                        name="name"
                        maxlength="100"
                        value="<?php echo htmlspecialchars($formName); ?>"
                        placeholder="Enter your name"
                    >

                </div>


                <div class="field">

                    <label>EMAIL ADDRESS</label>

                    <input
                        type="email"
                        name="email"
                        maxlength="150"
                        value="<?php echo htmlspecialchars($formEmail); ?>"
                        placeholder="Enter your email address"
                    >

                </div>


                <div class="field">

                    <label>SECURE MESSAGE</label>

                    <textarea
                        name="message"
                        maxlength="2000"
                        placeholder="Enter your message"
                    ><?php echo htmlspecialchars($formMessage); ?></textarea>

                </div>


                <div class="captcha-box">

                    <div class="captcha-title">
                        HUMAN VERIFICATION LAYER
                    </div>

                    <div class="captcha-question">
                        <?php echo htmlspecialchars($captchaQuestion); ?>
                    </div>

                    <input
                        type="number"
                        name="captcha"
                        placeholder="Enter verification answer"
                        min="0"
                        max="100"
                    >

                </div>


                <button type="submit">
                    VERIFY & SECURELY SUBMIT REQUEST
                </button>

            </form>

        </div>


        <!-- SECURITY PANEL -->

        <div class="card">

            <div class="card-title">
                ACTIVE DEFENSE LAYERS
            </div>

            <div class="card-subtitle">
                Every incoming request passes through the following
                security controls.
            </div>


            <div class="layers">

                <div class="layer">

                    <div class="layer-icon">◎</div>

                    <div>
                        <strong>Human Verification</strong>
                        <span>Arithmetic CAPTCHA validation</span>
                    </div>

                    <div class="active-dot"></div>

                </div>


                <div class="layer">

                    <div class="layer-icon">◈</div>

                    <div>
                        <strong>Bot Trap Layer</strong>
                        <span>Invisible honeypot detection</span>
                    </div>

                    <div class="active-dot"></div>

                </div>


                <div class="layer">

                    <div class="layer-icon">◌</div>

                    <div>
                        <strong>Request Throttling</strong>
                        <span>IP-based request frequency control</span>
                    </div>

                    <div class="active-dot"></div>

                </div>


                <div class="layer">

                    <div class="layer-icon">⌁</div>

                    <div>
                        <strong>IP Source Monitoring</strong>
                        <span>Restricted source detection</span>
                    </div>

                    <div class="active-dot"></div>

                </div>


                <div class="layer">

                    <div class="layer-icon">⚠</div>

                    <div>
                        <strong>Anomaly Detection</strong>
                        <span>Suspicious content analysis</span>
                    </div>

                    <div class="active-dot"></div>

                </div>


                <div class="layer">

                    <div class="layer-icon">✓</div>

                    <div>
                        <strong>Input Integrity</strong>
                        <span>Validation and sanitization</span>
                    </div>

                    <div class="active-dot"></div>

                </div>

            </div>


            <div class="info">

                <strong>SECURITY DECISION ENGINE</strong><br><br>

                Every request receives a threat score based on
                detected security indicators. Legitimate requests
                are verified, suspicious requests are flagged,
                and high-risk requests are automatically blocked
                and recorded in the Security Operations Center.

            </div>

        </div>

    </div>

</div>


<a class="back-btn" href="index.php">
    ← SECURE ACCESS GATEWAY
</a>

</body>
</html>