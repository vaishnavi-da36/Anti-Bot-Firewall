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
   THREAT ACTIVITY STATISTICS
========================================= */

/* Total Events */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM security_logs"
);
$total_events = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;

/* High + Critical */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM security_logs
     WHERE severity IN ('HIGH','CRITICAL')"
);
$high_threats = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;

/* Blocked / Neutralized */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM security_logs
     WHERE action_taken LIKE '%Block%'
        OR action_taken LIKE '%Neutralized%'
        OR action_taken LIKE '%Restricted%'"
);
$neutralized = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;

/* Low / Legitimate */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM security_logs
     WHERE severity = 'LOW'"
);
$legitimate_activity = $result ? (int)mysqli_fetch_assoc($result)["total"] : 0;


/* =========================================
   THREAT DISTRIBUTION
========================================= */

$result = mysqli_query(
    $conn,
    "SELECT severity, COUNT(*) AS total
     FROM security_logs
     GROUP BY severity"
);

$severity_data = [
    "LOW" => 0,
    "MEDIUM" => 0,
    "HIGH" => 0,
    "CRITICAL" => 0
];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $severity = strtoupper($row["severity"]);

        if (isset($severity_data[$severity])) {
            $severity_data[$severity] = (int)$row["total"];
        }
    }
}


/* =========================================
   RECENT THREAT ACTIVITY
========================================= */

$activities = mysqli_query(
    $conn,
    "SELECT
        id,
        event_type,
        ip_address,
        severity,
        action_taken,
        user_agent,
        created_at
     FROM security_logs
     ORDER BY id DESC
     LIMIT 100"
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
    Anti-Bot Firewall | Threat Activity
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

    width:650px;
    height:650px;

    left:-250px;
    bottom:-250px;

    background:
        radial-gradient(
            circle,
            rgba(0,217,255,.12),
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
   STATISTICS
========================================= */

.stats{

    display:grid;

    grid-template-columns:
        repeat(4,1fr);

    gap:16px;

    margin-bottom:22px;
}

.stat-card{

    background:
        rgba(7,18,29,.82);

    border:
        1px solid rgba(0,217,255,.13);

    border-radius:14px;

    padding:20px;

    position:relative;

    overflow:hidden;

    transition:.3s;
}

.stat-card:hover{

    transform:translateY(-3px);

    border-color:
        rgba(0,217,255,.35);

    box-shadow:
        0 12px 35px
        rgba(0,217,255,.08);
}

.stat-card::after{

    content:"";

    position:absolute;

    width:120px;
    height:120px;

    right:-55px;
    top:-55px;

    border-radius:50%;

    background:
        rgba(0,217,255,.06);
}

.stat-label{

    color:#6d8998;

    font-size:9px;

    letter-spacing:1.5px;
}

.stat-number{

    color:#fff;

    font-size:32px;

    font-weight:bold;

    margin-top:12px;
}

.stat-sub{

    color:#00d9ff;

    font-size:9px;

    letter-spacing:1px;

    margin-top:7px;
}


/* =========================================
   THREAT DISTRIBUTION
========================================= */

.distribution{

    display:grid;

    grid-template-columns:
        repeat(4,1fr);

    gap:12px;

    margin-bottom:22px;
}

.dist-card{

    padding:15px;

    border-radius:10px;

    background:
        rgba(5,15,24,.75);

    border:
        1px solid rgba(0,217,255,.09);
}

.dist-top{

    display:flex;

    justify-content:space-between;

    align-items:center;
}

.dist-name{

    font-size:9px;

    letter-spacing:1.3px;

    color:#78929f;
}

.dist-number{

    font-size:18px;

    font-weight:bold;

    color:#fff;
}

.dist-bar{

    margin-top:11px;

    width:100%;

    height:4px;

    border-radius:10px;

    background:#0a1720;

    overflow:hidden;
}

.dist-fill{

    height:100%;

    border-radius:10px;
}

.low{
    background:#00d9a0;
}

.medium{
    background:#ffd04d;
}

.high{
    background:#ff6b55;
}

.critical{
    background:#ff315d;
}


/* =========================================
   ACTIVITY PANEL
========================================= */

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

    padding:19px 20px;

    border-bottom:
        1px solid rgba(0,217,255,.10);
}

.panel-header h2{

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

    width:100%;

    overflow-x:auto;
}

table{

    width:100%;

    border-collapse:collapse;

    min-width:1050px;
}

th{

    text-align:left;

    padding:14px 17px;

    color:#526d7b;

    font-size:8px;

    letter-spacing:1.4px;

    font-weight:normal;
}

td{

    padding:15px 17px;

    border-top:
        1px solid rgba(0,217,255,.06);

    font-size:10px;

    color:#a9c0ca;
}

tr{

    transition:.2s;
}

tr:hover{

    background:
        rgba(0,217,255,.025);
}

.record{

    color:#637e8c;

    font-family:monospace;
}

.event{

    color:#d8e7ec;

    font-weight:bold;

    letter-spacing:.4px;
}

.ip{

    color:#00d9ff;

    font-family:monospace;

    font-size:11px;
}

.action{

    color:#a9c0ca;
}

.agent{

    max-width:200px;

    overflow:hidden;

    white-space:nowrap;

    text-overflow:ellipsis;

    color:#607986;

    font-size:9px;
}

.time{

    color:#6c8794;

    font-size:9px;
}


/* =========================================
   SEVERITY BADGES
========================================= */

.severity{

    display:inline-flex;

    align-items:center;

    gap:6px;

    padding:6px 9px;

    border-radius:6px;

    font-size:8px;

    letter-spacing:1px;
}

.severity-dot{

    width:6px;
    height:6px;

    border-radius:50%;
}

.severity-low{

    color:#61ffc7;

    background:
        rgba(0,255,170,.06);

    border:
        1px solid rgba(0,255,170,.18);
}

.severity-low .severity-dot{

    background:#00ffa6;

    box-shadow:
        0 0 8px #00ffa6;
}

.severity-medium{

    color:#ffd36a;

    background:
        rgba(255,200,60,.06);

    border:
        1px solid rgba(255,200,60,.18);
}

.severity-medium .severity-dot{

    background:#ffd04d;

    box-shadow:
        0 0 8px #ffd04d;
}

.severity-high{

    color:#ff8b72;

    background:
        rgba(255,90,60,.06);

    border:
        1px solid rgba(255,90,60,.18);
}

.severity-high .severity-dot{

    background:#ff674d;

    box-shadow:
        0 0 8px #ff674d;
}

.severity-critical{

    color:#ff6680;

    background:
        rgba(255,40,80,.08);

    border:
        1px solid rgba(255,40,80,.22);
}

.severity-critical .severity-dot{

    background:#ff315d;

    box-shadow:
        0 0 10px #ff315d;
}


/* =========================================
   EMPTY STATE
========================================= */

.empty{

    text-align:center;

    padding:60px 20px;
}

.empty-icon{

    font-size:38px;

    color:#00d9ff;

    margin-bottom:15px;
}

.empty h3{

    font-size:12px;

    letter-spacing:1.5px;
}

.empty p{

    margin-top:8px;

    color:#607987;

    font-size:10px;
}


/* =========================================
   SECURITY NOTE
========================================= */

.security-note{

    margin-top:18px;

    padding:17px 20px;

    border-radius:11px;

    border:
        1px solid rgba(0,217,255,.12);

    background:
        rgba(0,217,255,.025);

    color:#738b97;

    font-size:9px;

    line-height:1.7;
}

.security-note strong{

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

@media(max-width:1050px){

    .stats{

        grid-template-columns:
            repeat(2,1fr);
    }

    .distribution{

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

    .stats{

        grid-template-columns:1fr;
    }

    .distribution{

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


        <a
            href="threat_activity.php"
            class="active"
        >

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


        <a href="#">

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
                THREAT ACTIVITY
            </h1>

            <p>
                REAL-TIME SECURITY EVENT ANALYSIS & THREAT RESPONSE
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
         STATISTICS
    ========================================== -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-label">
                TOTAL SECURITY EVENTS
            </div>

            <div class="stat-number">
                <?= $total_events ?>
            </div>

            <div class="stat-sub">
                RECORDED ACTIVITY
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                HIGH PRIORITY THREATS
            </div>

            <div class="stat-number">
                <?= $high_threats ?>
            </div>

            <div class="stat-sub">
                HIGH + CRITICAL EVENTS
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                THREATS NEUTRALIZED
            </div>

            <div class="stat-number">
                <?= $neutralized ?>
            </div>

            <div class="stat-sub">
                BLOCKED / RESTRICTED EVENTS
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                LEGITIMATE ACTIVITY
            </div>

            <div class="stat-number">
                <?= $legitimate_activity ?>
            </div>

            <div class="stat-sub">
                LOW SEVERITY EVENTS
            </div>

        </div>

    </div>


    <!-- =========================================
         SEVERITY DISTRIBUTION
    ========================================== -->

    <div class="distribution">


        <div class="dist-card">

            <div class="dist-top">

                <div class="dist-name">
                    LOW
                </div>

                <div class="dist-number">
                    <?= $severity_data["LOW"] ?>
                </div>

            </div>

            <div class="dist-bar">

                <div
                    class="dist-fill low"
                    style="width:<?= $total_events > 0
                        ? ($severity_data["LOW"] / $total_events) * 100
                        : 0 ?>%;"
                ></div>

            </div>

        </div>


        <div class="dist-card">

            <div class="dist-top">

                <div class="dist-name">
                    MEDIUM
                </div>

                <div class="dist-number">
                    <?= $severity_data["MEDIUM"] ?>
                </div>

            </div>

            <div class="dist-bar">

                <div
                    class="dist-fill medium"
                    style="width:<?= $total_events > 0
                        ? ($severity_data["MEDIUM"] / $total_events) * 100
                        : 0 ?>%;"
                ></div>

            </div>

        </div>


        <div class="dist-card">

            <div class="dist-top">

                <div class="dist-name">
                    HIGH
                </div>

                <div class="dist-number">
                    <?= $severity_data["HIGH"] ?>
                </div>

            </div>

            <div class="dist-bar">

                <div
                    class="dist-fill high"
                    style="width:<?= $total_events > 0
                        ? ($severity_data["HIGH"] / $total_events) * 100
                        : 0 ?>%;"
                ></div>

            </div>

        </div>


        <div class="dist-card">

            <div class="dist-top">

                <div class="dist-name">
                    CRITICAL
                </div>

                <div class="dist-number">
                    <?= $severity_data["CRITICAL"] ?>
                </div>

            </div>

            <div class="dist-bar">

                <div
                    class="dist-fill critical"
                    style="width:<?= $total_events > 0
                        ? ($severity_data["CRITICAL"] / $total_events) * 100
                        : 0 ?>%;"
                ></div>

            </div>

        </div>

    </div>


    <!-- =========================================
         ACTIVITY TABLE
    ========================================== -->

    <section class="panel">


        <div class="panel-header">

            <h2>
                THREAT ACTIVITY STREAM
            </h2>

            <span>
                SECURITY EVENT MONITOR
            </span>

        </div>


        <div class="table-wrap">


        <?php if ($activities && mysqli_num_rows($activities) > 0): ?>


            <table>

                <thead>

                    <tr>

                        <th>
                            RECORD
                        </th>

                        <th>
                            EVENT TYPE
                        </th>

                        <th>
                            SOURCE IP
                        </th>

                        <th>
                            SEVERITY
                        </th>

                        <th>
                            ACTION TAKEN
                        </th>

                        <th>
                            CLIENT SIGNATURE
                        </th>

                        <th>
                            TIMESTAMP
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php while ($activity = mysqli_fetch_assoc($activities)): ?>

                    <?php

                    $severity =
                        strtoupper(
                            $activity["severity"]
                        );

                    $severity_class =
                        strtolower($severity);

                    ?>

                    <tr>


                        <td class="record">

                            #<?= (int)$activity["id"] ?>

                        </td>


                        <td class="event">

                            <?= htmlspecialchars(
                                $activity["event_type"]
                            ) ?>

                        </td>


                        <td class="ip">

                            <?= htmlspecialchars(
                                $activity["ip_address"]
                            ) ?>

                        </td>


                        <td>

                            <span
                                class="severity severity-<?= htmlspecialchars(
                                    $severity_class
                                ) ?>"
                            >

                                <span
                                    class="severity-dot"
                                ></span>

                                <?= htmlspecialchars(
                                    $severity
                                ) ?>

                            </span>

                        </td>


                        <td class="action">

                            <?= htmlspecialchars(
                                $activity["action_taken"]
                                ?: "Security Review"
                            ) ?>

                        </td>


                        <td class="agent">

                            <?= htmlspecialchars(
                                $activity["user_agent"]
                                ?: "Unknown Client"
                            ) ?>

                        </td>


                        <td class="time">

                            <?= date(
                                "d M Y | H:i",
                                strtotime(
                                    $activity["created_at"]
                                )
                            ) ?>

                        </td>


                    </tr>

                <?php endwhile; ?>


                </tbody>

            </table>


        <?php else: ?>


            <div class="empty">

                <div class="empty-icon">
                    ⚠
                </div>

                <h3>
                    NO THREAT ACTIVITY DETECTED
                </h3>

                <p>
                    Security event records will appear here
                    when firewall activity is recorded.
                </p>

            </div>


        <?php endif; ?>


        </div>

    </section>


    <!-- SECURITY NOTE -->

    <div class="security-note">

        <strong>
            THREAT ANALYSIS:
        </strong>

        This security activity stream consolidates firewall
        events into a centralized threat-monitoring view.
        Severity levels indicate the security priority of
        each event, while the recorded action identifies
        the mitigation response applied by the firewall.

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