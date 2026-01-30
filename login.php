<?php
session_start();

$error = "";

// CHECK LOGIN
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // --- SET PASSWORD ---
    if ($username === 'admin' && $password === 'omnifood') {
        $_SESSION['loggedin'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Incorrect username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Omnifood Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <style>
        :root { --primary: #e67e22; --primary-dark: #cf711f; }
        body {
            font-family: 'Rubik', sans-serif;
            background-color: #fdf2e9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background: white;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            text-align: center;
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .logo ion-icon { color: var(--primary); font-size: 32px; }
        
        input {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 9px;
            font-family: inherit;
            font-size: 16px;
            box-sizing: border-box; /* Fixes width issues */
            transition: 0.3s;
        }
        input:focus { border-color: var(--primary); outline: none; }
        
        button {
            width: 100%;
            padding: 15px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 9px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover { background-color: var(--primary-dark); }
        
        .error-msg {
            color: #e74c3c;
            background-color: #fde8e7;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            display: <?php echo $error ? 'block' : 'none'; ?>;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo">
            <ion-icon name="restaurant-outline"></ion-icon> Omnifood
        </div>

        <div class="error-msg"><?php echo $error; ?></div>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Log In</button>
        </form>
    </div>

</body>
</html>