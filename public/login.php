<?php
session_start();
include(__DIR__ . "/../config/db.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, full_name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] == 'authority') {
                header("Location: authority/dashboard.php");
            } else {
                header("Location: citizen/dashboard.php");
            }
            exit();

        } else {
            $error = "Invalid email or password.";
        }

    } else {
        $error = "Invalid email or password.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: url('../assets/images/road2.jpg') no-repeat center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background: rgba(0,0,0,0.8);
            padding: 30px;
            border-radius: 12px;
            width: 350px;
            color: white;
            text-align: center;
        }

        .login-box img {
            width: 80px;
            margin-bottom: 10px;
        }

        h2 {
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .login-btn {
            background: #28a745;
            color: white;
        }

        .register-btn {
            background: #007bff;
            color: white;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 10px;
            font-size: 12px;
            color: #ccc;
        }
    </style>
</head>

<body>

<div class="login-box">

    
    <img src="../assets/images/login1.png" alt="Login">

    <h2>Smart Road Hazard System</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">

        <input type="email" name="email" placeholder="Enter Email" required>

        <input type="password" name="password" placeholder="Enter Password" required>

        <button type="submit" class="login-btn">Login</button>

        <button type="button" class="register-btn" onclick="window.location.href='register.php'">
            Register
        </button>

    </form>

    <div class="footer">
        © Smart Hazard System
    </div>

</div>

</body>
</html>