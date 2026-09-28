<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: index.php");
    exit();
}

require_once "config/db.php";

/* =========================================
   SECURITY CONFIGURATION
========================================= */

$settings = [];

$result = mysqli_query(
    $conn,
    "SELECT setting_name, setting_value
     FROM security_settings
     ORDER BY id ASC"
);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $settings[$row["setting_name"]] = $row["setting_value"];
    }
}

/* Security Controls */

$security_controls = [
    "captcha_protection" => [
        "title" => "HUMAN VERIFICATION LAYER",
        "description" => "CAPTCHA verification protects web forms against automated submission attempts.",
        "icon" => "◉"
    ],

    "honeypot_protection" => [
        "title" => "BOT TRAP LAYER",
        "description" => "Hidden honeypot fields identify automated clients without affecting legitimate users.",
        "icon" => "⌁"
    ],

    "rate_limiting" => [
        "title" => "REQUEST THROTTLING",
        "description" => "Controls repeated requests from the same source to reduce automated abuse.",
        "icon" => "⚡"
    ],

    "ip_monitoring" => [
        "title" => "IP SOURCE MONITORING",
        "description" => "Tracks source addresses and identifies suspicious or restricted IP activity.",
        "icon" => "◈"
    ],

    "suspicious_activity_detection" => [
        "title" => "ANOMALOUS ACTIVITY DETECTION",
        "description" => "Analyzes request behavior to identify unusual or potentially automated activity.",
        "icon" => "⚠"
    ],

    "form_validation" => [
        "title" => "INPUT INTEGRITY CHECK",
        "description" => "Validates submitted form data before allowing requests into the application layer.",
        "icon" => "✓"
    ]
];

/* Count enabled controls */

$enabled_controls = 0;

foreach ($security_controls as $key => $control) {
    if (
        isset($settings[$key]) &&
        strtolower($settings[$key]) === "enabled"
    ) {
        $enabled_controls++;
    }
}

$total_controls = count($security_controls);

if ($total_controls > 0) {
    $protection_level = round(
        ($enabled_controls / $total_controls) * 100
    );
} else {
    $protection_level = 0;
}

$admin_name = $_SESSION["admin_name"] ?? "Security Administrator";
$admin_role = $_SESSION["admin_role"] ?? "Security Administrator";
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Anti-Bot Firewall | Security Configuration</title>

<style>

/* =========================================
   GLOBAL
========================================= */

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial, Helvetica, sans-serif;
    background:#02070d;
    color:#eafaff;
    min-height:100vh;
    overflow-x:hidden;
}

/* =========================================
   ANIMATED SECURITY GRID
========================================= */

body::before{
    content:"";
    position:fixed;
    inset:0;

    background:
        linear-gradient(
            rgba(0,217,255,.035) 1px,
            transparent 1px
        ),
        linear-gradient(
            90deg,
            rgba(0,217,255,.035) 1px,
            transparent 1px
        );

    background-size:45px 45px;

    animation:gridMove 18s linear infinite;

    z-index:-3;
}

body::after{
    content:"";

    position:fixed;

    width:650px;
    height:650px;

    right:-220px;
    top:-200px;

    background:
        radial-gradient(
            circle,
            rgba(0,217,255,.13),
            transparent 68%
        );

    animation:glowMove 8s ease-in-out infinite alternate;

    z-index:-2;
}

@keyframes gridMove{

    from{
        transform:translateY(0);
    }

    to{
        transform:translateY(45px);
    }

}

@keyframes glowMove{

    from{
        transform:scale(1);
    }

    to{
        transform:scale(1.25);
    }

}

/* =========================================
   SIDEBAR
========================================= */

.sidebar{
    position:fixed;

    left:0;
    top:0;

    width:255px;
    height:100vh;

    background:rgba(3,12,21,.95);

    border-right:
        1px solid rgba(0,217,255,.18);

    backdrop-filter:blur(18px);

    padding:28px 18px;

    z-index:20;
}

.logo{
    text-align:center;

    padding-bottom:25px;

    border-bottom:
        1px solid rgba(0,217,255,.12);
}

.logo-icon{
    width:58px;
    height:58px;

    margin:auto;

    border:
        2px solid #00d9ff;

    border-radius:50%;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:27px;

    color:#00d9ff;

    box-shadow:
        0 0 18px rgba(0,217,255,.45),
        inset 0 0 18px rgba(0,217,255,.08);
}

.logo h2{
    margin-top:13px;

    font-size:16px;

    letter-spacing:2px;

    color:#fff;
}

.logo p{
    margin-top:5px;

    font-size:9px;

    letter-spacing:2px;

    color:#00d9ff;
}

/* =========================================
   NAVIGATION
========================================= */

.nav{
    margin-top:30px;
}

.nav-title{
    margin:
        0 12px 12px;

    font-size:9px;

    letter-spacing:2px;

    color:#607887;
}

.nav a{
    display:block;

    text-decoration:none;

    color:#89a3b0;

    padding:13px 14px;

    margin-bottom:7px;

    border-radius:8px;

    font-size:12px;

    transition:.25s;
}

.nav a:hover,
.nav a.active{

    color:#fff;

    background:
        rgba(0,217,255,.09);

    border:
        1px solid rgba(0,217,255,.20);

    box-shadow:
        0 0 15px rgba(0,217,255,.07);
}

.nav a span{
    margin-right:10px;

    color:#00d9ff;
}

/* =========================================
   MAIN
========================================= */

.main{
    margin-left:255px;

    min-height:100vh;

    padding:
        30px
        32px
        100px;
}

/* =========================================
   HEADER
========================================= */

.header{
    display:flex;

    justify-content:space-between;

    align-items:flex-start;

    margin-bottom:28px;
}

.header h1{
    font-size:25px;

    letter-spacing:2px;
}

.header p{
    margin-top:8px;

    color:#6d8998;

    font-size:10px;

    letter-spacing:2px;
}

.status{
    display:flex;

    align-items:center;

    gap:9px;

    padding:10px 15px;

    border-radius:25px;

    border:
        1px solid rgba(0,255,170,.25);

    background:
        rgba(0,255,170,.04);

    color:#61ffc7;

    font-size:10px;

    letter-spacing:1.5px;
}

.status-dot{
    width:8px;
    height:8px;

    border-radius:50%;

    background:#00ffa6;

    box-shadow:
        0 0 12px #00ffa6;

    animation:pulse 1.5s infinite;
}

@keyframes pulse{

    0%,100%{
        opacity:1;
    }

    50%{
        opacity:.35;
    }

}

/* =========================================
   ADMIN BAR
========================================= */

.admin-bar{

    background:
        rgba(7,19,30,.75);

    border:
        1px solid rgba(0,217,255,.12);

    border-radius:12px;

    padding:13px 17px;

    margin-bottom:24px;

    display:flex;

    align-items:center;

    justify-content:space-between;
}

.admin-info{
    color:#78919e;

    font-size:11px;
}

.admin-info strong{
    color:#fff;
}

.role{
    color:#00d9ff;

    font-size:10px;

    letter-spacing:1px;
}

.logout{
    text-decoration:none;

    color:#ff7891;

    border:
        1px solid rgba(255,80,110,.25);

    padding:8px 13px;

    border-radius:7px;

    font-size:10px;

    letter-spacing:1px;
}

/* =========================================
   SECURITY SUMMARY
========================================= */

.summary{
    display:grid;

    grid-template-columns:
        1.5fr
        1fr
        1fr;

    gap:16px;

    margin-bottom:22px;
}

.summary-card{

    background:
        rgba(7,18,29,.80);

    border:
        1px solid rgba(0,217,255,.13);

    border-radius:14px;

    padding:20px;

    position:relative;

    overflow:hidden;
}

.summary-card::after{

    content:"";

    position:absolute;

    width:130px;
    height:130px;

    right:-60px;
    top:-60px;

    border-radius:50%;

    background:
        rgba(0,217,255,.06);
}

.summary-label{

    font-size:9px;

    letter-spacing:1.5px;

    color:#6d8998;
}

.summary-value{

    font-size:32px;

    font-weight:bold;

    margin-top:12px;

    color:#fff;
}

.summary-sub{

    margin-top:7px;

    color:#00d9ff;

    font-size:9px;

    letter-spacing:1px;
}

.progress{

    margin-top:16px;

    height:7px;

    background:#091722;

    border-radius:10px;

    overflow:hidden;
}

.progress-fill{

    width:<?= $protection_level ?>%;

    height:100%;

    background:
        linear-gradient(
            90deg,
            #00a8d4,
            #00ffd0
        );

    box-shadow:
        0 0 12px rgba(0,255,210,.45);

    border-radius:10px;
}

/* =========================================
   SECURITY CONTROL GRID
========================================= */

.section-title{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin:
        25px 0 15px;
}

.section-title h2{

    font-size:13px;

    letter-spacing:1.5px;
}

.section-title span{

    color:#607887;

    font-size:9px;

    letter-spacing:1px;
}

.controls{

    display:grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:16px;
}

.control-card{

    background:
        rgba(7,18,29,.80);

    border:
        1px solid rgba(0,217,255,.13);

    border-radius:14px;

    padding:21px;

    display:flex;

    gap:17px;

    transition:.3s;

    position:relative;

    overflow:hidden;
}

.control-card:hover{

    transform:translateY(-3px);

    border-color:
        rgba(0,217,255,.35);

    box-shadow:
        0 12px 35px
        rgba(0,217,255,.08);
}

.control-icon{

    min-width:48px;
    height:48px;

    border:
        1px solid rgba(0,217,255,.25);

    border-radius:11px;

    display:flex;

    align-items:center;
    justify-content:center;

    color:#00d9ff;

    font-size:20px;

    background:
        rgba(0,217,255,.045);

    box-shadow:
        inset 0 0 15px
        rgba(0,217,255,.05);
}

.control-content{

    flex:1;
}

.control-title{

    font-size:11px;

    letter-spacing:1px;

    color:#fff;

    margin-bottom:7px;
}

.control-description{

    font-size:10px;

    line-height:1.6;

    color:#6f8996;

    max-width:550px;
}

.control-bottom{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-top:14px;
}

.control-status{

    display:inline-flex;

    align-items:center;

    gap:6px;

    padding:6px 9px;

    border-radius:6px;

    font-size:8px;

    letter-spacing:1px;
}

.enabled{

    color:#61ffc7;

    background:
        rgba(0,255,170,.06);

    border:
        1px solid rgba(0,255,170,.16);
}

.disabled{

    color:#ff7891;

    background:
        rgba(255,80,110,.06);

    border:
        1px solid rgba(255,80,110,.16);
}

.status-light{

    width:6px;
    height:6px;

    border-radius:50%;

    background:#00ffa6;

    box-shadow:
        0 0 8px #00ffa6;
}

.disabled .status-light{

    background:#ff526f;

    box-shadow:
        0 0 8px #ff526f;
}

.control-id{

    color:#405864;

    font-size:8px;

    letter-spacing:1px;
}

/* =========================================
   INFORMATION PANEL
========================================= */

.info-panel{

    margin-top:20px;

    background:
        rgba(7,18,29,.75);

    border:
        1px solid rgba(0,217,255,.12);

    border-radius:14px;

    padding:20px;
}

.info-panel h3{

    font-size:11px;

    letter-spacing:1.5px;

    margin-bottom:10px;
}

.info-panel p{

    color:#698391;

    font-size:10px;

    line-height:1.8;
}

/* =========================================
   BACK BUTTON
========================================= */

.back-btn{

    position:fixed;

    right:25px;

    bottom:22px;

    text-decoration:none;

    color:#00d9ff;

    border:
        1px solid rgba(0,217,255,.25);

    background:
        rgba(3,12,21,.92);

    padding:11px 17px;

    border-radius:25px;

    font-size:10px;

    letter-spacing:1px;

    backdrop-filter:blur(12px);

    z-index:50;

    transition:.25s;
}

.back-btn:hover{

    background:
        rgba(0,217,255,.08);

    box-shadow:
        0 0 18px rgba(0,217,255,.18);
}

/* =========================================
   RESPONSIVE
========================================= */

@media(max-width:1000px){

    .summary{

        grid-template-columns:
            repeat(2,1fr);
    }

    .controls{

        grid-template-columns:1fr;
    }

}

@media(max-width:750px){

    .sidebar{

        position:relative;

        width:100%;

        height:auto;
    }

    .main{

        margin-left:0;

        padding:20px;
    }

    .header{

        flex-direction:column;

        gap:15px;
    }

    .summary{

        grid-template-columns:1fr;
    }

    .admin-bar{

        flex-direction:column;

        align-items:flex-start;

        gap:12px;
    }

}

</style>

</head>

<body>


<!-- =========================================
     SIDEBAR
========================================= -->

<aside class="sidebar">

    <div class="logo">

        <div class="logo-icon">
            🛡
        </div>

        <h2>ANTI-BOT FIREWALL</h2>

        <p>THREAT DEFENSE SYSTEM</p>

    </div>


    <div class="nav">

        <div class="nav-title">
            SECURITY CONTROL
        </div>


        <a href="dashboard.php">

            <span>◈</span>

            Security Operations Center

        </a>


        <a href="security_logs.php">

            <span>◉</span>

            Security Event Logs

        </a>


        <a href="#">

            <span>⚠</span>

            Threat Activity

        </a>


        <a href="#">

            <span>⌁</span>

            Restricted IP Sources

        </a>


        <div
            class="nav-title"
            style="margin-top:25px;"
        >
            SYSTEM INTELLIGENCE
        </div>


        <a href="#">

            <span>▣</span>

            Request Audit Trail

        </a>


        <a href="#">

            <span>◫</span>

            Security Intelligence

        </a>


        <a
            href="security_settings.php"
            class="active"
        >

            <span>⚙</span>

            Security Configuration

        </a>

    </div>

</aside>


<!-- =========================================
     MAIN
========================================= -->

<main class="main">


    <!-- HEADER -->

    <div class="header">

        <div>

            <h1>
                SECURITY CONFIGURATION
            </h1>

            <p>
                WEB FORM PROTECTION CONTROL CENTER
            </p>

        </div>


        <div class="status">

            <div class="status-dot"></div>

            FIREWALL STATUS: ACTIVE

        </div>

    </div>


    <!-- ADMIN BAR -->

    <div class="admin-bar">

        <div class="admin-info">

            AUTHENTICATED SECURITY ADMINISTRATOR :

            <strong>
                <?= htmlspecialchars($admin_name) ?>
            </strong>

            &nbsp; | &nbsp;

            <span class="role">
                <?= htmlspecialchars($admin_role) ?>
            </span>

        </div>


        <a
            href="logout.php"
            class="logout"
        >
            TERMINATE SECURE SESSION
        </a>

    </div>


    <!-- =========================================
         SECURITY SUMMARY
    ========================================== -->

    <div class="summary">


        <div class="summary-card">

            <div class="summary-label">
                ACTIVE PROTECTION CONTROLS
            </div>

            <div class="summary-value">
                <?= $enabled_controls ?>/<?= $total_controls ?>
            </div>

            <div class="summary-sub">
                SECURITY LAYERS OPERATIONAL
            </div>

            <div class="progress">

                <div class="progress-fill"></div>

            </div>

        </div>


        <div class="summary-card">

            <div class="summary-label">
                PROTECTION COVERAGE
            </div>

            <div class="summary-value">
                <?= $protection_level ?>%
            </div>

            <div class="summary-sub">
                CONFIGURED DEFENSE LEVEL
            </div>

        </div>


        <div class="summary-card">

            <div class="summary-label">
                SYSTEM STATE
            </div>

            <div class="summary-value">
                ACTIVE
            </div>

            <div class="summary-sub">
                SECURITY ENGINE ONLINE
            </div>

        </div>

    </div>


    <!-- =========================================
         CONTROL SECTION
    ========================================== -->

    <div class="section-title">

        <h2>
            SECURITY DEFENSE CONTROLS
        </h2>

        <span>
            DATABASE CONFIGURATION
        </span>

    </div>


    <div class="controls">


        <?php foreach ($security_controls as $key => $control): ?>

            <?php

            $value = $settings[$key] ?? "Disabled";

            $is_enabled =
                strtolower($value) === "enabled";

            ?>

            <div class="control-card">


                <div class="control-icon">

                    <?= $control["icon"] ?>

                </div>


                <div class="control-content">


                    <div class="control-title">

                        <?= htmlspecialchars(
                            $control["title"]
                        ) ?>

                    </div>


                    <div class="control-description">

                        <?= htmlspecialchars(
                            $control["description"]
                        ) ?>

                    </div>


                    <div class="control-bottom">


                        <div
                            class="control-status
                            <?= $is_enabled
                                ? 'enabled'
                                : 'disabled' ?>"
                        >

                            <div class="status-light"></div>

                            <?= $is_enabled
                                ? "PROTECTION ENABLED"
                                : "PROTECTION DISABLED" ?>

                        </div>


                        <div class="control-id">

                            <?= htmlspecialchars($key) ?>

                        </div>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>


    </div>


    <!-- =========================================
         SECURITY INFORMATION
    ========================================== -->

    <div class="info-panel">

        <h3>
            SECURITY CONTROL STATUS
        </h3>

        <p>
            All protection controls displayed above are loaded directly
            from the Anti-Bot Firewall security configuration database.
            These controls represent the defensive layers responsible
            for human verification, automated bot detection, request
            throttling, IP source monitoring, anomaly detection and
            input integrity validation.
        </p>

    </div>


</main>


<!-- BACK BUTTON -->

<a
    href="dashboard.php"
    class="back-btn"
>
    ← SECURITY OPERATIONS CENTER
</a>


</body>

</html>