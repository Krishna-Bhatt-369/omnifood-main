<?php
include 'connect.php';

$message = "";
$status = "";

// --- HANDLE BUSINESS FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $company_name = htmlspecialchars(trim($_POST['company_name']));
    $contact_person = htmlspecialchars(trim($_POST['contact_person']));
    $email = htmlspecialchars(trim($_POST['email']));
    $employees = htmlspecialchars(trim($_POST['employees']));
    $requirements = htmlspecialchars(trim($_POST['requirements']));
    
    // Combine fields to fit into your existing DB structure (name, email, order_items, source)
    $full_name = $contact_person . " (" . $company_name . ")";
    $order_details = "Business Inquiry: " . $employees . " employees. Req: " . $requirements;
    $source = "Business Page";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: business.php?status=invalid");
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO cafe (name, email, order_items, source) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $order_details, $source);
        
        if ($stmt->execute()) {
            header("Location: business.php?status=success");
            exit();
        } else {
            header("Location: business.php?status=error&msg=" . urlencode($stmt->error));
            exit();
        }
        $stmt->close();
    }
}

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $message = "✅ Inquiry sent! Our B2B team will contact you shortly.";
        $status = "success";
    } elseif ($_GET['status'] === 'invalid') $message = "❌ Invalid email.";
    elseif ($_GET['status'] === 'error') $message = "❌ Error: " . htmlspecialchars($_GET['msg']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omnifood for Business - Corporate Catering Nepal</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/general.css">
    <style>
        /* --- BUSINESS SPECIFIC STYLES --- */
        .section-business-hero { background-color: #fdf2e9; padding: 4.8rem 0 9.6rem 0; }
        .business-hero-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6.4rem; align-items: center; }
        
        /* OFFERS SECTION */
        .section-offers { padding: 9.6rem 0; }
        .offer-card { background: #fff; border-radius: 11px; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.08); transition: 0.3s; border: 1px solid #eee; height: 100%; display: flex; flex-direction: column; }
        .offer-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.12); border-color: #e67e22; }
        .offer-header { background-color: #e67e22; padding: 20px; color: white; text-align: center; }
        .offer-header h3 { font-size: 2.4rem; margin-bottom: 5px; color: white; }
        .offer-body { padding: 30px; flex-grow: 1; }
        .offer-price { font-size: 3rem; font-weight: 700; color: #333; margin-bottom: 10px; text-align: center; }
        .offer-sub { font-size: 1.4rem; color: #777; text-align: center; margin-bottom: 20px; display: block; }
        
        /* FESTIVAL SECTION */
        .section-festivals { background-color: #fdf2e9; padding: 9.6rem 0; }
        .festival-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }
        .festival-box { background: white; padding: 32px; border-radius: 9px; text-align: center; border-top: 5px solid #e67e22; }
        .festival-icon { font-size: 4rem; color: #e67e22; margin-bottom: 16px; }
        
        /* FORM */
        .business-form { background-color: #e67e22; padding: 4rem; border-radius: 11px; color: white; box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
        .business-form label { display: block; font-size: 1.6rem; margin-bottom: 8px; }
        .business-form input, .business-form select, .business-form textarea { width: 100%; padding: 12px; border-radius: 6px; border: none; font-family: inherit; margin-bottom: 20px; }
        .btn-business { background-color: white; color: #e67e22; font-weight: 700; width: 100%; border: none; cursor: pointer; padding: 16px; font-size: 1.8rem; border-radius: 9px; transition: 0.3s; }
        .btn-business:hover { background-color: #fdf2e9; }

        @media (max-width: 850px) { .business-hero-grid { grid-template-columns: 1fr; } .festival-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <header class="header">
        <a href="index.php"><img class="logo" src="./img/omnifood-logo.png" alt="Omnifood Logo"></a>
        <nav class="main-nav">
            <ul class="main-nav-list">
                <li><a class="main-nav-link" href="index.php">Home</a></li>
                <li><a class="main-nav-link" href="index.php#how">How it works</a></li>
                <li><a class="main-nav-link" href="recipes.php">Recipes</a></li>
                <li><a class="main-nav-link nav-cta" href="#contact">Contact Sales</a></li>
            </ul>
        </nav>
    </header>

    <?php if (!empty($message)): ?>
    <div style="position:fixed; top:20px; left:50%; transform:translateX(-50%); background:white; padding:20px 40px; border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; border-left: 5px solid #e67e22;">
        <p style="font-size:1.6rem; margin:0;"><?php echo $message; ?></p>
        <button onclick="this.parentElement.style.display='none'" style="margin-top:10px; background:none; border:none; color:#555; cursor:pointer; font-size:1.4rem; text-decoration:underline;">Close</button>
    </div>
    <?php endif; ?>

    <main>
        <section class="section-business-hero">
            <div class="container business-hero-grid">
                <div class="hero-text-box">
                    <h1 class="heading-primary">Healthy Fuel for Your Team</h1>
                    <p class="hero-description">
                        Boost employee productivity and satisfaction with healthy, hot, and delicious Nepali meals delivered daily to your office. VAT Bill provided.
                    </p>
                    <a href="#contact" class="btn btn--full">Get a Corporate Quote</a>
                </div>
                <div class="hero-img-box">
                    <img src="./img/hero.png" alt="Office Lunch" style="width: 100%;">
                </div>
            </div>
        </section>

        <section class="section-featured">
            <div class="container center-text">
                <h2 class="heading-secondary">Why Omnifood for Business?</h2>
                <div class="grid grid--3-cols" style="margin-top: 4rem; text-align: left;">
                    <div>
                        <ion-icon name="trending-up-outline" style="font-size: 3rem; color: #e67e22; margin-bottom: 1rem;"></ion-icon>
                        <h3 class="heading-tertiary">Boost Productivity</h3>
                        <p style="font-size: 1.6rem; line-height: 1.6;">No more long lunch breaks or sleepy afternoons from heavy, oily food. Our meals are balanced for energy.</p>
                    </div>
                    <div>
                        <ion-icon name="receipt-outline" style="font-size: 3rem; color: #e67e22; margin-bottom: 1rem;"></ion-icon>
                        <h3 class="heading-tertiary">Simplified Billing</h3>
                        <p style="font-size: 1.6rem; line-height: 1.6;">Get a single consolidated monthly invoice with PAN/VAT for easy accounting.</p>
                    </div>
                    <div>
                        <ion-icon name="time-outline" style="font-size: 3rem; color: #e67e22; margin-bottom: 1rem;"></ion-icon>
                        <h3 class="heading-tertiary">On-Time Delivery</h3>
                        <p style="font-size: 1.6rem; line-height: 1.6;">We deliver hot tiffins exactly at your lunch hour, 365 days a year.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-offers">
            <div class="container center-text">
                <span class="subheading">Exclusive Offers</span>
                <h2 class="heading-secondary">Corporate Discounts</h2>
            </div>
            <div class="container grid grid--3-cols margin-bottom-md">
                <div class="offer-card">
                    <div class="offer-header"><h3>Startup Plan</h3></div>
                    <div class="offer-body">
                        <p class="offer-price">5% OFF</p>
                        <span class="offer-sub">For teams of 5-10 people</span>
                        <ul class="list">
                            <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Flexible Menu</span></li>
                            <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Weekly Billing</span></li>
                        </ul>
                    </div>
                </div>
                <div class="offer-card" style="border: 2px solid #e67e22;">
                    <div class="offer-header"><h3>Growth Plan</h3></div>
                    <div class="offer-body">
                        <p class="offer-price">10% OFF</p>
                        <span class="offer-sub">For teams of 11-50 people</span>
                        <ul class="list">
                            <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span><strong>Free Delivery</strong></span></li>
                            <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Dedicated Account Manager</span></li>
                            <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Buffet Setup Available</span></li>
                        </ul>
                    </div>
                </div>
                <div class="offer-card">
                    <div class="offer-header"><h3>Enterprise</h3></div>
                    <div class="offer-body">
                        <p class="offer-price">15% OFF</p>
                        <span class="offer-sub">For teams of 50+ people</span>
                        <ul class="list">
                            <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Custom Menu Design</span></li>
                            <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>On-site Catering Staff</span></li>
                            <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Monthly Credit Line</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-festivals">
            <div class="container center-text">
                <span class="subheading">Celebrate Together</span>
                <h2 class="heading-secondary">Nepali Festival Specials</h2>
                <p style="font-size: 1.6rem; margin-top: 1rem;">Make your office parties memorable with our festive catering.</p>
            </div>
            
            <div class="container festival-grid" style="margin-top: 4rem;">
                <div class="festival-box">
                    <ion-icon name="gift-outline" class="festival-icon"></ion-icon>
                    <h3 class="heading-tertiary">Dashain Dakshina</h3>
                    <p style="font-size: 1.6rem; color: #555;">Special <strong>Mutton & Vegetarian Thali Sets</strong> for your pre-Dashain office closing party. Bulk booking includes complimentary Dahi-Chiura.</p>
                </div>
                <div class="festival-box">
                    <ion-icon name="sparkles-outline" class="festival-icon"></ion-icon>
                    <h3 class="heading-tertiary">Tihar Sweets Box</h3>
                    <p style="font-size: 1.6rem; color: #555;">Gift your employees custom <strong>Sel Roti & Sweets Boxes</strong>. Beautifully packaged with your company logo.</p>
                </div>
                <div class="festival-box">
                    <ion-icon name="beer-outline" class="festival-icon"></ion-icon>
                    <h3 class="heading-tertiary">New Year Feast</h3>
                    <p style="font-size: 1.6rem; color: #555;">Full catering service for your Nepali New Year celebrations. Includes <strong>Sekuwa Corner</strong> and live cooking stations.</p>
                </div>
            </div>
        </section>

        <section class="section-cta" id="contact" style="padding-bottom: 9.6rem;">
            <div class="container">
                <div class="business-form">
                    <h2 class="heading-secondary" style="color: white; margin-bottom: 2rem;">Partner with us</h2>
                    <form method="POST">
                        <div class="grid grid--2-cols">
                            <div>
                                <label for="company_name">Company Name</label>
                                <input type="text" id="company_name" name="company_name" placeholder="e.g. ABC Tech Pvt Ltd" required>
                            </div>
                            <div>
                                <label for="contact_person">Contact Person</label>
                                <input type="text" id="contact_person" name="contact_person" placeholder="HR Manager Name" required>
                            </div>
                        </div>
                        
                        <div class="grid grid--2-cols">
                            <div>
                                <label for="email">Work Email</label>
                                <input type="email" id="email" name="email" placeholder="hr@company.com" required>
                            </div>
                            <div>
                                <label for="employees">No. of Employees</label>
                                <select id="employees" name="employees">
                                    <option value="1-10">1-10</option>
                                    <option value="11-50">11-50</option>
                                    <option value="50-100">50-100</option>
                                    <option value="100+">100+</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="requirements">Specific Requirements</label>
                            <textarea id="requirements" name="requirements" rows="3" placeholder="Lunch daily? Just for Dashain party?"></textarea>
                        </div>

                        <button type="submit" class="btn-business">Request Call Back</button>
                    </form>
                </div>
            </div>
        </section>

    </main>

    <footer class="footer">
        <div class="container grid grid--footer">
            <div class="logo-col">
                <a href="#" class="footer-logo"><img class="logo" src="./img/omnifood-logo.png" alt="Omnifood Logo"></a>
                <p class="copyright">Copyright &copy; 2025 by Omnifood Nepal.</p>
            </div>
            <div class="address-col">
                <p class="footer-heading">Contact</p>
                <address class="contacts">
                    <p class="address">Kathmandu, Nepal</p>
                    <p><a class="footer-link" href="tel:9800000000">980-000-0000</a></p>
                </address>
            </div>
            <nav class="nav-col">
                <p class="footer-heading">Company</p>
                <ul class="footer-nav">
                    <li><a class="footer-link" href="index.php">About Omnifood</a></li>
                    <li><a class="footer-link" href="business.php" style="color: #e67e22; font-weight: bold;">For Business</a></li>
                    <li><a class="footer-link" href="index.php#chefs">Cooking partners</a></li>
                    <li><a class="footer-link" href="#">Careers</a></li>
                </ul>
            </nav>
            <nav class="nav-col">
                <p class="footer-heading">Resources</p>
                <ul class="footer-nav">
                    <li><a class="footer-link" href="recipes.php">Recipe Directory</a></li>
                    <li><a class="footer-link" href="privacy.php">Privacy & terms</a></li>
                </ul>
            </nav>
        </div>
    </footer>

</body>
</html>