<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . "/config/db.php";

$logs = [];

$query = "
    SELECT id, event_type, ip_address, severity, action_taken, user_agent, created_at
    FROM security_logs
    ORDER BY id DESC
";

$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Anti-Bot Firewall | Security Event Logs</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial,Helvetica,sans-serif;
}

body{
    min-height:100vh;
    color:#eaf7ff;
    background:
        radial-gradient(circle at 15% 15%,rgba(0,217,255,.08),transparent 28%),
        radial-gradient(circle at 85% 85%,rgba(0,100,255,.08),transparent 28%),
        #040a12;
    overflow-x:hidden;
}

body::before{
    content:"";
    position:fixed;
    inset:0;
    background-image:
        linear-gradient(rgba(0,217,255,.025) 1px,transparent 1px),
        linear-gradient(90deg,rgba(0,217,255,.025) 1px,transparent 1px);
    background-size:45px 45px;
    animation:gridMove 18s linear infinite;
    pointer-events:none;
    z-index:-1;
}

@keyframes gridMove{
    from{transform:translate(0,0);}
    to{transform:translate(45px,45px);}
}

.sidebar{
    position:fixed;
    left:0;
    top:0;
    bottom:0;
    width:245px;
    padding:28px 18px;
    background:rgba(4,12,22,.94);
    border-right:1px solid rgba(0,217,255,.13);
    backdrop-filter:blur(18px);
    z-index:10;
}

.logo{
    text-align:center;
    font-size:19px;
    font-weight:700;
    letter-spacing:2px;
}

.logo span{
    color:#00d9ff;
}

.logo-sub{
    text-align:center;
    color:#587789;
    font-size:9px;
    letter-spacing:1.5px;
    margin-top:7px;
    margin-bottom:35px;
}

.nav-title{
    color:#456474;
    font-size:9px;
    letter-spacing:2px;
    margin:20px 10px 10px;
}

.nav{
    display:block;
    padding:13px 14px;
    margin:5px 0;
    border-radius:10px;
    color:#7895a5;
    text-decoration:none;
    font-size:12px;
    transition:.3s;
}

.nav:hover,
.nav.active{
    color:#00d9ff;
    background:rgba(0,217,255,.07);
    border:1px solid rgba(0,217,255,.13);
}

.sidebar-bottom{
    position:absolute;
    left:18px;
    right:18px;
    bottom:25px;
}

.logout{
    display:block;
    text-align:center;
    padding:12px;
    border-radius:10px;
    color:#ff6d82;
    text-decoration:none;
    border:1px solid rgba(255,70,100,.15);
    background:rgba(255,70,100,.04);
    font-size:11px;
}

.main{
    margin-left:245px;
    padding:30px;
}

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.title{
    font-size:24px;
    font-weight:700;
    letter-spacing:1px;
}

.subtitle{
    margin-top:7px;
    color:#607b8b;
    font-size:10px;
    letter-spacing:1px;
}

.status{
    padding:11px 17px;
    border-radius:12px;
    border:1px solid rgba(0,255,170,.18);
    background:rgba(0,255,170,.04);
    color:#53f5bc;
    font-size:10px;
    letter-spacing:1px;
}

.panel{
    background:rgba(7,18,30,.80);
    border:1px solid rgba(0,217,255,.11);
    border-radius:18px;
    padding:22px;
    overflow:hidden;
}

.panel-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.panel-title{
    font-size:12px;
    letter-spacing:1.5px;
}

.count{
    color:#00d9ff;
    font-size:10px;
    padding:7px 10px;
    border-radius:8px;
    background:rgba(0,217,255,.06);
}

.table-wrap{
    width:100%;
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:900px;
}

th{
    text-align:left;
    color:#557080;
    font-size:9px;
    letter-spacing:1.3px;
    padding:13px 12px;
    border-bottom:1px solid rgba(255,255,255,.06);
}

td{
    padding:15px 12px;
    border-bottom:1px solid rgba(255,255,255,.045);
    font-size:10px;
    color:#a4b8c4;
}

tr:hover{
    background:rgba(0,217,255,.025);
}

.event-name{
    color:#e5f5fc;
    font-weight:600;
}

.ip{
    color:#00d9ff;
    font-family:monospace;
}

.badge{
    display:inline-block;
    padding:6px 9px;
    border-radius:7px;
    font-size:8px;
    letter-spacing:1px;
}

.high{
    color:#ff7185;
    background:rgba(255,70,100,.08);
    border:1px solid rgba(255,70,100,.14);
}

.medium{
    color:#ffc56b;
    background:rgba(255,180,50,.07);
    border:1px solid rgba(255,180,50,.13);
}

.low{
    color:#53f5bc;
    background:rgba(0,255,170,.06);
    border:1px solid rgba(0,255,170,.13);
}

.action{
    color:#7c98a7;
}

.empty{
    padding:50px;
    text-align:center;
    color:#536f7e;
    font-size:11px;
}

.back{
    display:inline-block;
    margin-top:20px;
    padding:11px 17px;
    border-radius:10px;
    text-decoration:none;
    color:#00d9ff;
    border:1px solid rgba(0,217,255,.15);
    background:rgba(0,217,255,.04);
    font-size:10px;
    letter-spacing:1px;
}

@media(max-width:700px){

    .sidebar{
        position:relative;
        width:100%;
        height:auto;
        border-right:0;
        border-bottom:1px solid rgba(0,217,255,.13);
    }

    .sidebar-bottom{
        position:relative;
        left:auto;
        right:auto;
        bottom:auto;
        margin-top:20px;
    }

    .main{
        margin-left:0;
        padding:18px;
    }

    .topbar{
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }
}

</style>

</head>

<body>

<aside class="sidebar">

    <div class="logo">
        ANTI-BOT <span>FIREWALL</span>
    </div>

    <div class="logo-sub">
        SECURITY DEFENSE PLATFORM
    </div>

    <div class="nav-title">
        SECURITY CONTROL
    </div>

    <a class="nav" href="dashboard.php">
        🛡️ Security Operations Center
    </a>

    <a class="nav" href="#">
        📡 Threat Activity
    </a>

    <a class="nav active" href="security_logs.php">
        📋 Security Event Logs
    </a>

    <div class="nav-title">
        DEFENSE MANAGEMENT
    </div>

    <a class="nav" href="#">
        🚫 IP Threat Control
    </a>

    <a class="nav" href="#">
        ⚡ Request Throttling
    </a>

    <a class="nav" href="#">
        🪤 Bot Trap Layer
    </a>

    <div class="nav-title">
        INTELLIGENCE
    </div>

    <a class="nav" href="#">
        📊 Security Intelligence
    </a>

    <a class="nav" href="#">
        ⚙️ Security Configuration
    </a>

    <div class="sidebar-bottom">

        <a class="logout" href="logout.php">
            TERMINATE SECURE SESSION
        </a>

    </div>

</aside>

<main class="main">

    <div class="topbar">

        <div>
            <div class="title">
                SECURITY EVENT LOGS
            </div>

            <div class="subtitle">
                CENTRALIZED SECURITY EVENT MONITORING & AUDIT TRAIL
            </div>
        </div>

        <div class="status">
            ● LOGGING ENGINE : ACTIVE
        </div>

    </div>

    <section class="panel">

        <div class="panel-header">

            <div class="panel-title">
                SECURITY EVENT MONITOR
            </div>

            <div class="count">
                <?= count($logs) ?> EVENTS
            </div>

        </div>

        <?php if (count($logs) > 0): ?>

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>
                        <th>EVENT</th>
                        <th>IP SOURCE</th>
                        <th>SEVERITY</th>
                        <th>ACTION TAKEN</th>
                        <th>USER AGENT</th>
                        <th>TIMESTAMP</th>
                    </tr>

                </thead>

                <tbody>

                <?php foreach ($logs as $log): ?>

                    <tr>

                        <td class="event-name">
                            <?= htmlspecialchars($log["event_type"]) ?>
                        </td>

                        <td class="ip">
                            <?= htmlspecialchars($log["ip_address"]) ?>
                        </td>

                        <td>

                            <?php
                            $severity = strtoupper($log["severity"]);

                            $class = "low";

                            if ($severity === "HIGH" || $severity === "CRITICAL") {
                                $class = "high";
                            } elseif ($severity === "MEDIUM") {
                                $class = "medium";
                            }
                            ?>

                            <span class="badge <?= $class ?>">
                                <?= htmlspecialchars($severity) ?>
                            </span>

                        </td>

                        <td class="action">
                            <?= htmlspecialchars($log["action_taken"] ?? "—") ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($log["user_agent"] ?? "—") ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($log["created_at"]) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <?php else: ?>

            <div class="empty">
                NO SECURITY EVENTS RECORDED
            </div>

        <?php endif; ?>

    </section>

    <a class="back" href="dashboard.php">
        ← RETURN TO SECURITY OPERATIONS CENTER
    </a>

</main>

</body>
</html>