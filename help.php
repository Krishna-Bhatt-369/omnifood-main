<?php
session_start();
include 'connect.php';

$message = "";
$status = "";

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = htmlspecialchars(trim($_POST['full_name']));
    $email   = htmlspecialchars(trim($_POST['email']));
    $topic   = htmlspecialchars(trim($_POST['topic']));
    $details = htmlspecialchars(trim($_POST['message']));

    $support_data = "Support Ticket [" . $topic . "]: " . $details;
    $source = "Help Center";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $status = "error";
        $message = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("INSERT INTO cafe (name, email, order_items, source, status) VALUES (?, ?, ?, ?, 'new')");
        $stmt->bind_param("ssss", $name, $email, $support_data, $source);

        if ($stmt->execute()) {
            $status = "success";
            $message = "Ticket #". $conn->insert_id ." received. We'll reply shortly.";
        } else {
            $status = "error";
            $message = "System busy. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center | Omnifood</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/general.css">
    <script type="module" src="https://unpkg.com/ionicons@5.4.0/dist/ionicons/ionicons.esm.js"></script>

    <style>
        /* --- RESET & BASICS --- */
        body { background-color: #f7f7f7; }
        .header { background-color: #fff; border-bottom: 1px solid #eee; }

        /* --- HERO SECTION (LEFT ALIGNED) --- */
        .help-hero {
            background-color: #fff;
            padding: 5rem 0 4rem 0; 
            border-bottom: 1px solid #eee;
        }
        
        .help-title {
            font-size: 3.6rem;
            color: #333;
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
            /* Text is left-aligned by default */
        }
        
        .help-subtitle {
            font-size: 1.8rem;
            color: #777;
            max-width: 700px;
            margin-bottom: 0; 
            line-height: 1.6;
        }

        /* --- FAQ SECTION --- */
        .section-faq {
            padding: 4rem 0; 
            background-color: #f7f7f7;
        }
        
        .faq-grid {
            max-width: 900px;
            margin: 0 auto; /* Keeps the cards centered in the container */
            display: flex;
            flex-direction: column;
            gap: 16px; 
        }

        .faq-card {
            background: #fff;
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }

        .faq-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.05);
            border-color: #e67e22;
        }

        .faq-header {
            padding: 20px 24px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-question {
            font-size: 1.8rem;
            font-weight: 600;
            color: #444;
        }

        .faq-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-in-out;
            background-color: #fafafa;
        }

        .faq-content {
            padding: 20px 24px;
            font-size: 1.6rem;
            line-height: 1.6;
            color: #555;
            border-top: 1px solid #eee;
        }

        .faq-card.active .faq-body { max-height: 300px; }
        .faq-card.active .faq-header ion-icon { transform: rotate(180deg); color: #e67e22; }
        .faq-card.active .faq-question { color: #e67e22; }

        /* --- CONTACT SECTION --- */
        .section-contact-us {
            padding: 5rem 0 8rem 0;
            background-color: #e67e22;
            position: relative;
        }

        .section-contact-us::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: radial-gradient(rgba(255, 255, 255, 0.2) 2px, transparent 2px);
            background-size: 30px 30px;
            opacity: 0.3;
        }

        .form-container {
            background: #fff;
            max-width: 800px;
            margin: 0 auto;
            border-radius: 15px;
            padding: 4rem 5rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            position: relative;
            z-index: 2;
        }

        .form-header { text-align: center; margin-bottom: 3rem; }
        .form-title { font-size: 2.8rem; color: #333; margin-bottom: 0.5rem; }
        .form-sub { font-size: 1.6rem; color: #777; }

        .form-group { margin-bottom: 2rem; }
        .form-label { display: block; font-size: 1.3rem; font-weight: 700; color: #555; margin-bottom: 0.6rem; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 1.2rem;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1.6rem;
            font-family: inherit;
            transition: 0.3s;
            background: #fdfdfd;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #e67e22;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(230, 126, 34, 0.1);
        }

        .btn-submit {
            background-color: #333;
            color: #fff;
            width: 100%;
            padding: 1.4rem;
            font-size: 1.8rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 0.5rem;
        }
        
        .btn-submit:hover {
            background-color: #e67e22;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .alert {
            padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        @media (max-width: 600px) {
            .form-container { padding: 3rem 2rem; }
            .help-title { font-size: 3rem; }
        }
    </style>
</head>
<body>

    <header class="header">
      <a href="index.php"><img class="logo" src="./img/omnifood-logo.png" alt="Omnifood Logo"></a>
      <nav class="main-nav">
        <ul class="main-nav-list">
            <li><a class="main-nav-link" href="index.php">Home</a></li>
            <li><a class="main-nav-link" href="recipes.php">Recipes</a></li>
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
        <section class="help-hero">
            <div class="container">
                <h1 class="help-title">Hello, how can we help?</h1>
                <p class="help-subtitle">Find answers to common questions about subscriptions, delivery, and payments, or get in touch with our team directly.</p>
            </div>
        </section>

        <section class="section-faq">
            <div class="container">
                <div class="faq-grid">
                    <div class="faq-card" onclick="toggleFaq(this)">
                        <div class="faq-header">
                            <span class="faq-question">How can I cancel or pause my subscription?</span>
                            <ion-icon name="chevron-down-outline"></ion-icon>
                        </div>
                        <div class="faq-body">
                            <div class="faq-content">
                                You can manage your subscription directly from your user dashboard. Simply navigate to "Account Settings" and select "Pause" to skip a week or "Cancel" to stop billing. There are no cancellation fees.
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-card" onclick="toggleFaq(this)">
                        <div class="faq-header">
                            <span class="faq-question">Where do you deliver in Nepal?</span>
                            <ion-icon name="chevron-down-outline"></ion-icon>
                        </div>
                        <div class="faq-body">
                            <div class="faq-content">
                                We currently cover the entire Kathmandu Valley (Kathmandu, Lalitpur, Bhaktapur). We are launching in Pokhara and Chitwan in late 2025.
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-card" onclick="toggleFaq(this)">
                        <div class="faq-header">
                            <span class="faq-question">I have allergies. Is it safe for me?</span>
                            <ion-icon name="chevron-down-outline"></ion-icon>
                        </div>
                        <div class="faq-body">
                            <div class="faq-content">
                                Absolutely. When you sign up, our AI collects your dietary restrictions (Nut, Dairy, Gluten, etc.). This data is sent to our chefs who prepare your specific meal separately to ensure zero cross-contamination.
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-card" onclick="toggleFaq(this)">
                        <div class="faq-header">
                            <span class="faq-question">Can I pay via Esewa or Khalti?</span>
                            <ion-icon name="chevron-down-outline"></ion-icon>
                        </div>
                        <div class="faq-body">
                            <div class="faq-content">
                                Yes! We accept all major digital wallets in Nepal (Esewa, Khalti, IME Pay) as well as Fonepay and direct bank transfers. Cash on Delivery is available for your first week.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-contact-us" id="contact">
            <div class="container">
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">Send us a message</h2>
                        <p class="form-sub">Our support team typically replies within 2 hours.</p>
                    </div>

                    <?php if ($status === 'success'): ?>
                        <div class="alert alert-success">
                            <ion-icon name="checkmark-circle"></ion-icon> <?php echo $message; ?>
                        </div>
                    <?php elseif ($status === 'error'): ?>
                        <div class="alert alert-error">
                            <ion-icon name="alert-circle"></ion-icon> <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form action="help.php#contact" method="POST">
                        <div class="grid grid--2-cols" style="gap: 20px;">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-input" placeholder="e.g. John Doe" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-input" placeholder="e.g. you@gmail.com" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Topic</label>
                            <select name="topic" class="form-select" required>
                                <option value="General">General Inquiry</option>
                                <option value="Billing">Billing & Refunds</option>
                                <option value="Food">Food Quality & Diet</option>
                                <option value="Delivery">Delivery Issue</option>
                                <option value="Tech">Website / App Issue</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-textarea" rows="4" placeholder="Tell us how we can help..." required></textarea>
                        </div>

                        <button type="submit" class="btn-submit">Submit Ticket</button>
                    </form>
                </div>
            </div>
        </section>

    </main>

    <footer class="footer">
      <div class="container grid grid--footer">
        <div class="logo-col"><img class="logo" src="./img/omnifood-logo.png" alt="Omnifood Logo"><p class="copyright">Copyright &copy; 2025 by Omnifood Nepal.</p></div>
        <div class="address-col"><p class="footer-heading">Contact</p><address class="contacts"><p>Kathmandu, Nepal</p><p>980-000-0000</p></address></div>
        <nav class="nav-col"><p class="footer-heading">Account</p><ul class="footer-nav"><li><a class="footer-link" href="signin.php">Sign in</a></li></ul></nav>
        <nav class="nav-col"><p class="footer-heading">Company</p><ul class="footer-nav"><li><a class="footer-link" href="business.php">For Business</a></li><li><a class="footer-link" href="index.php#chefs">Cooking partners</a></li></ul></nav>
      </div>
    </footer>

    <script>
        function toggleFaq(card) {
            const allCards = document.querySelectorAll('.faq-card');
            allCards.forEach(c => {
                if(c !== card) c.classList.remove('active');
            });
            card.classList.toggle('active');
        }
    </script>
</body>
</html>