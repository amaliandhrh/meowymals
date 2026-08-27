<?php
session_start();

$conn = new mysqli("localhost", "root", "", "inseat_food_ordering_system_");

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("
        SELECT * FROM Staff
        WHERE Username='$username'
        AND Password='$password'
    ");

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();

        $_SESSION['staff_id'] = $row['Staff_ID'];
        $_SESSION['username'] = $row['Username'];
        $_SESSION['role'] = $row['Role'];

        if ($row['Role'] == 'admin') {
            header("Location: admin.php");
            exit();
        } elseif ($row['Role'] == 'kitchen') {
            header("Location: kitchen.php");
            exit();
        } elseif ($row['Role'] == 'cashier') {
            header("Location: cashier.php");
            exit();
        }
    } else {
        $error = "Username or password is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Staff Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background: linear-gradient(135deg,#ffd6e0,#cde7ff,#fff0c9);
    font-family:"Poppins", Arial, sans-serif;
    min-height:100vh;
}

.main-container{
    max-width:450px;
    margin:auto;
}

.top-nav{
    margin-top:25px;
    margin-bottom:20px;
    text-align:left;
}

.home-card{
    display:inline-block;
    background:linear-gradient(135deg,#ffd6e0,#cdb4db) !important;
    color:#444 !important;
    padding:14px 28px !important;
    border-radius:25px !important;
    text-decoration:none !important;
    font-weight:800 !important;
    box-shadow:0 8px 18px rgba(0,0,0,0.15) !important;
    border:4px solid white !important;
    transition:0.3s !important;
}

.home-card:hover{
    background:linear-gradient(135deg,#cde7ff,#fff0c9) !important;
    color:#444 !important;
    transform:translateY(-3px);
}

.login-box{
    background:rgba(255,255,255,0.92);
    padding:35px;
    border-radius:30px;
    box-shadow:0 10px 25px rgba(0,0,0,0.13);
    border:2px solid white;
    text-align:center;
}

.login-icon{
    font-size:55px;
    background:#ffd6e0;
    width:90px;
    height:90px;
    border-radius:25px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 15px auto;
    box-shadow:0 8px 18px rgba(0,0,0,0.10);
}

.title{
    font-weight:800;
    color:#3f3f46;
    margin-bottom:5px;
}

.subtitle{
    color:#6b7280;
    margin-bottom:25px;
}

.form-control{
    border-radius:15px;
    border:2px solid #fbcfe8;
    padding:13px;
}

.form-control:focus{
    border-color:#93c5fd;
    box-shadow:0 0 0 0.2rem rgba(147,197,253,0.25);
}

.btn-login{
    background:linear-gradient(135deg,#a8d8ff,#cdb4db);
    border:none;
    color:#333;
    border-radius:18px;
    padding:13px;
    font-weight:800;
    transition:0.3s;
    width:100%;
}

.btn-login:hover{
    transform:translateY(-2px);
    background:linear-gradient(135deg,#90cfff,#b892cc);
}

.error-box{
    background:#ffb3b3;
    color:#7f1d1d;
    padding:10px;
    border-radius:15px;
    margin-top:15px;
    font-weight:700;
}
</style>
</head>

<body>

<div class="container main-container">

<div class="top-nav">
    <a href="index.php" class="home-card">🏠 Back Home</a>
</div>

<div class="login-box">

<div class="login-icon">🔐</div>

<h2 class="title">Staff Login</h2>
<p class="subtitle">Admin • Kitchen • Cashier Access</p>

<form method="POST">

<input class="form-control mb-3"
       type="text"
       name="username"
       placeholder="Username"
       required>

<input class="form-control mb-3"
       type="password"
       name="password"
       placeholder="Password"
       required>

<button class="btn-login" type="submit">
    Login
</button>

</form>

<?php if($error != "") { ?>
<div class="error-box">
    <?= $error ?>
</div>
<?php } ?>

</div>

</div>

</body>
</html>