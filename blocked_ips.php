<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: index.php");
    exit();
}

require_once "config/db.php";

/* =========================================
   IP THREAT CONTROL DATA
========================================= */

$active_count = 0;
$released_count = 0;
$total_count = 0;

/* Active Restricted IPs */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM blocked_ips
     WHERE status = 'Active'"
);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $active_count = (int)$row["total"];
}

/* Released IPs */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM blocked_ips
     WHERE status = 'Released'"
);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $released_count = (int)$row["total"];
}

/* Total IP Records */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM blocked_ips"
);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_count = (int)$row["total"];
}

/* IP Threat Records */
$blocked_ips = mysqli_query(
    $conn,
    "SELECT
        id,
        ip_address,
        reason,
        status,
        blocked_at
     FROM blocked_ips
     ORDER BY id DESC"
);

$admin_name = $_SESSION["admin_name"] ?? "Security Administrator";
$admin_role = $_SESSION["admin_role"] ?? "Security Administrator";
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
    Anti-Bot Firewall | IP Threat Control
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
   ANIMATED GRID
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

    background:
        rgba(3,12,21,.95);

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
        repeat(3,1fr);

    gap:16px;

    margin-bottom:22px;
}

.stat-card{

    background:
        rgba(7,18,29,.80);

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
   MAIN PANEL
========================================= */

.panel{

    background:
        rgba(7,18,29,.80);

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

    min-width:800px;
}

th{

    text-align:left;

    padding:14px 17px;

    color:#526d7b;

    font-size:8px;

    letter-spacing:1.4px;

    font-weight:normal;

    background:
        rgba(0,217,255,.015);
}

td{

    padding:16px 17px;

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

.ip{

    color:#00d9ff;

    font-family:monospace;

    font-size:11px;
}

.reason{

    color:#b5c9d2;

}

.time{

    color:#6d8794;

    font-size:9px;
}

/* =========================================
   STATUS BADGES
========================================= */

.badge{

    display:inline-flex;

    align-items:center;

    gap:6px;

    padding:6px 9px;

    border-radius:6px;

    font-size:8px;

    letter-spacing:1px;
}

.active{

    color:#ff7188;

    background:
        rgba(255,70,100,.06);

    border:
        1px solid rgba(255,70,100,.18);
}

.released{

    color:#61ffc7;

    background:
        rgba(0,255,170,.06);

    border:
        1px solid rgba(0,255,170,.18);
}

.active-dot{

    width:6px;
    height:6px;

    border-radius:50%;

    background:#ff526f;

    box-shadow:
        0 0 8px #ff526f;
}

.released-dot{

    width:6px;
    height:6px;

    border-radius:50%;

    background:#00ffa6;

    box-shadow:
        0 0 8px #00ffa6;
}

/* =========================================
   EMPTY STATE
========================================= */

.empty{

    text-align:center;

    padding:55px 20px;
}

.empty-icon{

    font-size:35px;

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
        1px solid rgba(255,190,70,.13);

    background:
        rgba(255,190,70,.025);

    color:#8d9294;

    font-size:9px;

    line-height:1.7;
}

.security-note strong{

    color:#ffd36a;

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

@media(max-width:950px){

    .stats{

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


        <a href="#">

            <span>⚠</span>

            Threat Activity

        </a>


        <a
            href="blocked_ips.php"
            class="active"
        >

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
                IP THREAT CONTROL
            </h1>

            <p>
                RESTRICTED SOURCE MONITORING & ACCESS CONTROL
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
                ACTIVE RESTRICTED SOURCES
            </div>

            <div class="stat-number">
                <?= $active_count ?>
            </div>

            <div class="stat-sub">
                CURRENT IP RESTRICTIONS
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                RELEASED SOURCES
            </div>

            <div class="stat-number">
                <?= $released_count ?>
            </div>

            <div class="stat-sub">
                PREVIOUSLY RESTRICTED
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-label">
                TOTAL IP RECORDS
            </div>

            <div class="stat-number">
                <?= $total_count ?>
            </div>

            <div class="stat-sub">
                SECURITY AUDIT RECORDS
            </div>

        </div>

    </div>


    <!-- =========================================
         IP THREAT TABLE
    ========================================== -->

    <section class="panel">


        <div class="panel-header">

            <h2>
                RESTRICTED IP SOURCES
            </h2>

            <span>
                IP THREAT DATABASE
            </span>

        </div>


        <div class="table-wrap">

        <?php if ($blocked_ips && mysqli_num_rows($blocked_ips) > 0): ?>


            <table>

                <thead>

                    <tr>

                        <th>
                            RECORD ID
                        </th>

                        <th>
                            IP SOURCE
                        </th>

                        <th>
                            THREAT REASON
                        </th>

                        <th>
                            SECURITY STATUS
                        </th>

                        <th>
                            RESTRICTION TIME
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php while ($ip = mysqli_fetch_assoc($blocked_ips)): ?>

                    <tr>


                        <td>
                            #<?= (int)$ip["id"] ?>
                        </td>


                        <td class="ip">

                            <?= htmlspecialchars(
                                $ip["ip_address"]
                            ) ?>

                        </td>


                        <td class="reason">

                            <?= htmlspecialchars(
                                $ip["reason"] ?? "-"
                            ) ?>

                        </td>


                        <td>

                            <?php
                            $status =
                                strtoupper(
                                    $ip["status"]
                                );

                            if ($status === "ACTIVE"):
                            ?>

                                <span class="badge active">

                                    <span class="active-dot"></span>

                                    RESTRICTED

                                </span>

                            <?php else: ?>

                                <span class="badge released">

                                    <span class="released-dot"></span>

                                    RELEASED

                                </span>

                            <?php endif; ?>

                        </td>


                        <td class="time">

                            <?= date(
                                "d M Y | H:i",
                                strtotime(
                                    $ip["blocked_at"]
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
                    🛡
                </div>

                <h3>
                    NO RESTRICTED IP SOURCES
                </h3>

                <p>
                    The firewall has not recorded any restricted
                    IP source in the security database.
                </p>

            </div>


        <?php endif; ?>

        </div>

    </section>


    <!-- SECURITY NOTE -->

    <div class="security-note">

        <strong>
            SECURITY CONTROL NOTICE:
        </strong>

        Restricted IP sources represent addresses identified
        by the firewall as suspicious, automated, abusive or
        otherwise requiring access control. Active restrictions
        remain under security monitoring until their status is
        changed by an authorized security administrator.

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