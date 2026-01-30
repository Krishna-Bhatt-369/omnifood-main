<?php
session_start();

$error = "";

// CHECK LOGIN
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get inputs
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // --- HARDCODED ADMIN CREDENTIALS ---
    // User: admin
    // Pass: omnifood
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
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
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

        .form-group { margin-bottom: 20px; text-align: left; }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #555;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            font-size: 16px;
            box-sizing: border-box;
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
            display: <?php echo !empty($error) ? 'block' : 'none'; ?>;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo">
            <ion-icon name="restaurant"></ion-icon> Omnifood
        </div>

        <div class="error-msg">
            <?php echo $error; ?>
        </div>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter admin username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
            </div>

            <button type="submit">Log In</button>
        </form>

        <p style="margin-top: 20px; font-size: 14px; color: #888;">
            <a href="index.php" style="color:#888; text-decoration:none;">&larr; Back to Website</a>
        </p>
    </div>

</body>
</html>