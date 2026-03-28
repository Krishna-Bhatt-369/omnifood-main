<?php
session_start();
include 'connect.php'; 

$error = "";

// CHECK LOGIN
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize Inputs (Security)
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // 2. ADMIN LOGIN (Secure Hardcoded Check)
    // Username: admin | Password: sirifood
    if ($username === 'admin' && $password === 'sirifood') {
        session_regenerate_id(true); // Security: New session ID
        $_SESSION['loggedin'] = true;
        $_SESSION['is_admin'] = true; 
        $_SESSION['user_name'] = "Administrator";
        header("Location: admin.php");
        exit;
    }

    // 3. REGULAR USER LOGIN (Database Check)
    // Note: This checks if the entered 'Password' matches the user's Email in the database.

    $stmt = $conn->prepare("SELECT * FROM cafe WHERE name = ? AND email = ?");
    $stmt->bind_param("ss", $username, $password); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        session_regenerate_id(true); // Security: New session ID
        $_SESSION['loggedin'] = true;
        $_SESSION['is_admin'] = false; 
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['user_id'] = $row['id'];
        
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid Username or Password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login | Sirifood</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <style>
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
            border-radius: 9px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 380px;
        }
        .logo-box { text-align: center; margin-bottom: 30px; }
        .logo-text { font-size: 2.4rem; font-weight: 700; color: #333; display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none; }
        .logo-icon { color: #e67e22; font-size: 3.2rem; }
        
        .form-label { display: block; font-size: 1.4rem; font-weight: 500; margin-bottom: 8px; color: #555; }
        .form-input {
            width: 100%;
            padding: 12px;
            font-size: 1.6rem;
            border: 1px solid #ddd;
            border-radius: 9px;
            box-sizing: border-box;
            margin-bottom: 20px;
            transition: 0.3s;
            font-family: inherit;
        }
        .form-input:focus { outline: none; border-color: #e67e22; box-shadow: 0 0 0 3px rgba(230,126,34,0.1); }
        
        .btn-login {
            width: 100%;
            background-color: #e67e22;
            color: white;
            font-size: 1.8rem;
            font-weight: 600;
            border: none;
            border-radius: 9px;
            padding: 12px;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-login:hover { background-color: #cf711f; }
        
        .error-msg {
            background-color: #fde8e7;
            color: #c92a2a;
            padding: 12px;
            border-radius: 9px;
            font-size: 1.4rem;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #ffa8a8;
            display: <?php echo !empty($error) ? 'block' : 'none'; ?>;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-box">
            <a href="index.php" class="logo-text">
                <ion-icon name="restaurant" class="logo-icon"></ion-icon> 
                <span>Sirifood</span>
            </a>
        </div>

        <div class="error-msg"><?php echo $error; ?></div>

        <form method="POST" action="login.php">
            <div>
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" placeholder="Enter username" required>
            </div>

            <div>
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="Enter password" required>
            </div>

            <button type="submit" class="btn-login">Sign In</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color: #777; font-size: 1.4rem; text-decoration: none;">&larr; Back to Home</a>
        </div>
    </div>

</body>
</html>