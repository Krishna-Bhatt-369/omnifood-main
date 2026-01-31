<?php
session_start();
include 'connect.php'; // Connected to database

$error = "";

// CHECK LOGIN
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get inputs
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // --- 1. HARDCODED ADMIN CREDENTIALS ---
    // User: admin
    // Pass: omnifood
    if ($username === 'admin' && $password === 'omnifood') {
        $_SESSION['loggedin'] = true;
        $_SESSION['admin_name'] = "Admin";
        header("Location: admin.php");
        exit;
    }

    // --- 2. CHECK DATABASE (SIGN UP USERS) ---
    // Connects login with the "Sign Up" data in 'cafe' table
    // Treat Name as Username and Email as Password
    $stmt = $conn->prepare("SELECT * FROM cafe WHERE name = ? AND email = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['loggedin'] = true;
        // Store the user's name in session
        $_SESSION['admin_name'] = isset($row['Name']) ? $row['Name'] : $row['name'];
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
        label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 5px; color: #555; }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: 0.3s;
        }
        input:focus { border-color: var(--primary); outline: none; }

        button {
            width: 100%;
            background-color: var(--primary);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
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
                <label for="username">Name (Username)</label>
                <input type="text" id="username" name="username" placeholder="Enter name" required>
            </div>

            <div class="form-group">
                <label for="password">Email (Password)</label>
                <input type="password" id="password" name="password" placeholder="Enter email" required>
            </div>

            <button type="submit">Log In</button>
        </form>
    </div>

</body>
</html>