<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: index.php");
    exit();
}

require_once "config/db.php";

/* =========================================
   REAL SECURITY STATISTICS
========================================= */

/* Total Security Events */
$total_events = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM security_logs");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_events = (int)$row["total"];
}

/* Threat Detections */
$threat_detections = 0;
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM security_logs 
     WHERE severity IN ('HIGH','CRITICAL')"
);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $threat_detections = (int)$row["total"];
}

/* Threats Neutralized */
$threats_neutralized = 0;
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM submissions 
     WHERE verification_status = 'Blocked'"
);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $threats_neutralized = (int)$row["total"];
}

/* Restricted IP Sources */
$restricted_ips = 0;
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM blocked_ips 
     WHERE status = 'Active'"
);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $restricted_ips = (int)$row["total"];
}

/* Verified Requests */
$verified_requests = 0;
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM submissions 
     WHERE verification_status = 'Verified'"
);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $verified_requests = (int)$row["total"];
}

/* Suspicious Requests */
$suspicious_requests = 0;
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM submissions 
     WHERE verification_status = 'Suspicious'"
);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $suspicious_requests = (int)$row["total"];
}

/* =========================================
   RECENT THREAT ACTIVITY
========================================= */

$recent_logs = mysqli_query(
    $conn,
    "SELECT event_type, ip_address, severity, action_taken, created_at
     FROM security_logs
     ORDER BY id DESC
     LIMIT 8"
);

/* =========================================
   SECURITY RATE
========================================= */

$total_requests = $verified_requests + $suspicious_requests + $threats_neutralized;

if ($total_requests > 0) {
    $security_rate = round(
        ($verified_requests / $total_requests) * 100
    );
} else {
    $security_rate = 0;
}

/* =========================================
   ADMIN DETAILS
========================================= */

$admin_name = $_SESSION["admin_name"] ?? "Security Administrator";
$admin_role = $_SESSION["admin_role"] ?? "Security Administrator";
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Anti-Bot Firewall | Security Operations Center</title>

<style>

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
   ANIMATED BACKGROUND
========================================= */

body::before{
    content:"";
    position:fixed;
    inset:0;
    background:
        linear-gradient(rgba(0,220,255,0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,220,255,0.035) 1px, transparent 1px);
    background-size:45px 45px;
    animation:gridMove 18s linear infinite;
    z-index:-3;
}

body::after{
    content:"";
    position:fixed;
    width:700px;
    height:700px;
    background:radial-gradient(
        circle,
        rgba(0,210,255,0.12),
        transparent 65%
    );
    top:-250px;
    right:-200px;
    z-index:-2;
    animation:glowMove 7s ease-in-out infinite alternate;
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
    background:rgba(3,12,21,0.94);
    border-right:1px solid rgba(0,220,255,0.20);
    backdrop-filter:blur(18px);
    padding:28px 18px;
    z-index:20;
}

.logo{
    text-align:center;
    padding-bottom:25px;
    border-bottom:1px solid rgba(0,220,255,0.12);
}

.logo-icon{
    width:58px;
    height:58px;
    margin:auto;
    border:2px solid #00d9ff;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:27px;
    color:#00d9ff;
    box-shadow:
        0 0 15px rgba(0,217,255,0.5),
        inset 0 0 15px rgba(0,217,255,0.1);
}

.logo h2{
    margin-top:13px;
    font-size:16px;
    letter-spacing:2px;
    color:#ffffff;
}

.logo p{
    margin-top:5px;
    font-size:9px;
    letter-spacing:2px;
    color:#00d9ff;
}

.nav{
    margin-top:30px;
}

.nav-title{
    font-size:9px;
    letter-spacing:2px;
    color:#607887;
    margin:0 12px 12px;
}

.nav a{
    display:block;
    text-decoration:none;
    color:#89a3b0;
    padding:13px 14px;
    margin-bottom:7px;
    border-radius:8px;
    font-size:12px;
    letter-spacing:.5px;
    transition:.25s;
}

.nav a:hover,
.nav a.active{
    color:#ffffff;
    background:rgba(0,217,255,0.09);
    border:1px solid rgba(0,217,255,0.18);
    box-shadow:0 0 15px rgba(0,217,255,0.07);
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
    padding:28px 32px 100px;
}

/* =========================================
   TOP BAR
========================================= */

.topbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:30px;
}

.top-title h1{
    font-size:25px;
    letter-spacing:2px;
    font-weight:700;
}

.top-title p{
    margin-top:7px;
    color:#6d8998;
    font-size:10px;
    letter-spacing:2px;
}

.status{
    display:flex;
    align-items:center;
    gap:9px;
    border:1px solid rgba(0,255,170,0.25);
    background:rgba(0,255,170,0.04);
    padding:10px 15px;
    border-radius:25px;
    font-size:10px;
    letter-spacing:1.5px;
    color:#61ffc7;
}

.status-dot{
    width:8px;
    height:8px;
    border-radius:50%;
    background:#00ffa6;
    box-shadow:0 0 12px #00ffa6;
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
    background:rgba(7,19,30,.72);
    border:1px solid rgba(0,217,255,.12);
    border-radius:12px;
    padding:13px 17px;
    margin-bottom:25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.admin-info{
    font-size:11px;
    color:#78919e;
}

.admin-info strong{
    color:#ffffff;
}

.admin-role{
    color:#00d9ff;
    font-size:10px;
    letter-spacing:1px;
}

.logout{
    text-decoration:none;
    color:#ff7891;
    border:1px solid rgba(255,80,110,.25);
    padding:8px 13px;
    border-radius:7px;
    font-size:10px;
    letter-spacing:1px;
    transition:.2s;
}

.logout:hover{
    background:rgba(255,80,110,.08);
}

/* =========================================
   STAT CARDS
========================================= */

.stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-bottom:25px;
}

.card{
    position:relative;
    overflow:hidden;
    background:rgba(7,18,29,.78);
    border:1px solid rgba(0,217,255,.13);
    border-radius:14px;
    padding:20px;
    transition:.3s;
}

.card:hover{
    transform:translateY(-4px);
    border-color:rgba(0,217,255,.38);
    box-shadow:0 10px 35px rgba(0,217,255,.08);
}

.card::after{
    content:"";
    position:absolute;
    width:100px;
    height:100px;
    background:rgba(0,217,255,.07);
    border-radius:50%;
    right:-50px;
    top:-50px;
}

.card-label{
    color:#718b99;
    font-size:9px;
    letter-spacing:1.5px;
}

.card-number{
    font-size:32px;
    font-weight:700;
    margin-top:13px;
    color:#ffffff;
}

.card-sub{
    margin-top:9px;
    color:#00d9ff;
    font-size:9px;
    letter-spacing:1px;
}

/* =========================================
   CONTENT GRID
========================================= */

.content-grid{
    display:grid;
    grid-template-columns:1.6fr 1fr;
    gap:20px;
}

.panel{
    background:rgba(7,18,29,.78);
    border:1px solid rgba(0,217,255,.13);
    border-radius:14px;
    overflow:hidden;
}

.panel-header{
    padding:18px 20px;
    border-bottom:1px solid rgba(0,217,255,.10);
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.panel-header h3{
    font-size:12px;
    letter-spacing:1.5px;
}

.panel-header span{
    color:#00d9ff;
    font-size:9px;
    letter-spacing:1px;
}

/* =========================================
   TABLE
========================================= */

.table-wrap{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    text-align:left;
    padding:13px 15px;
    color:#536e7d;
    font-size:8px;
    letter-spacing:1.3px;
    font-weight:normal;
}

td{
    padding:14px 15px;
    border-top:1px solid rgba(0,217,255,.06);
    font-size:10px;
    color:#b8cbd4;
}

td.ip{
    color:#00d9ff;
    font-family:monospace;
}

.badge{
    display:inline-block;
    padding:5px 8px;
    border-radius:5px;
    font-size:8px;
    letter-spacing:1px;
}

.low{
    color:#61ffc7;
    background:rgba(0,255,170,.07);
    border:1px solid rgba(0,255,170,.15);
}

.medium{
    color:#ffd86b;
    background:rgba(255,200,60,.07);
    border:1px solid rgba(255,200,60,.15);
}

.high{
    color:#ff9d68;
    background:rgba(255,120,50,.07);
    border:1px solid rgba(255,120,50,.15);
}

.critical{
    color:#ff6b82;
    background:rgba(255,50,80,.07);
    border:1px solid rgba(255,50,80,.15);
}

/* =========================================
   SECURITY OVERVIEW
========================================= */

.overview{
    padding:20px;
}

.security-meter{
    margin-bottom:25px;
}

.meter-top{
    display:flex;
    justify-content:space-between;
    margin-bottom:9px;
}

.meter-top span{
    font-size:10px;
    color:#8098a5;
}

.meter-value{
    color:#00d9ff !important;
    font-weight:bold;
}

.meter{
    height:8px;
    background:#0a1822;
    border-radius:10px;
    overflow:hidden;
}

.meter-fill{
    height:100%;
    width:<?= min(100, max(0, $security_rate)) ?>%;
    background:linear-gradient(
        90deg,
        #00a8d4,
        #00ffd0
    );
    box-shadow:0 0 12px rgba(0,255,210,.45);
    border-radius:10px;
}

.overview-item{
    display:flex;
    justify-content:space-between;
    padding:13px 0;
    border-bottom:1px solid rgba(0,217,255,.07);
}

.overview-item:last-child{
    border-bottom:none;
}

.overview-item span:first-child{
    color:#718b99;
    font-size:10px;
}

.overview-item span:last-child{
    color:#ffffff;
    font-size:11px;
    font-weight:bold;
}

/* =========================================
   QUICK ACTIONS
========================================= */

.actions{
    margin-top:20px;
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:10px;
}

.action{
    text-decoration:none;
    padding:13px;
    border:1px solid rgba(0,217,255,.12);
    background:rgba(0,217,255,.025);
    border-radius:8px;
    color:#9db6c1;
    font-size:9px;
    letter-spacing:1px;
    text-align:center;
    transition:.25s;
}

.action:hover{
    color:#00d9ff;
    border-color:rgba(0,217,255,.35);
    background:rgba(0,217,255,.06);
}

/* =========================================
   BOTTOM BACK BUTTON
========================================= */

.back-btn{
    position:fixed;
    right:25px;
    bottom:22px;
    text-decoration:none;
    color:#00d9ff;
    border:1px solid rgba(0,217,255,.25);
    background:rgba(3,12,21,.9);
    padding:11px 17px;
    border-radius:25px;
    font-size:10px;
    letter-spacing:1px;
    backdrop-filter:blur(12px);
    z-index:50;
    transition:.25s;
}

.back-btn:hover{
    background:rgba(0,217,255,.08);
    box-shadow:0 0 18px rgba(0,217,255,.18);
}

/* =========================================
   RESPONSIVE
========================================= */

@media(max-width:1100px){

    .stats{
        grid-template-columns:repeat(2,1fr);
    }

    .content-grid{
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

    .topbar{
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }

    .stats{
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

        <a href="dashboard.php" class="active">
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

        <div class="nav-title" style="margin-top:25px;">
            SYSTEM INTELLIGENCE
        </div>

        <a href="request_audit.php">
            <span>▣</span>
            Request Audit Trail
        </a>

        <a href="security_reports.php">
            <span>◫</span>
            Security Intelligence
        </a>

        <a href="security_settings.php">
            <span>⚙</span>
            Security Configuration
        </a>

        <div class="nav-title" style="margin-top:25px;">
            PROTECTION TESTING
        </div>

        <a href="protected_form.php">
            <span>🛡</span>
            Protected Web Form
        </a>

    </div>

</aside>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<main class="main">

    <!-- TOP BAR -->

    <div class="topbar">

        <div class="top-title">

            <h1>SECURITY OPERATIONS CENTER</h1>

            <p>
                REAL-TIME WEB FORM THREAT MONITORING & BOT DEFENSE
            </p>

        </div>

        <div class="status">

            <div class="status-dot"></div>

            FIREWALL STATUS: ACTIVE

        </div>

    </div>


    <!-- ADMIN INFORMATION -->

    <div class="admin-bar">

        <div class="admin-info">

            AUTHENTICATED SECURITY ADMINISTRATOR :
            <strong>
                <?= htmlspecialchars($admin_name) ?>
            </strong>

            &nbsp; | &nbsp;

            <span class="admin-role">
                <?= htmlspecialchars($admin_role) ?>
            </span>

        </div>

        <a href="logout.php" class="logout">
            TERMINATE SECURE SESSION
        </a>

    </div>


    <!-- =========================================
         REAL SECURITY STATISTICS
    ========================================== -->

    <div class="stats">

        <div class="card">

            <div class="card-label">
                TOTAL SECURITY EVENTS
            </div>

            <div class="card-number">
                <?= $total_events ?>
            </div>

            <div class="card-sub">
                EVENT AUDIT RECORDS
            </div>

        </div>


        <div class="card">

            <div class="card-label">
                THREAT DETECTIONS
            </div>

            <div class="card-number">
                <?= $threat_detections ?>
            </div>

            <div class="card-sub">
                HIGH / CRITICAL EVENTS
            </div>

        </div>


        <div class="card">

            <div class="card-label">
                THREATS NEUTRALIZED
            </div>

            <div class="card-number">
                <?= $threats_neutralized ?>
            </div>

            <div class="card-sub">
                BLOCKED FORM REQUESTS
            </div>

        </div>


        <div class="card">

            <div class="card-label">
                RESTRICTED IP SOURCES
            </div>

            <div class="card-number">
                <?= $restricted_ips ?>
            </div>

            <div class="card-sub">
                ACTIVE IP RESTRICTIONS
            </div>

        </div>

    </div>


    <!-- =========================================
         MAIN CONTENT GRID
    ========================================== -->

    <div class="content-grid">


        <!-- RECENT THREAT ACTIVITY -->

        <section class="panel">

            <div class="panel-header">

                <h3>
                    RECENT THREAT ACTIVITY
                </h3>

                <span>
                    LIVE SECURITY FEED
                </span>

            </div>

            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>

                            <th>
                                SECURITY EVENT
                            </th>

                            <th>
                                SOURCE
                            </th>

                            <th>
                                SEVERITY
                            </th>

                            <th>
                                ACTION
                            </th>

                            <th>
                                TIME
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($recent_logs && mysqli_num_rows($recent_logs) > 0): ?>

                        <?php while ($log = mysqli_fetch_assoc($recent_logs)): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($log["event_type"]) ?>
                                </td>

                                <td class="ip">
                                    <?= htmlspecialchars($log["ip_address"]) ?>
                                </td>

                                <td>

                                    <?php
                                    $severity = strtoupper($log["severity"]);
                                    $severity_class = strtolower($severity);
                                    ?>

                                    <span class="badge <?= $severity_class ?>">
                                        <?= htmlspecialchars($severity) ?>
                                    </span>

                                </td>

                                <td>
                                    <?= htmlspecialchars($log["action_taken"] ?? "-") ?>
                                </td>

                                <td>
                                    <?= date(
                                        "d M Y H:i",
                                        strtotime($log["created_at"])
                                    ) ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="5" style="text-align:center;padding:30px;">
                                NO SECURITY EVENTS DETECTED
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>


        <!-- SECURITY OVERVIEW -->

        <section class="panel">

            <div class="panel-header">

                <h3>
                    SECURITY OVERVIEW
                </h3>

                <span>
                    LIVE ANALYTICS
                </span>

            </div>

            <div class="overview">


                <!-- SECURITY RATE -->

                <div class="security-meter">

                    <div class="meter-top">

                        <span>
                            VERIFIED REQUEST RATE
                        </span>

                        <span class="meter-value">
                            <?= $security_rate ?>%
                        </span>

                    </div>

                    <div class="meter">

                        <div class="meter-fill"></div>

                    </div>

                </div>


                <!-- VERIFIED -->

                <div class="overview-item">

                    <span>
                        VERIFIED REQUESTS
                    </span>

                    <span>
                        <?= $verified_requests ?>
                    </span>

                </div>


                <!-- SUSPICIOUS -->

                <div class="overview-item">

                    <span>
                        ANOMALOUS REQUESTS
                    </span>

                    <span>
                        <?= $suspicious_requests ?>
                    </span>

                </div>


                <!-- BLOCKED -->

                <div class="overview-item">

                    <span>
                        BLOCKED REQUESTS
                    </span>

                    <span>
                        <?= $threats_neutralized ?>
                    </span>

                </div>


                <!-- RESTRICTED IPS -->

                <div class="overview-item">

                    <span>
                        ACTIVE IP RESTRICTIONS
                    </span>

                    <span>
                        <?= $restricted_ips ?>
                    </span>

                </div>


                <!-- QUICK ACTIONS -->

                <div class="actions">

                    <a href="security_logs.php" class="action">
                        VIEW SECURITY LOGS
                    </a>

                    <a href="request_audit.php" class="action">
                        REQUEST AUDIT
                    </a>

                    <a href="blocked_ips.php" class="action">
                        IP THREAT CONTROL
                    </a>

                    <a href="security_reports.php" class="action">
                        SECURITY REPORTS
                    </a>

                </div>

            </div>

        </section>

    </div>

</main>


<!-- BACK BUTTON -->

<a href="index.php" class="back-btn">
    ← SECURE GATEWAY
</a>

</body>
</html>