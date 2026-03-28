<?php
session_start();
include 'connect.php';

$message = "";
$status = "";

// --- HANDLE CAREER APPLICATION ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name       = htmlspecialchars(trim($_POST['full_name']));
    $email      = htmlspecialchars(trim($_POST['email']));
    $phone      = htmlspecialchars(trim($_POST['phone']));
    $role       = htmlspecialchars(trim($_POST['role']));
    $experience = htmlspecialchars(trim($_POST['experience']));
    $details    = htmlspecialchars(trim($_POST['message']));

    // --- VALIDATION ---
    $errors = [];

    // 1. Email Check
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }
    // 2. Phone Check (Strict 10 Digits)
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }

    if (!empty($errors)) {
        $status = "error";
        $message = implode(" ", $errors);
    } else {
        // Prepare Data for DB
        // We save the application details into the 'order_items' column
        $app_data = "Application: $role | Exp: $experience | Ph: $phone | Msg: $details";
        $source = "Careers Page";

        // Insert
        $stmt = $conn->prepare("INSERT INTO cafe (name, email, order_items, source, status) VALUES (?, ?, ?, ?, 'new')");
        $stmt->bind_param("ssss", $name, $email, $app_data, $source);

        if ($stmt->execute()) {
            header("Location: careers.php?status=success");
            exit();
        } else {
            $status = "error";
            $message = "System Busy: " . $stmt->error;
        }
    }
}

// Success Message Handling
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $status = 'success';
    $message = "Application received! We will contact you for an interview.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers & Academy | Sirifood</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/general.css">
    <script type="module" src="https://unpkg.com/ionicons@5.4.0/dist/ionicons/ionicons.esm.js"></script>
    
    <style>
        /* --- HERO --- */
        .section-career-hero {
            background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('img/hero.png'); /* Using existing image as placeholder */
            background-size: cover;
            background-position: center;
            padding: 12rem 0;
            text-align: center;
            color: white;
        }
        .career-hero-title { font-size: 5.2rem; font-weight: 700; margin-bottom: 2rem; letter-spacing: -0.5px; color: #fff; }
        .career-hero-sub { font-size: 2rem; font-weight: 500; max-width: 800px; margin: 0 auto; line-height: 1.6; color: #eee; }

        /* --- LEARNING PATHS --- */
        .section-paths { padding: 9.6rem 0; background-color: #fdf2e9; }
        .path-card { background: white; border-radius: 11px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.08); transition: 0.3s; }
        .path-card:hover { transform: translateY(-10px); }
        .path-img-box { height: 250px; overflow: hidden; background: #ddd; display: flex; align-items: center; justify-content: center; font-size: 5rem; color: #888; }
        .path-content { padding: 3.2rem; }
        .path-title { font-size: 2.4rem; color: #333; margin-bottom: 1.5rem; font-weight: 700; }
        .path-desc { font-size: 1.6rem; line-height: 1.6; color: #555; margin-bottom: 2rem; }
        .path-list { list-style: none; margin-bottom: 2.4rem; }
        .path-item { font-size: 1.6rem; display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }

        /* --- MENTORSHIP PROJECT --- */
        .section-project { padding: 9.6rem 0; background-color: #fff; }
        .project-box { background-color: #e67e22; border-radius: 20px; overflow: hidden; color: white; box-shadow: 0 20px 50px rgba(230, 126, 34, 0.3); }
        .project-content { padding: 6.4rem; }
        .project-title { font-size: 3.6rem; margin-bottom: 2rem; color: white; }
        .project-text { font-size: 1.8rem; line-height: 1.8; margin-bottom: 3rem; opacity: 0.9; }
        
        /* --- FORM --- */
        .section-apply { padding: 9.6rem 0; background-color: #fdf2e9; }
        .apply-form { background: white; padding: 5rem; border-radius: 11px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); max-width: 700px; margin: 0 auto; }
        .form-label { display: block; font-size: 1.6rem; font-weight: 500; margin-bottom: 1rem; }
        .form-input, .form-select, .form-textarea { width: 100%; padding: 1.2rem; font-size: 1.6rem; border: 1px solid #ddd; border-radius: 9px; margin-bottom: 2rem; font-family: inherit; }
        
        /* ALERTS */
        .alert { padding: 15px; border-radius: 9px; margin-bottom: 30px; font-size: 1.6rem; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <header class="header">
      <a href="index.php"><img class="logo" src="./img/sirifood-logo.png" alt="Sirifood Logo"></a>
      <nav class="main-nav">
        <ul class="main-nav-list">
            <li><a class="main-nav-link" href="index.php">Home</a></li>
            <li><a class="main-nav-link" href="index.php#how">How it works</a></li>
            <li><a class="main-nav-link" href="index.php#meals">Meals</a></li>
            
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true): ?>
                <li><a class="main-nav-link nav-cta" href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a class="main-nav-link nav-cta" href="login.php">Sign In</a></li>
            <?php endif; ?>
        </ul>
      </nav>
    
    </header>

    <main>
        <section class="section-career-hero">
            <div class="container">
                <h1 class="career-hero-title">Join the Sirifood Academy</h1>
                <p class="career-hero-sub">From mastering the art of healthy cooking to creating complex coffee art. Grow your skills and teach the next generation of chefs.</p>
                <a href="#apply" class="btn btn--full margin-right-sm" style="margin-top: 3rem;">Apply Now</a>
            </div>
        </section>

        <section class="section-paths">
            <div class="container center-text">
                <span class="subheading">Career Paths</span>
                <h2 class="heading-secondary">Choose your Specialization</h2>
            </div>
            
            <div class="container grid grid--2-cols margin-top-md">
                <div class="path-card">
                    <div class="path-img-box" style="background: #e67e22; color: white;">
                        <ion-icon name="restaurant-outline"></ion-icon>
                    </div>
                    <div class="path-content">
                        <h3 class="path-title">Culinary Arts & Nutrition</h3>
                        <p class="path-desc">Learn how to cook healthy, organic meals using AI-generated recipes. Master the balance of macros and local Nepali spices.</p>
                        <ul class="path-list">
                            <li class="path-item"><ion-icon name="checkmark-circle-outline" style="color:#e67e22"></ion-icon> Knife Skills & Prep</li>
                            <li class="path-item"><ion-icon name="checkmark-circle-outline" style="color:#e67e22"></ion-icon> Diet-specific Cooking (Keto, Vegan)</li>
                            <li class="path-item"><ion-icon name="checkmark-circle-outline" style="color:#e67e22"></ion-icon> Zero-Waste Techniques</li>
                        </ul>
                    </div>
                </div>

                <div class="path-card">
                    <div class="path-img-box" style="background: #333; color: white;">
                        <ion-icon name="cafe-outline"></ion-icon>
                    </div>
                    <div class="path-content">
                        <h3 class="path-title">Barista & Latte Art Mastery</h3>
                        <p class="path-desc">Go beyond simple coffee. Learn the physics of milk frothing and create complex shapes like Swans, Rosettas, and 3D Art.</p>
                        <ul class="path-list">
                            <li class="path-item"><ion-icon name="checkmark-circle-outline" style="color:#e67e22"></ion-icon> Espresso Extraction Science</li>
                            <li class="path-item"><ion-icon name="checkmark-circle-outline" style="color:#e67e22"></ion-icon> Advanced Latte Art (Tulip, Swan)</li>
                            <li class="path-item"><ion-icon name="checkmark-circle-outline" style="color:#e67e22"></ion-icon> Machine Maintenance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-project">
            <div class="container">
                <div class="project-box grid grid--2-cols">
                    <div class="project-content">
                        <span class="subheading" style="color: #fdf2e9;">Leadership Project</span>
                        <h2 class="project-title">The "Train the Trainer" Project</h2>
                        <p class="project-text">
                            Are you already an expert? Join our special project to <strong>enhance your teaching skills</strong>. 
                            You will lead live cooking sessions and mentor junior chefs. It's not just about cooking; it's about leading a kitchen.
                        </p>
                        <ul class="path-list" style="color: white;">
                            <li class="path-item"><ion-icon name="star"></ion-icon> Conduct Live Workshops</li>
                            <li class="path-item"><ion-icon name="star"></ion-icon> Create Training Manuals</li>
                            <li class="path-item"><ion-icon name="star"></ion-icon> Earn "Master Chef" Certification</li>
                        </ul>
                        <a href="#apply" class="btn btn--outline" style="background: white; color: #e67e22; border: none;">Join as a Mentor</a>
                    </div>
                    <div class="project-img" style="background-image: url('img/eating.jpg'); background-size: cover; background-position: center; min-height: 400px;">
                        </div>
                </div>
            </div>
        </section>

        <section class="section-apply" id="apply">
            <div class="container center-text">
                <h2 class="heading-secondary">Apply Today</h2>
                <p>Start your journey with Sirifood.</p>
            </div>

            <div class="container margin-top-md">
                <div class="apply-form">
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo ($status === 'success') ? 'alert-success' : 'alert-error'; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form action="careers.php" method="POST">
                        <div class="grid grid--2-cols" style="gap: 20px;">
                            <div>
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-input" required placeholder="John Doe">
                            </div>
                            <div>
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-input" required placeholder="you@example.com">
                            </div>
                        </div>

                        <div class="grid grid--2-cols" style="gap: 20px;">
                            <div>
                                <label class="form-label">Phone (10 digits)</label>
                                <input type="tel" name="phone" class="form-input" required pattern="[0-9]{10}" placeholder="98XXXXXXXX">
                            </div>
                            <div>
                                <label class="form-label">Applying For</label>
                                <select name="role" class="form-select">
                                    <option value="Chef Student">Culinary Student (Learning)</option>
                                    <option value="Barista Student">Barista Student (Latte Art)</option>
                                    <option value="Mentor Project">Mentor Project (Teaching)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Years of Experience</label>
                            <select name="experience" class="form-select">
                                <option value="None">No Experience (I want to learn)</option>
                                <option value="1-2 Years">1-2 Years</option>
                                <option value="3-5 Years">3-5 Years</option>
                                <option value="5+ Years">5+ Years (Expert)</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Why do you want to join?</label>
                            <textarea name="message" class="form-textarea" rows="4" placeholder="Tell us about your passion for food or coffee..."></textarea>
                        </div>

                        <button class="btn btn--full" style="width: 100%;">Submit Application</button>
                    </form>
                </div>
            </div>
        </section>

    </main>

    <footer class="footer">
      <div class="container grid grid--footer">
        <div class="logo-col"><img class="logo" src="./img/sirifood-logo.png" alt="Sirifood Logo"><p class="copyright">Copyright &copy; 2025 by Sirifood Nepal.</p></div>
        <div class="address-col"><p class="footer-heading">Contact</p><address class="contacts"><p>Kathmandu, Nepal</p><p>980-000-0000</p></address></div>
        <nav class="nav-col"><p class="footer-heading">Company</p><ul class="footer-nav"><li><a class="footer-link" href="business.php">For Business</a></li><li><a class="footer-link" href="careers.php">Careers</a></li></ul></nav>
        <nav class="nav-col"><p class="footer-heading">Resources</p><ul class="footer-nav"><li><a class="footer-link" href="recipes.php">Recipe Directory</a></li></ul></nav>
      </div>
    </footer>
</body>
</html>