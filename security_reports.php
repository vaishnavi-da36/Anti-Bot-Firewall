<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: index.php");
    exit();
}

require_once "config/db.php";

$admin_name = $_SESSION["admin_name"] ?? "Security Administrator";
$admin_role = $_SESSION["admin_role"] ?? "Security Administrator";

/* =========================================
   SECURITY REPORT STATISTICS
========================================= */

/* Security Events */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM security_logs"
);
$total_events = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;

/* Critical */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM security_logs
     WHERE severity = 'CRITICAL'"
);
$critical_events = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;

/* High */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM security_logs
     WHERE severity = 'HIGH'"
);
$high_events = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;

/* Medium */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM security_logs
     WHERE severity = 'MEDIUM'"
);
$medium_events = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;

/* Low */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM security_logs
     WHERE severity = 'LOW'"
);
$low_events = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;


/* =========================================
   REQUEST STATISTICS
========================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM submissions"
);
$total_requests = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;


$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM submissions
     WHERE verification_status = 'Verified'"
);
$verified_requests = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;


$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM submissions
     WHERE verification_status = 'Suspicious'"
);
$suspicious_requests = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;


$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM submissions
     WHERE verification_status = 'Blocked'"
);
$blocked_requests = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;


/* =========================================
   BLOCKED IP STATISTICS
========================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM blocked_ips
     WHERE status = 'Active'"
);
$active_restrictions = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;


$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM blocked_ips
     WHERE status = 'Released'"
);
$released_ips = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;


/* =========================================
   SECURITY SETTINGS
========================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM security_settings
     WHERE setting_value = 'Enabled'"
);
$enabled_controls = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;


$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM security_settings"
);
$total_controls = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;


/* =========================================
   PROTECTION RATE
========================================= */

$protection_rate = 0;

if ($total_requests > 0) {

    $protected_requests =
        $suspicious_requests +
        $blocked_requests;

    $protection_rate =
        round(
            ($protected_requests / $total_requests) * 100
        );
}


/* =========================================
   VERIFICATION RATE
========================================= */

$verification_rate = 0;

if ($total_requests > 0) {

    $verification_rate =
        round(
            ($verified_requests / $total_requests) * 100
        );
}


/* =========================================
   RECENT REPORT EVENTS
========================================= */

$recent_events = mysqli_query(
    $conn,
    "SELECT
        event_type,
        ip_address,
        severity,
        action_taken,
        created_at
     FROM security_logs
     ORDER BY id DESC
     LIMIT 8"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Anti-Bot Firewall | Security Intelligence Reports
</title>

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

    color:#e8faff;

    min-height:100vh;

    overflow-x:hidden;
}


/* =========================================
   ANIMATED CYBER GRID
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

    width:700px;

    height:700px;

    right:-280px;

    top:-260px;

    background:
        radial-gradient(
            circle,
            rgba(0,217,255,.13),
            transparent 68%
        );

    animation:glowMove 9s ease-in-out infinite alternate;

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

    background:
        rgba(3,12,21,.96);

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
   NAV
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
   MAIN KPI GRID
========================================= */

.kpi-grid{

    display:grid;

    grid-template-columns:
        repeat(4,1fr);

    gap:16px;

    margin-bottom:22px;
}

.kpi{

    position:relative;

    overflow:hidden;

    background:
        rgba(7,18,29,.82);

    border:
        1px solid rgba(0,217,255,.13);

    border-radius:14px;

    padding:20px;

    transition:.3s;
}

.kpi:hover{

    transform:translateY(-3px);

    border-color:
        rgba(0,217,255,.35);

    box-shadow:
        0 12px 35px
        rgba(0,217,255,.08);
}

.kpi::after{

    content:"";

    position:absolute;

    right:-45px;

    top:-45px;

    width:120px;

    height:120px;

    border-radius:50%;

    background:
        rgba(0,217,255,.06);
}

.kpi-label{

    color:#6d8998;

    font-size:9px;

    letter-spacing:1.5px;
}

.kpi-number{

    margin-top:12px;

    font-size:31px;

    font-weight:bold;

    color:#fff;
}

.kpi-description{

    margin-top:7px;

    color:#00d9ff;

    font-size:8px;

    letter-spacing:1px;
}


/* =========================================
   REPORT GRID
========================================= */

.report-grid{

    display:grid;

    grid-template-columns:
        1.15fr .85fr;

    gap:18px;

    margin-bottom:18px;
}

.panel{

    background:
        rgba(7,18,29,.82);

    border:
        1px solid rgba(0,217,255,.13);

    border-radius:14px;

    overflow:hidden;
}

.panel-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    padding:18px 20px;

    border-bottom:
        1px solid rgba(0,217,255,.10);
}

.panel-header h2{

    font-size:12px;

    letter-spacing:1.5px;
}

.panel-header span{

    color:#00d9ff;

    font-size:8px;

    letter-spacing:1px;
}


/* =========================================
   SEVERITY REPORT
========================================= */

.severity-body{

    padding:22px;
}

.severity-row{

    margin-bottom:20px;
}

.severity-top{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:8px;
}

.severity-name{

    color:#a8bec8;

    font-size:9px;

    letter-spacing:1.2px;
}

.severity-count{

    color:#fff;

    font-size:10px;

    font-family:monospace;
}

.progress{

    width:100%;

    height:7px;

    border-radius:20px;

    background:#091720;

    overflow:hidden;
}

.progress-fill{

    height:100%;

    border-radius:20px;
}

.low{
    background:#00d9a0;
}

.medium{
    background:#ffd04d;
}

.high{
    background:#ff704f;
}

.critical{
    background:#ff315d;
}


/* =========================================
   PROTECTION METRICS
========================================= */

.metrics{

    padding:22px;
}

.metric{

    display:flex;

    justify-content:space-between;

    align-items:center;

    padding:15px 0;

    border-bottom:
        1px solid rgba(0,217,255,.07);
}

.metric:last-child{

    border-bottom:none;
}

.metric-label{

    color:#78929f;

    font-size:9px;

    letter-spacing:1px;
}

.metric-value{

    color:#fff;

    font-size:14px;

    font-weight:bold;
}

.metric-value.cyan{

    color:#00d9ff;
}

.metric-value.green{

    color:#61ffc7;
}

.metric-value.red{

    color:#ff7188;
}

.metric-value.yellow{

    color:#ffd36a;
}


/* =========================================
   CONTROL STATUS
========================================= */

.control-grid{

    display:grid;

    grid-template-columns:
        repeat(3,1fr);

    gap:12px;

    padding:18px 20px 22px;
}

.control{

    padding:15px;

    border-radius:9px;

    background:
        rgba(0,217,255,.025);

    border:
        1px solid rgba(0,217,255,.10);
}

.control-name{

    color:#718b98;

    font-size:8px;

    letter-spacing:1.1px;
}

.control-status{

    margin-top:9px;

    color:#61ffc7;

    font-size:9px;

    letter-spacing:1px;
}


/* =========================================
   RECENT EVENTS
========================================= */

.event-list{

    padding:0 20px 10px;
}

.event{

    display:grid;

    grid-template-columns:
        1.3fr .8fr .7fr 1fr;

    gap:12px;

    align-items:center;

    padding:14px 0;

    border-bottom:
        1px solid rgba(0,217,255,.06);
}

.event:last-child{

    border-bottom:none;
}

.event-type{

    color:#d4e5eb;

    font-size:9px;

    font-weight:bold;
}

.event-ip{

    color:#00d9ff;

    font-size:9px;

    font-family:monospace;
}

.event-action{

    color:#78919e;

    font-size:8px;
}

.event-time{

    color:#607987;

    font-size:8px;

    text-align:right;
}


/* =========================================
   SEVERITY BADGES
========================================= */

.badge{

    display:inline-block;

    padding:5px 7px;

    border-radius:5px;

    font-size:7px;

    letter-spacing:1px;
}

.badge-low{

    color:#61ffc7;

    background:
        rgba(0,255,170,.06);

    border:
        1px solid rgba(0,255,170,.16);
}

.badge-medium{

    color:#ffd36a;

    background:
        rgba(255,200,60,.06);

    border:
        1px solid rgba(255,200,60,.16);
}

.badge-high{

    color:#ff8b72;

    background:
        rgba(255,90,60,.06);

    border:
        1px solid rgba(255,90,60,.16);
}

.badge-critical{

    color:#ff6680;

    background:
        rgba(255,40,80,.08);

    border:
        1px solid rgba(255,40,80,.20);
}


/* =========================================
   REPORT NOTE
========================================= */

.report-note{

    margin-top:18px;

    padding:18px 20px;

    border-radius:11px;

    border:
        1px solid rgba(0,217,255,.12);

    background:
        rgba(0,217,255,.025);

    color:#738b97;

    font-size:9px;

    line-height:1.7;
}

.report-note strong{

    color:#00d9ff;

    letter-spacing:1px;
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

@media(max-width:1100px){

    .kpi-grid{

        grid-template-columns:
            repeat(2,1fr);
    }

    .report-grid{

        grid-template-columns:1fr;
    }

}

@media(max-width:800px){

    .control-grid{

        grid-template-columns:
            repeat(2,1fr);
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

    .kpi-grid{

        grid-template-columns:1fr;
    }

    .admin-bar{

        flex-direction:column;

        align-items:flex-start;

        gap:12px;
    }

    .control-grid{

        grid-template-columns:1fr;
    }

    .event{

        grid-template-columns:1fr;
    }

    .event-time{

        text-align:left;
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

        <h2>
            ANTI-BOT FIREWALL
        </h2>

        <p>
            THREAT DEFENSE SYSTEM
        </p>

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


        <a href="threat_activity.php">

            <span>⚠</span>

            Threat Activity

        </a>


        <a href="blocked_ips.php">

            <span>⌁</span>

            Restricted IP Sources

        </a>


        <div
            class="nav-title"
            style="margin-top:25px;"
        >
            SYSTEM INTELLIGENCE
        </div>


        <a href="request_audit.php">

            <span>▣</span>

            Request Audit Trail

        </a>


        <a
            href="security_reports.php"
            class="active"
        >

            <span>◫</span>

            Security Intelligence

        </a>


        <a href="security_settings.php">

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
                SECURITY INTELLIGENCE REPORTS
            </h1>

            <p>
                FIREWALL PERFORMANCE & THREAT PROTECTION ANALYSIS
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
         KPI CARDS
    ========================================== -->

    <div class="kpi-grid">


        <div class="kpi">

            <div class="kpi-label">
                TOTAL SECURITY EVENTS
            </div>

            <div class="kpi-number">
                <?= $total_events ?>
            </div>

            <div class="kpi-description">
                EVENT RECORDS
            </div>

        </div>


        <div class="kpi">

            <div class="kpi-label">
                THREATS NEUTRALIZED
            </div>

            <div class="kpi-number">
                <?= $blocked_requests ?>
            </div>

            <div class="kpi-description">
                BLOCKED FORM REQUESTS
            </div>

        </div>


        <div class="kpi">

            <div class="kpi-label">
                ACTIVE IP RESTRICTIONS
            </div>

            <div class="kpi-number">
                <?= $active_restrictions ?>
            </div>

            <div class="kpi-description">
                RESTRICTED SOURCES
            </div>

        </div>


        <div class="kpi">

            <div class="kpi-label">
                CONTROL COVERAGE
            </div>

            <div class="kpi-number">
                <?= $total_controls > 0
                    ? round(
                        ($enabled_controls / $total_controls) * 100
                    )
                    : 0 ?>%
            </div>

            <div class="kpi-description">
                SECURITY CONTROLS ENABLED
            </div>

        </div>

    </div>


    <!-- =========================================
         REPORT GRID
    ========================================== -->

    <div class="report-grid">


        <!-- SEVERITY -->

        <section class="panel">

            <div class="panel-header">

                <h2>
                    THREAT SEVERITY DISTRIBUTION
                </h2>

                <span>
                    SECURITY EVENTS
                </span>

            </div>


            <div class="severity-body">


                <div class="severity-row">

                    <div class="severity-top">

                        <span class="severity-name">
                            CRITICAL THREATS
                        </span>

                        <span class="severity-count">
                            <?= $critical_events ?>
                        </span>

                    </div>

                    <div class="progress">

                        <div
                            class="progress-fill critical"
                            style="width:<?= $total_events > 0
                                ? ($critical_events / $total_events) * 100
                                : 0 ?>%;"
                        ></div>

                    </div>

                </div>


                <div class="severity-row">

                    <div class="severity-top">

                        <span class="severity-name">
                            HIGH THREATS
                        </span>

                        <span class="severity-count">
                            <?= $high_events ?>
                        </span>

                    </div>

                    <div class="progress">

                        <div
                            class="progress-fill high"
                            style="width:<?= $total_events > 0
                                ? ($high_events / $total_events) * 100
                                : 0 ?>%;"
                        ></div>

                    </div>

                </div>


                <div class="severity-row">

                    <div class="severity-top">

                        <span class="severity-name">
                            MEDIUM EVENTS
                        </span>

                        <span class="severity-count">
                            <?= $medium_events ?>
                        </span>

                    </div>

                    <div class="progress">

                        <div
                            class="progress-fill medium"
                            style="width:<?= $total_events > 0
                                ? ($medium_events / $total_events) * 100
                                : 0 ?>%;"
                        ></div>

                    </div>

                </div>


                <div class="severity-row">

                    <div class="severity-top">

                        <span class="severity-name">
                            LOW EVENTS
                        </span>

                        <span class="severity-count">
                            <?= $low_events ?>
                        </span>

                    </div>

                    <div class="progress">

                        <div
                            class="progress-fill low"
                            style="width:<?= $total_events > 0
                                ? ($low_events / $total_events) * 100
                                : 0 ?>%;"
                        ></div>

                    </div>

                </div>


            </div>

        </section>


        <!-- PROTECTION METRICS -->

        <section class="panel">

            <div class="panel-header">

                <h2>
                    PROTECTION METRICS
                </h2>

                <span>
                    FIREWALL ANALYTICS
                </span>

            </div>


            <div class="metrics">


                <div class="metric">

                    <span class="metric-label">
                        TOTAL FORM REQUESTS
                    </span>

                    <span class="metric-value cyan">
                        <?= $total_requests ?>
                    </span>

                </div>


                <div class="metric">

                    <span class="metric-label">
                        VERIFIED REQUESTS
                    </span>

                    <span class="metric-value green">
                        <?= $verified_requests ?>
                    </span>

                </div>


                <div class="metric">

                    <span class="metric-label">
                        ANOMALOUS REQUESTS
                    </span>

                    <span class="metric-value yellow">
                        <?= $suspicious_requests ?>
                    </span>

                </div>


                <div class="metric">

                    <span class="metric-label">
                        BLOCKED REQUESTS
                    </span>

                    <span class="metric-value red">
                        <?= $blocked_requests ?>
                    </span>

                </div>


                <div class="metric">

                    <span class="metric-label">
                        REQUEST VERIFICATION RATE
                    </span>

                    <span class="metric-value cyan">
                        <?= $verification_rate ?>%
                    </span>

                </div>


                <div class="metric">

                    <span class="metric-label">
                        PROTECTION ACTIVITY RATE
                    </span>

                    <span class="metric-value green">
                        <?= $protection_rate ?>%
                    </span>

                </div>


            </div>

        </section>


    </div>


    <!-- =========================================
         SECURITY CONTROLS
    ========================================== -->

    <section class="panel">

        <div class="panel-header">

            <h2>
                SECURITY CONTROL STATUS
            </h2>

            <span>
                ACTIVE DEFENSE LAYERS
            </span>

        </div>


        <div class="control-grid">


            <div class="control">

                <div class="control-name">
                    HUMAN VERIFICATION LAYER
                </div>

                <div class="control-status">
                    ● ENABLED
                </div>

            </div>


            <div class="control">

                <div class="control-name">
                    BOT TRAP LAYER
                </div>

                <div class="control-status">
                    ● ENABLED
                </div>

            </div>


            <div class="control">

                <div class="control-name">
                    REQUEST THROTTLING
                </div>

                <div class="control-status">
                    ● ENABLED
                </div>

            </div>


            <div class="control">

                <div class="control-name">
                    IP SOURCE MONITORING
                </div>

                <div class="control-status">
                    ● ENABLED
                </div>

            </div>


            <div class="control">

                <div class="control-name">
                    ANOMALOUS ACTIVITY DETECTION
                </div>

                <div class="control-status">
                    ● ENABLED
                </div>

            </div>


            <div class="control">

                <div class="control-name">
                    INPUT INTEGRITY CHECK
                </div>

                <div class="control-status">
                    ● ENABLED
                </div>

            </div>


        </div>

    </section>


    <!-- =========================================
         RECENT SECURITY EVENTS
    ========================================== -->

    <section
        class="panel"
        style="margin-top:18px;"
    >

        <div class="panel-header">

            <h2>
                RECENT SECURITY EVENTS
            </h2>

            <span>
                LATEST 8 RECORDS
            </span>

        </div>


        <div class="event-list">


        <?php if (
            $recent_events &&
            mysqli_num_rows($recent_events) > 0
        ): ?>


            <?php while (
                $event = mysqli_fetch_assoc($recent_events)
            ): ?>

                <?php

                $severity =
                    strtoupper(
                        $event["severity"]
                    );

                $severity_class =
                    strtolower($severity);

                ?>


                <div class="event">


                    <div class="event-type">

                        <?= htmlspecialchars(
                            $event["event_type"]
                        ) ?>

                    </div>


                    <div class="event-ip">

                        <?= htmlspecialchars(
                            $event["ip_address"]
                        ) ?>

                    </div>


                    <div>

                        <span
                            class="badge badge-<?= htmlspecialchars(
                                $severity_class
                            ) ?>"
                        >

                            <?= htmlspecialchars(
                                $severity
                            ) ?>

                        </span>

                    </div>


                    <div class="event-time">

                        <?= date(
                            "d M Y | H:i",
                            strtotime(
                                $event["created_at"]
                            )
                        ) ?>

                    </div>


                </div>

            <?php endwhile; ?>


        <?php else: ?>


            <div
                style="
                    padding:35px 0;
                    text-align:center;
                    color:#607987;
                    font-size:10px;
                "
            >

                NO SECURITY EVENTS AVAILABLE

            </div>


        <?php endif; ?>


        </div>

    </section>


    <!-- REPORT NOTE -->

    <div class="report-note">

        <strong>
            SECURITY INTELLIGENCE:
        </strong>

        This report consolidates firewall telemetry,
        form-request verification, IP restrictions and
        security-event severity into a centralized
        intelligence view. The displayed metrics are
        calculated directly from the Anti-Bot Firewall
        security database.

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