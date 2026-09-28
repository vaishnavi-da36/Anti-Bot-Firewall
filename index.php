<?php
session_start();
require_once "config/db.php";

$error = "";
$success = "";

/* =========================================
   LOGIN
========================================= */

if (isset($_POST["login"])) {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        $error = "Please enter username and password.";

    } else {

        /* ADMIN LOGIN */
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, username, password, full_name, role, status
             FROM admin_users
             WHERE username = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {

            $admin = mysqli_fetch_assoc($result);

            if (
                $admin["status"] === "Active" &&
                $admin["password"] === md5($password)
            ) {

                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_username"] = $admin["username"];
                $_SESSION["admin_name"] = $admin["full_name"];
                $_SESSION["admin_role"] = $admin["role"];
                $_SESSION["login_type"] = "admin";

                header("Location: dashboard.php");
                exit();

            } elseif ($admin["status"] !== "Active") {

                $error = "Admin account is blocked.";

            } else {

                $error = "Invalid username or password.";
            }

        } else {

            /* USER LOGIN */

            $stmt2 = mysqli_prepare(
                $conn,
                "SELECT id, username, full_name, email, password, status
                 FROM users
                 WHERE username = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt2, "s", $username);
            mysqli_stmt_execute($stmt2);

            $result2 = mysqli_stmt_get_result($stmt2);

            if ($result2 && mysqli_num_rows($result2) === 1) {

                $user = mysqli_fetch_assoc($result2);

                if ($user["status"] !== "Active") {

                    $error = "Your user account is blocked.";

                } elseif ($user["password"] === md5($password)) {

                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["user_username"] = $user["username"];
                    $_SESSION["user_name"] = $user["full_name"];
                    $_SESSION["user_email"] = $user["email"];
                    $_SESSION["login_type"] = "user";

                    header("Location: user_dashboard.php");
                    exit();

                } else {

                    $error = "Invalid username or password.";
                }

            } else {

                $error = "Invalid username or password.";
            }
        }
    }
}


/* =========================================
   USER ACCOUNT CREATION
========================================= */

if (isset($_POST["create_account"])) {

    $full_name = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["new_username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["new_password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if (
        $full_name === "" ||
        $username === "" ||
        $email === "" ||
        $password === "" ||
        $confirm_password === ""
    ) {

        $error = "All account fields are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $error = "Password must contain at least 6 characters.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        /* CHECK USERNAME */
        $check_username = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE username = ?
             UNION
             SELECT id FROM admin_users WHERE username = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check_username,
            "ss",
            $username,
            $username
        );

        mysqli_stmt_execute($check_username);

        $username_result = mysqli_stmt_get_result($check_username);

        if ($username_result && mysqli_num_rows($username_result) > 0) {

            $error = "Username already exists.";

        } else {

            /* CHECK EMAIL */
            $check_email = mysqli_prepare(
                $conn,
                "SELECT id FROM users WHERE email = ? LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $check_email,
                "s",
                $email
            );

            mysqli_stmt_execute($check_email);

            $email_result = mysqli_stmt_get_result($check_email);

            if ($email_result && mysqli_num_rows($email_result) > 0) {

                $error = "Email address already registered.";

            } else {

                $hashed_password = md5($password);

                $insert = mysqli_prepare(
                    $conn,
                    "INSERT INTO users
                    (username, full_name, email, password, status)
                    VALUES (?, ?, ?, ?, 'Active')"
                );

                mysqli_stmt_bind_param(
                    $insert,
                    "ssss",
                    $username,
                    $full_name,
                    $email,
                    $hashed_password
                );

                if (mysqli_stmt_execute($insert)) {

                    $success =
                        "Account created successfully. You can now login.";

                } else {

                    $error = "Account creation failed. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Anti-Bot Firewall | Secure Access Gateway</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    min-height:100vh;
    font-family:Arial, Helvetica, sans-serif;
    background:#02070d;
    color:#e9fbff;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}

/* GRID */

body::before{
    content:"";
    position:fixed;
    inset:0;
    background:
        linear-gradient(rgba(0,220,255,.035) 1px,transparent 1px),
        linear-gradient(90deg,rgba(0,220,255,.035) 1px,transparent 1px);
    background-size:45px 45px;
    animation:gridMove 18s linear infinite;
    z-index:-5;
}

@keyframes gridMove{
    from{
        transform:translate(0,0);
    }
    to{
        transform:translate(45px,45px);
    }
}

/* GLOW */

.glow{
    position:fixed;
    width:650px;
    height:650px;
    border-radius:50%;
    background:radial-gradient(
        circle,
        rgba(0,217,255,.13),
        transparent 68%
    );
    top:-250px;
    right:-200px;
    animation:glowMove 7s ease-in-out infinite alternate;
    z-index:-4;
}

.glow2{
    position:fixed;
    width:500px;
    height:500px;
    border-radius:50%;
    background:radial-gradient(
        circle,
        rgba(0,120,255,.08),
        transparent 68%
    );
    bottom:-220px;
    left:-180px;
    animation:glowMove2 8s ease-in-out infinite alternate;
    z-index:-4;
}

@keyframes glowMove{
    from{
        transform:scale(1);
    }
    to{
        transform:scale(1.25);
    }
}

@keyframes glowMove2{
    from{
        transform:scale(1);
    }
    to{
        transform:scale(1.2);
    }
}

/* MAIN */

.container{
    width:100%;
    max-width:470px;
    padding:20px;
}

.panel{
    background:rgba(5,16,27,.90);
    border:1px solid rgba(0,217,255,.18);
    border-radius:18px;
    padding:35px;
    backdrop-filter:blur(20px);
    box-shadow:
        0 25px 80px rgba(0,0,0,.55),
        0 0 35px rgba(0,217,255,.05);
}

/* LOGO */

.logo{
    text-align:center;
    margin-bottom:25px;
}

.logo-icon{
    width:68px;
    height:68px;
    margin:auto;
    border:2px solid #00d9ff;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:31px;
    color:#00d9ff;
    box-shadow:
        0 0 20px rgba(0,217,255,.5),
        inset 0 0 18px rgba(0,217,255,.08);
}

.logo h1{
    margin-top:15px;
    font-size:20px;
    letter-spacing:2px;
}

.logo p{
    margin-top:7px;
    font-size:9px;
    color:#00d9ff;
    letter-spacing:2px;
}

/* TABS */

.tabs{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:8px;
    margin-bottom:25px;
}

.tab{
    border:1px solid rgba(0,217,255,.12);
    background:rgba(0,217,255,.025);
    color:#718995;
    padding:12px;
    border-radius:8px;
    cursor:pointer;
    text-align:center;
    font-size:10px;
    letter-spacing:1px;
    transition:.25s;
}

.tab.active{
    color:#00d9ff;
    border-color:rgba(0,217,255,.35);
    background:rgba(0,217,255,.08);
}

/* FORM */

.form-title{
    font-size:13px;
    letter-spacing:1.5px;
    margin-bottom:20px;
    color:#ffffff;
}

.field{
    margin-bottom:16px;
}

.field label{
    display:block;
    color:#718995;
    font-size:9px;
    letter-spacing:1.2px;
    margin-bottom:7px;
}

.field input{
    width:100%;
    padding:13px 14px;
    background:#07131e;
    border:1px solid rgba(0,217,255,.14);
    border-radius:8px;
    color:#ffffff;
    outline:none;
    font-size:12px;
    transition:.25s;
}

.field input:focus{
    border-color:#00d9ff;
    box-shadow:0 0 14px rgba(0,217,255,.10);
}

/* BUTTON */

.btn{
    width:100%;
    border:none;
    padding:14px;
    margin-top:5px;
    border-radius:8px;
    background:linear-gradient(
        90deg,
        #007da3,
        #00d9ff
    );
    color:#001018;
    font-weight:bold;
    font-size:10px;
    letter-spacing:1.5px;
    cursor:pointer;
    transition:.25s;
}

.btn:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 25px rgba(0,217,255,.20);
}

/* MESSAGE */

.message{
    padding:11px 13px;
    border-radius:8px;
    margin-bottom:17px;
    font-size:10px;
    line-height:1.5;
}

.error{
    color:#ff8094;
    background:rgba(255,50,80,.06);
    border:1px solid rgba(255,50,80,.18);
}

.success{
    color:#5dffc5;
    background:rgba(0,255,170,.06);
    border:1px solid rgba(0,255,170,.18);
}

/* SECURITY FOOTER */

.security-footer{
    margin-top:23px;
    padding-top:18px;
    border-top:1px solid rgba(0,217,255,.08);
    text-align:center;
    color:#4f6875;
    font-size:8px;
    letter-spacing:1.2px;
}

.security-footer span{
    color:#00d9ff;
}

/* CREATE ACCOUNT HINT */

.account-note{
    text-align:center;
    margin-top:16px;
    color:#536d79;
    font-size:9px;
}

.account-note strong{
    color:#00d9ff;
}

/* HIDE */

.hidden{
    display:none;
}

/* RESPONSIVE */

@media(max-width:520px){

    .panel{
        padding:25px 20px;
    }

    .container{
        padding:12px;
    }

}

</style>

</head>

<body>

<div class="glow"></div>
<div class="glow2"></div>

<div class="container">

    <div class="panel">

        <!-- LOGO -->

        <div class="logo">

            <div class="logo-icon">
                🛡
            </div>

            <h1>
                SECURE ACCESS GATEWAY
            </h1>

            <p>
                ANTI-BOT FIREWALL
            </p>

        </div>


        <!-- TABS -->

        <div class="tabs">

            <div
                class="tab active"
                id="loginTab"
                onclick="showLogin()"
            >
                LOGIN
            </div>

            <div
                class="tab"
                id="registerTab"
                onclick="showRegister()"
            >
                CREATE ACCOUNT
            </div>

        </div>


        <!-- MESSAGE -->

        <?php if ($error !== ""): ?>

            <div class="message error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <?php if ($success !== ""): ?>

            <div class="message success">
                <?= htmlspecialchars($success) ?>
            </div>

        <?php endif; ?>


        <!-- =====================================
             LOGIN FORM
        ====================================== -->

        <div id="loginSection">

            <div class="form-title">
                AUTHENTICATE & ENTER
            </div>

            <form method="POST">

                <div class="field">

                    <label>
                        USERNAME
                    </label>

                    <input
                        type="text"
                        name="username"
                        placeholder="Enter username"
                        required
                    >

                </div>


                <div class="field">

                    <label>
                        PASSWORD
                    </label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Enter password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="login"
                    class="btn"
                >
                    AUTHENTICATE & ENTER
                </button>

            </form>

            <div class="account-note">
                New user?
                <strong onclick="showRegister()" style="cursor:pointer;">
                    CREATE ACCOUNT
                </strong>
            </div>

        </div>


        <!-- =====================================
             REGISTER FORM
        ====================================== -->

        <div
            id="registerSection"
            class="hidden"
        >

            <div class="form-title">
                CREATE USER ACCOUNT
            </div>

            <form method="POST">

                <div class="field">

                    <label>
                        FULL NAME
                    </label>

                    <input
                        type="text"
                        name="full_name"
                        placeholder="Enter full name"
                        required
                    >

                </div>


                <div class="field">

                    <label>
                        USERNAME
                    </label>

                    <input
                        type="text"
                        name="new_username"
                        placeholder="Create username"
                        required
                    >

                </div>


                <div class="field">

                    <label>
                        EMAIL ADDRESS
                    </label>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter email"
                        required
                    >

                </div>


                <div class="field">

                    <label>
                        PASSWORD
                    </label>

                    <input
                        type="password"
                        name="new_password"
                        placeholder="Create password"
                        required
                    >

                </div>


                <div class="field">

                    <label>
                        CONFIRM PASSWORD
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Confirm password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="create_account"
                    class="btn"
                >
                    CREATE SECURE ACCOUNT
                </button>

            </form>

        </div>


        <!-- FOOTER -->

        <div class="security-footer">

            <span>●</span>
            FIREWALL STATUS: ACTIVE
            &nbsp; | &nbsp;
            DETECT • ANALYZE • MITIGATE • PROTECT

        </div>

    </div>

</div>


<script>

function showLogin(){

    document.getElementById("loginSection").classList.remove("hidden");
    document.getElementById("registerSection").classList.add("hidden");

    document.getElementById("loginTab").classList.add("active");
    document.getElementById("registerTab").classList.remove("active");
}


function showRegister(){

    document.getElementById("loginSection").classList.add("hidden");
    document.getElementById("registerSection").classList.remove("hidden");

    document.getElementById("loginTab").classList.remove("active");
    document.getElementById("registerTab").classList.add("active");
}

</script>

</body>
</html>