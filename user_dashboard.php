<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["login_type"] ?? '') !== "user") {
    header("Location: index.php");
    exit();
}

$user_name = $_SESSION["user_name"] ?? "Authorized User";
$user_username = $_SESSION["user_username"] ?? "user";
$user_email = $_SESSION["user_email"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>User Security Portal | Anti-Bot Firewall</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    min-height:100vh;
    background:
        radial-gradient(circle at 20% 20%, rgba(0,229,255,.12), transparent 30%),
        radial-gradient(circle at 80% 70%, rgba(0,102,255,.12), transparent 30%),
        #030812;
    color:#eafcff;
    overflow-x:hidden;
}

/* Animated Grid */
body::before{
    content:"";
    position:fixed;
    inset:0;
    background-image:
        linear-gradient(rgba(0,229,255,.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,229,255,.035) 1px, transparent 1px);
    background-size:50px 50px;
    animation:gridMove 12s linear infinite;
    pointer-events:none;
}

@keyframes gridMove{
    from{transform:translateY(0);}
    to{transform:translateY(50px);}
}

.container{
    position:relative;
    z-index:2;
    width:92%;
    max-width:1200px;
    margin:auto;
    padding:35px 0 100px;
}

/* Header */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:35px;
}

.brand{
    display:flex;
    align-items:center;
    gap:15px;
}

.logo{
    width:55px;
    height:55px;
    border:1px solid #00e5ff;
    border-radius:15px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#00e5ff;
    font-size:25px;
    box-shadow:0 0 25px rgba(0,229,255,.25);
}

.brand h1{
    font-size:23px;
    letter-spacing:2px;
}

.brand p{
    margin-top:5px;
    color:#71909c;
    font-size:12px;
    letter-spacing:1px;
}

.logout{
    text-decoration:none;
    color:#fff;
    border:1px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.05);
    padding:12px 20px;
    border-radius:10px;
    transition:.3s;
}

.logout:hover{
    border-color:#00e5ff;
    color:#00e5ff;
    box-shadow:0 0 20px rgba(0,229,255,.15);
}

/* Welcome */
.welcome{
    border:1px solid rgba(0,229,255,.18);
    background:rgba(8,20,35,.72);
    backdrop-filter:blur(15px);
    border-radius:20px;
    padding:30px;
    margin-bottom:25px;
    box-shadow:0 20px 60px rgba(0,0,0,.3);
}

.welcome h2{
    font-size:28px;
    margin-bottom:10px;
}

.welcome h2 span{
    color:#00e5ff;
}

.welcome p{
    color:#8ba4ae;
    line-height:1.7;
}

.status{
    display:inline-flex;
    align-items:center;
    gap:8px;
    margin-top:18px;
    padding:8px 13px;
    border-radius:20px;
    background:rgba(0,255,150,.08);
    border:1px solid rgba(0,255,150,.25);
    color:#62ffbd;
    font-size:12px;
}

.dot{
    width:8px;
    height:8px;
    border-radius:50%;
    background:#00ff9d;
    box-shadow:0 0 10px #00ff9d;
}

/* Cards */
.cards{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    margin-bottom:25px;
}

.card{
    background:rgba(8,20,35,.72);
    border:1px solid rgba(0,229,255,.14);
    border-radius:18px;
    padding:25px;
    transition:.3s;
}

.card:hover{
    transform:translateY(-5px);
    border-color:rgba(0,229,255,.45);
    box-shadow:0 15px 40px rgba(0,229,255,.08);
}

.card-icon{
    font-size:25px;
    color:#00e5ff;
    margin-bottom:15px;
}

.card h3{
    font-size:17px;
    margin-bottom:8px;
}

.card p{
    color:#8099a4;
    font-size:13px;
    line-height:1.6;
}

/* Account */
.account{
    background:rgba(8,20,35,.72);
    border:1px solid rgba(0,229,255,.14);
    border-radius:18px;
    padding:28px;
}

.account h3{
    margin-bottom:20px;
    color:#00e5ff;
    letter-spacing:1px;
}

.info{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:15px;
}

.info-box{
    padding:18px;
    background:rgba(0,0,0,.22);
    border-radius:12px;
    border:1px solid rgba(255,255,255,.06);
}

.info-box small{
    display:block;
    color:#667f8a;
    margin-bottom:7px;
    font-size:11px;
    text-transform:uppercase;
}

.info-box strong{
    color:#e9fbff;
    word-break:break-word;
}

/* Back Button */
.back{
    position:fixed;
    right:25px;
    bottom:25px;
    z-index:10;
    text-decoration:none;
    color:#00e5ff;
    background:rgba(3,12,22,.92);
    border:1px solid rgba(0,229,255,.35);
    padding:12px 18px;
    border-radius:10px;
    font-size:13px;
    transition:.3s;
}

.back:hover{
    background:#00e5ff;
    color:#001018;
    box-shadow:0 0 25px rgba(0,229,255,.35);
}

@media(max-width:800px){
    .cards{
        grid-template-columns:1fr;
    }

    .info{
        grid-template-columns:1fr;
    }

    .header{
        align-items:flex-start;
    }

    .brand h1{
        font-size:18px;
    }
}
</style>
</head>

<body>

<div class="container">

    <div class="header">

        <div class="brand">
            <div class="logo">🛡</div>

            <div>
                <h1>ANTI-BOT FIREWALL</h1>
                <p>AUTHORIZED USER SECURITY PORTAL</p>
            </div>
        </div>

        <a href="logout.php" class="logout">
            Terminate Secure Session
        </a>

    </div>


    <div class="welcome">

        <h2>
            Welcome, <span><?php echo htmlspecialchars($user_name); ?></span>
        </h2>

        <p>
            You are authenticated as an authorized entity.
            This portal provides access to protected web-form security services
            while restricting administrative security operations.
        </p>

        <div class="status">
            <span class="dot"></span>
            SECURE SESSION ACTIVE
        </div>

    </div>


    <div class="cards">

        <div class="card">
            <div class="card-icon">🛡</div>
            <h3>Protected Form</h3>
            <p>
                Access the protected web form secured by CAPTCHA,
                honeypot detection, rate limiting and validation.
            </p>
        </div>


        <div class="card">
            <div class="card-icon">🔐</div>
            <h3>Human Verification</h3>
            <p>
                Requests are evaluated through multiple security
                layers before being accepted by the application.
            </p>
        </div>


        <div class="card">
            <div class="card-icon">⚡</div>
            <h3>Request Protection</h3>
            <p>
                Suspicious requests and automated activity are
                analyzed by the Anti-Bot Firewall.
            </p>
        </div>

    </div>


    <div class="account">

        <h3>AUTHORIZED ENTITY PROFILE</h3>

        <div class="info">

            <div class="info-box">
                <small>Full Name</small>
                <strong>
                    <?php echo htmlspecialchars($user_name); ?>
                </strong>
            </div>

            <div class="info-box">
                <small>Username</small>
                <strong>
                    <?php echo htmlspecialchars($user_username); ?>
                </strong>
            </div>

            <div class="info-box">
                <small>Email</small>
                <strong>
                    <?php echo htmlspecialchars($user_email); ?>
                </strong>
            </div>

        </div>

    </div>

</div>


<a href="index.php" class="back">
    ← Back to Secure Gateway
</a>

</body>
</html>