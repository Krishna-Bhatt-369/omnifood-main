<?php
session_start();
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM cafe WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            
            // If they came from checkout, send them back to index to finish buying
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Log In - Omnifood</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/general.css">
    <style>
        body { background: #fdf2e9; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .auth-box { background: white; padding: 40px; border-radius: 9px; width: 400px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { width: 100%; cursor: pointer; }
    </style>
</head>
<body>
    <div class="auth-box">
        <h2 class="heading-secondary">Welcome Back</h2>
        <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn btn--full">Log In</button>
        </form>
        <p style="margin-top: 15px; font-size: 1.4rem;">New here? <a href="signup.php">Create Account</a></p>
        <p style="margin-top: 5px; font-size: 1.4rem;"><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>