<?php
session_start();
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure hashing
    
    // Check if email exists
    $check = $conn->query("SELECT id FROM cafe WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $error = "Email already registered. Please login.";
    } else {
        $stmt = $conn->prepare("INSERT INTO cafe (name, email, password, source) VALUES (?, ?, ?, 'Website Signup')");
        $stmt->bind_param("sss", $name, $email, $password);
        if ($stmt->execute()) {
            // Auto login after signup
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['user_name'] = $name;
            header("Location: index.php"); 
            exit();
        } else {
            $error = "Error creating account.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up - Omnifood</title>
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
        <h2 class="heading-secondary">Create Account</h2>
        <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn btn--full">Sign Up</button>
        </form>
        <p style="margin-top: 15px; font-size: 1.4rem;">Already have an account? <a href="signin.php">Log in</a></p>
        <p style="margin-top: 5px; font-size: 1.4rem;"><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>