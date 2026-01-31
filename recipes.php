<?php
session_start();
include 'connect.php';

// --- 1. HANDLE ORDER SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize Inputs
    $name = isset($_POST['full_name']) ? htmlspecialchars(trim($_POST['full_name'])) : "Guest";
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : "no-email@test.com";
    $order_items = isset($_POST['hidden_order_items']) ? htmlspecialchars(trim($_POST['hidden_order_items'])) : "";
    
    // Capture Location & Phone
    $location = isset($_POST['location_coords']) ? htmlspecialchars(trim($_POST['location_coords'])) : "";
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : "";

    // --- HYBRID FIX: Append details to Order Text as a BACKUP ---
    // This ensures Admin sees it in the "Order Items" column even if the phone column is hidden
    if(!empty($phone)) {
        $order_items .= " | [Ph: $phone]";
    }
    if(!empty($location)) {
        $order_items .= " | [Loc: $location]";
    }

    $source = "Recipe Page";
    $status = 'new';
    $payment = 'pending';

    // --- INSERT INTO DATABASE ---
    // We try to insert into 'phone_number' column. 
    // If your DB doesn't have that column yet, the text append above saves you.
    // If your DB DOES have it, this saves it properly.
    
    // Check if phone_number column exists in your specific setup or just use the 6 param version if unsure
    // Assuming you created the column 'phone_number':
    $stmt = $conn->prepare("INSERT INTO cafe (name, email, phone_number, order_items, source, payment_status, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $email, $phone, $order_items, $source, $payment, $status);
    
    if ($stmt->execute()) {
        header("Location: recipes.php?msg=ordered");
        exit();
    } else {
        // Fallback for debugging
        echo "Error: " . $conn->error;
    }
}

// --- 2. CHECK PERMISSIONS (View Recipe Button) ---
$unlocked = false; // Default: Locked
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT payment_status FROM cafe WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (strtolower($row['payment_status']) === 'paid') {
            $unlocked = true; // Unlock ONLY if paid
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Nepali Cookbook - Omnifood</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/general.css">
    <style>
        /* --- PAGE LAYOUT & RESET --- */
        body { background-color: #fdf2e9; position: relative; }
        header { background-color: #fff; }

        /* --- FEATURED SECTION (TEXT LOGOS) --- */
        .section-featured { padding: 4.8rem 0 3.2rem 0; }
        .heading-featured { 
            font-size: 1.4rem; text-transform: uppercase; letter-spacing: 0.75px; 
            font-weight: 500; text-align: center; margin-bottom: 2.4rem; color: #888; 
        }
        .logos { 
            display: flex; justify-content: space-around; align-items: center; 
            flex-wrap: wrap; gap: 30px; 
        }
        .logo-text {
            font-size: 1.8rem; font-weight: 700; color: #888;
            text-transform: uppercase; letter-spacing: 1px;
            cursor: default; transition: all 0.3s;
        }
        .logo-text:hover { color: #e67e22; transform: scale(1.05); }

        /* --- CHEFS SECTION --- */
        .section-chefs { padding: 9.6rem 0; background-color: #fae5d3; } 
        .chefs-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; text-align: center; }
        
        .chef-profile img { 
            width: 15rem; height: 15rem; border-radius: 50%; margin-bottom: 2rem; 
            border: 5px solid #fff; transition: 0.3s; object-fit: cover;
        }
        .chef-profile:hover img { transform: scale(1.05); border-color: #e67e22; }
        .chef-name { font-size: 2rem; font-weight: 700; color: #333; margin-bottom: 0.5rem; }
        .chef-role { font-size: 1.4rem; color: #e67e22; text-transform: uppercase; font-weight: 500; }

        /* --- RECIPE GRID --- */
        .section-meals { padding: 9.6rem 0; }
        .recipe-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 48px; align-items: stretch; }

        .recipe-card {
            background: white; border-radius: 12px; overflow: hidden;
            box-shadow: 0 15px 30px rgba(0,0,0,0.08); transition: all 0.3s;
            display: flex; flex-direction: column; height: 100%;
        }
        .recipe-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.15); }
        
        .recipe-img-box { position: relative; height: 240px; overflow: hidden; flex-shrink: 0; }
        .recipe-img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .recipe-card:hover .recipe-img { transform: scale(1.1); }
        .nepali-badge {
            position: absolute; top: 15px; right: 15px; 
            background: #e67e22; color: white; padding: 5px 12px; 
            border-radius: 100px; font-size: 1.2rem; font-weight: 700; text-transform: uppercase;
        }

        .recipe-content { padding: 32px; display: flex; flex-direction: column; flex-grow: 1; gap: 15px; }
        .recipe-title { font-size: 2.4rem; font-weight: 700; color: #333; margin: 0; }
        .recipe-desc { font-size: 1.6rem; color: #555; line-height: 1.6; margin-bottom: auto; }
        
        .card-footer { margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px; }
        .chef-mini { display: flex; align-items: center; margin-bottom: 15px; }
        .chef-mini img { width: 40px; height: 40px; border-radius: 50%; margin-right: 12px; border: 2px solid #e67e22; object-fit: cover; }
        .chef-info h4 { font-size: 1.4rem; color: #333; margin: 0; }
        .chef-info span { font-size: 1.1rem; color: #888; text-transform: uppercase; font-weight: 600; }

        /* BUTTONS ACTIONS CONTAINER */
        .meal-actions { display: flex; gap: 10px; }

        .btn-view {
            flex: 1; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background-color: #333; color: #fff; padding: 10px; border-radius: 9px; 
            font-weight: 600; font-size: 1.4rem; font-family: inherit; transition: 0.3s; 
        }
        .btn-view:hover { background-color: #555; }
        
        /* Locked Button Style */
        .btn-view.locked { background-color: #ccc; cursor: not-allowed; }

        /* NEW ADD TO CART BUTTON */
        .btn-add {
            flex: 1; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background-color: #e67e22; color: #fff; padding: 10px; border-radius: 9px; 
            font-weight: 600; font-size: 1.4rem; font-family: inherit; transition: 0.3s; 
        }
        .btn-add:hover { background-color: #cf711f; }

        /* --- MODAL CSS --- */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(4px); }
        .modal-content { background: white; width: 95%; max-width: 900px; max-height: 90vh; border-radius: 12px; overflow-y: auto; position: relative; padding: 40px; animation: slideUp 0.3s ease-out; box-shadow: 0 20px 50px rgba(0,0,0,0.2); }
        @keyframes slideUp { from{transform: translateY(40px); opacity: 0;} to{transform: translateY(0); opacity: 1;} }
        
        .close-btn { 
            position: absolute; top: 20px; right: 25px; 
            font-size: 4rem; cursor: pointer; color: #555; 
            line-height: 0.6; transition: 0.2s; z-index: 1000;
        }
        .close-btn:hover { color: #e67e22; }

        .modal-header { border-bottom: 2px solid #fdf2e9; padding-bottom: 20px; margin-bottom: 30px; }
        .modal-title { font-size: 3.2rem; color: #333; margin-bottom: 10px; }
        .modal-subtitle { font-size: 1.8rem; color: #e67e22; font-weight: 500; }

        .recipe-details { display: grid; grid-template-columns: 1fr 1.5fr; gap: 40px; }
        .ing-list { list-style: none; padding: 0; }
        .ing-list li { font-size: 1.8rem; padding: 10px 0; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 10px; color: #444; }
        .ing-list li ion-icon { color: #e67e22; }

        .step-box { background-color: #fdf2e9; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .step-box h4 { font-size: 1.4rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .step-box p { font-size: 1.6rem; line-height: 1.6; color: #333; }

        /* CART STYLES */
        #sticky-cart { display: none; position: fixed; bottom: 0; left: 0; width: 100%; background: white; border-top: 3px solid #e67e22; padding: 20px 40px; justify-content: space-between; align-items: center; z-index: 1000; box-shadow: 0 -5px 20px rgba(0,0,0,0.1); box-sizing: border-box; }
        .cart-list { list-style: none; padding: 0; margin: 20px 0; max-height: 200px; overflow-y: auto; text-align: left;}
        .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #eee; font-size: 1.6rem; }
        .btn-remove { background: #ffe3e3; color: #e03131; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 1.4rem; margin-left: 15px; }
        
        /* LOCATION BUTTON */
        .btn-location { background-color: #2ecc71; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; width: 100%; margin: 15px 0; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 1.4rem; }
        .btn-location:disabled { background-color: #ccc; cursor: not-allowed; }

        @media (max-width: 950px) { .recipe-grid { grid-template-columns: 1fr 1fr; } .chefs-grid { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 700px) { .recipe-details { grid-template-columns: 1fr; } } 
        @media (max-width: 600px) { 
            .recipe-grid { grid-template-columns: 1fr; } 
            .chefs-grid { grid-template-columns: 1fr; } 
            .logos { flex-direction: column; gap: 15px; } 
        }
    </style>
</head>
<body>

    <header class="header">
        <a href="index.php"><img class="logo" src="./img/omnifood-logo.png" alt="Omnifood Logo"></a>
        <nav class="main-nav">
            <ul class="main-nav-list">
                <li><a class="main-nav-link" href="index.php">Home</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a class="main-nav-link nav-cta" href="signin.php?logout=true">Logout</a></li>
                <?php else: ?>
                    <li><a class="main-nav-link nav-cta" href="signin.php">Log in</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        
        <section class="section-featured">
            <div class="container">
                <h2 class="heading-featured">Featured in Nepali Media</h2>
                <div class="logos">
                    <div class="logo-text">The Kathmandu Post</div>
                    <div class="logo-text">Foodmandu</div>
                    <div class="logo-text">OnlineKhabar</div>
                    <div class="logo-text">The Himalayan Times</div>
                    <div class="logo-text">Routine of Nepal Banda</div>
                </div>
            </div>
        </section>

        <section class="section-chefs">
            <div class="container center-text">
                <span class="subheading">The Team</span>
                <h2 class="heading-secondary">Meet our Local Chefs</h2>
                <p style="font-size: 1.6rem; margin-top: 1rem; margin-bottom: 4rem;">The masters behind your daily meals.</p>
            </div>
            <div class="container chefs-grid">
                <div class="chef-profile">
                    <img src="./img/chef/Ram Sharan Thapa.jpg" alt="Ram Sharan Thapa">
                    <div class="chef-name">Ram Sharan Thapa</div>
                    <div class="chef-role">Momo Expert</div>
                </div>
                <div class="chef-profile">
                    <img src="./img/chef/Bishal Gurung.webp" alt="Bishal Gurung">
                    <div class="chef-name">Bishal Gurung</div>
                    <div class="chef-role">Thakali Master</div>
                </div>
                <div class="chef-profile">
                    <img src="./img/chef/Dorje Sherpa.jpeg" alt="Dorje Sherpa">
                    <div class="chef-name">Dorje Sherpa</div>
                    <div class="chef-role">Himalayan Soups</div>
                </div>
                <div class="chef-profile">
                    <img src="./img/chef/Rahul Babu Shrestha.avif" alt="Rahul Babu Shrestha">
                    <div class="chef-name">Rahul Babu Shrestha</div>
                    <div class="chef-role">Newari Cuisine</div>
                </div>
            </div>
        </section>

        <section class="section-meals">
            <div class="container center-text">
                <span class="subheading">Premium Content</span>
                <h2 class="heading-secondary">The Recipe Directory</h2>
                <?php if(!$unlocked): ?>
                <p style="font-size:1.6rem; color:#e67e22; margin-top:1rem; background: #fff4e6; display:inline-block; padding:10px 20px; border-radius:50px;">
                    <ion-icon name="lock-closed-outline" style="vertical-align:middle;"></ion-icon> 
                    <strong>Note:</strong> Recipes are locked for guests. Upgrade to Complete Plan to view.
                </p>
                <?php endif; ?>
            </div>
            <div class="container recipe-grid">
                
                <div class="recipe-card">
                    <div class="recipe-img-box"><img src="./img/meals/meal-1.jpg" class="recipe-img" alt="Jhol Momo"><span class="nepali-badge">🇳🇵 Authentic</span></div>
                    <div class="recipe-content">
                        <h3 class="recipe-title">Secret Jhol Momos</h3>
                        <p class="recipe-desc">The exact recipe for our famous sesame-tomato soup and juicy dumpling filling.</p>
                        <div class="card-footer">
                            <div class="chef-mini"><img src="./img/chef/Ram Sharan Thapa.jpg" alt="Chef Ram"><div class="chef-info"><h4>Ram Sharan</h4><span>Momo Expert</span></div></div>
                            <div class="meal-actions">
                                <?php if($unlocked): ?>
                                <button class="btn-view" onclick="openModal('momo')"><ion-icon name="restaurant-outline"></ion-icon> View</button>
                                <?php else: ?>
                                <button class="btn-view locked" onclick="alert('🔒 Locked! Please upgrade to Complete Plan to view recipes.')"><ion-icon name="lock-closed-outline"></ion-icon> Locked</button>
                                <?php endif; ?>
                                <button class="btn-add" onclick="addToCart('Jhol Momos', 350)"><ion-icon name="cart-outline"></ion-icon> Add Rs.350</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="recipe-card">
                    <div class="recipe-img-box"><img src="./img/meals/Thakali Dal Bhat.png" class="recipe-img" alt="Thakali Set"><span class="nepali-badge">🇳🇵 Mustang Style</span></div>
                    <div class="recipe-content">
                        <h3 class="recipe-title">Thakali Dal Bhat</h3>
                        <p class="recipe-desc">Learn how to temper lentils with Jimbu herb and ferment your own Gundruk.</p>
                        <div class="card-footer">
                            <div class="chef-mini"><img src="./img/chef/Bishal Gurung.webp" alt="Chef Bishal"><div class="chef-info"><h4>Bishal Gurung</h4><span>Thakali Master</span></div></div>
                            <div class="meal-actions">
                                <?php if($unlocked): ?>
                                <button class="btn-view" onclick="openModal('dalbhat')"><ion-icon name="restaurant-outline"></ion-icon> View</button>
                                <?php else: ?>
                                <button class="btn-view locked" onclick="alert('🔒 Locked! Please upgrade to Complete Plan to view recipes.')"><ion-icon name="lock-closed-outline"></ion-icon> Locked</button>
                                <?php endif; ?>
                                <button class="btn-add" onclick="addToCart('Dal Bhat', 450)"><ion-icon name="cart-outline"></ion-icon> Add Rs.450</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="recipe-card">
                    <div class="recipe-img-box"><img src="./img/meals/Himalayan Thukpa.jpeg" class="recipe-img" alt="Thukpa"><span class="nepali-badge">🇳🇵 Warm</span></div>
                    <div class="recipe-content">
                        <h3 class="recipe-title">Himalayan Thukpa</h3>
                        <p class="recipe-desc">The ultimate comfort food. Hand-pulled noodles in a rich bone broth.</p>
                        <div class="card-footer">
                            <div class="chef-mini"><img src="./img/chef/Dorje Sherpa.jpeg" alt="Chef Dorje"><div class="chef-info"><h4>Dorje Sherpa</h4><span>Soup Master</span></div></div>
                            <div class="meal-actions">
                                <?php if($unlocked): ?>
                                <button class="btn-view" onclick="openModal('thukpa')"><ion-icon name="restaurant-outline"></ion-icon> View</button>
                                <?php else: ?>
                                <button class="btn-view locked" onclick="alert('🔒 Locked! Please upgrade to Complete Plan to view recipes.')"><ion-icon name="lock-closed-outline"></ion-icon> Locked</button>
                                <?php endif; ?>
                                <button class="btn-add" onclick="addToCart('Thukpa', 300)"><ion-icon name="cart-outline"></ion-icon> Add Rs.300</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="recipe-card">
                    <div class="recipe-img-box"><img src="./img/meals/Newari Samay Baji.png" class="recipe-img" alt="Newari Set"><span class="nepali-badge">🇳🇵 Spicy</span></div>
                    <div class="recipe-content">
                        <h3 class="recipe-title">Newari Samay Baji</h3>
                        <p class="recipe-desc">A festive platter with beaten rice, spicy choila, and black soybeans.</p>
                        <div class="card-footer">
                            <div class="chef-mini"><img src="./img/chef/Rahul Babu Shrestha.avif" alt="Chef Rahul"><div class="chef-info"><h4>Rahul Babu</h4><span>Newari Cuisine</span></div></div>
                            <div class="meal-actions">
                                <?php if($unlocked): ?>
                                <button class="btn-view" onclick="openModal('newari')"><ion-icon name="restaurant-outline"></ion-icon> View</button>
                                <?php else: ?>
                                <button class="btn-view locked" onclick="alert('🔒 Locked! Please upgrade to Complete Plan to view recipes.')"><ion-icon name="lock-closed-outline"></ion-icon> Locked</button>
                                <?php endif; ?>
                                <button class="btn-add" onclick="addToCart('Samay Baji', 500)"><ion-icon name="cart-outline"></ion-icon> Add Rs.500</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="recipe-card">
                    <div class="recipe-img-box"><img src="./img/meals/Dharan Sekuwa.jpg" class="recipe-img" alt="Sekuwa"><span class="nepali-badge">🇳🇵 BBQ</span></div>
                    <div class="recipe-content">
                        <h3 class="recipe-title">Dharan Sekuwa</h3>
                        <p class="recipe-desc">Our secret marinade spice blend revealed for the first time.</p>
                        <div class="card-footer">
                            <div class="chef-mini"><img src="./img/chef/Ram Sharan Thapa.jpg" alt="Chef Ram"><div class="chef-info"><h4>Ram Sharan</h4><span>Grill Master</span></div></div>
                            <div class="meal-actions">
                                <?php if($unlocked): ?>
                                <button class="btn-view" onclick="openModal('sekuwa')"><ion-icon name="restaurant-outline"></ion-icon> View</button>
                                <?php else: ?>
                                <button class="btn-view locked" onclick="alert('🔒 Locked! Please upgrade to Complete Plan to view recipes.')"><ion-icon name="lock-closed-outline"></ion-icon> Locked</button>
                                <?php endif; ?>
                                <button class="btn-add" onclick="addToCart('Sekuwa', 400)"><ion-icon name="cart-outline"></ion-icon> Add Rs.400</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="recipe-card">
                    <div class="recipe-img-box"><img src="./img/meals/Sel Roti & Aloo.jpg" class="recipe-img" alt="Sel Roti"><span class="nepali-badge">🇳🇵 Sweet</span></div>
                    <div class="recipe-content">
                        <h3 class="recipe-title">Sel Roti & Aloo</h3>
                        <p class="recipe-desc">Master the art of pouring perfect ring-shaped rice bread.</p>
                        <div class="card-footer">
                            <div class="chef-mini"><img src="./img/chef/Rahul Babu Shrestha.avif" alt="Chef Rahul"><div class="chef-info"><h4>Rahul Babu</h4><span>Festival Special</span></div></div>
                            <div class="meal-actions">
                                <?php if($unlocked): ?>
                                <button class="btn-view" onclick="openModal('selroti')"><ion-icon name="restaurant-outline"></ion-icon> View</button>
                                <?php else: ?>
                                <button class="btn-view locked" onclick="alert('🔒 Locked! Please upgrade to Complete Plan to view recipes.')"><ion-icon name="lock-closed-outline"></ion-icon> Locked</button>
                                <?php endif; ?>
                                <button class="btn-add" onclick="addToCart('Sel Roti', 200)"><ion-icon name="cart-outline"></ion-icon> Add Rs.200</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <div id="recipeModal" class="modal-overlay">
        <div class="modal-content">
            <span class="close-btn" onclick="closeRecipeModal()">&times;</span>
            <div class="modal-header">
                <h2 class="modal-title" id="m-title"></h2>
                <p class="modal-subtitle" id="m-chef"></p>
            </div>
            <div class="recipe-details">
                <div>
                    <h3 class="heading-tertiary">Ingredients</h3>
                    <ul class="ing-list" id="m-ing"></ul>
                </div>
                <div>
                    <h3 class="heading-tertiary">Instructions</h3>
                    <div id="m-steps"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="sticky-cart">
        <span style="font-size: 1.6rem; font-weight: 700; color: #333;">Total: Rs. <span id="sticky-total">0</span></span>
        <button class="btn-add" style="flex:0; padding:10px 30px;" onclick="toggleCartModal()">View Cart</button>
    </div>

    <div id="cartModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 500px;">
            <span class="close-btn" onclick="toggleCartModal()">&times;</span>
            <h2 class="heading-secondary" style="margin-bottom:20px;">Your Cart</h2>
            
            <ul id="cart-list-container" class="cart-list"></ul>
            
            <p style="font-size:1.8rem; font-weight:bold; margin-bottom:20px; color:#e67e22;">Total: Rs. <span id="modal-cart-total">0</span></p>

            <form action="recipes.php" method="POST" id="checkout-form" style="display:none; text-align:left;">
                
                <label style="font-size:1.4rem; color:#555;">Full Name</label>
                <input type="text" name="full_name" required style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ddd; border-radius:5px;">
                
                <label style="font-size:1.4rem; color:#555;">Email</label>
                <input type="email" name="email" required style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ddd; border-radius:5px;">

                <label style="font-size:1.4rem; color:#555;">Phone Number</label>
                <input type="tel" name="phone" placeholder="98XXXXXXXX" required style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ddd; border-radius:5px;">

                <input type="hidden" name="location_coords" id="location_coords">
                <input type="hidden" name="hidden_order_items" id="hidden_order_items">

                <button type="button" class="btn-location" onclick="getKathmanduLocation()">
                    <ion-icon name="location-outline"></ion-icon> Verify Location (Kathmandu Only)
                </button>
                <p id="loc-status" style="font-size:1.4rem; color:red; margin-bottom:10px; text-align:center; font-weight:bold;"></p>

                <button type="submit" class="btn-add" id="btn-submit-order" disabled style="background-color:#ccc; width:100%;">Confirm Order</button>
            </form>
            
            <button id="btn-show-checkout" class="btn-add" onclick="showCheckout()" style="width:100%;">Proceed to Checkout</button>
        </div>
    </div>

    <script>
        // --- RECIPE DATA & MODAL LOGIC ---
        const recipes = {
            'momo': { title: "Secret Jhol Momos", chef: "By Chef Ram Sharan Thapa", ingredients: ["500g Minced Meat", "Ginger Garlic Paste", "Timur (Szechuan Pepper)", "Momo Masala", "Flour Dough", "Roasted Tomatoes", "Sesame Seeds"], steps: ["Mix meat with spices and let it rest for 30 mins.", "Roll dough into small circles.", "Wrap the filling in the dough.", "Steam for 15 mins.", "Blend roasted tomatoes and sesame for the sauce."] },
            'dalbhat': { title: "Thakali Dal Bhat", chef: "By Chef Bishal Gurung", ingredients: ["Black Lentils (Kalo Dal)", "Rice", "Jimbu (Himalayan Herb)", "Ghee", "Mustard Greens", "Gundruk"], steps: ["Pressure cook lentils for 20 mins.", "Temper with Ghee and Jimbu.", "Cook rice until fluffy.", "Stir fry greens with garlic.", "Serve hot on a brass plate."] },
            'thukpa': { title: "Himalayan Thukpa", chef: "By Chef Dorje Sherpa", ingredients: ["Hand-pulled Noodles", "Bone Broth", "Veggies (Carrot, Cabbage)", "Soy Sauce", "Cumin Powder", "Green Chili"], steps: ["Boil the bone broth for 4 hours.", "Boil noodles separately.", "Sauté veggies in a wok.", "Add broth to veggies.", "Mix in noodles and serve hot."] },
            'newari': { title: "Newari Samay Baji", chef: "By Chef Rahul Babu Shrestha", ingredients: ["Beaten Rice (Chiura)", "Grilled Meat (Choila)", "Ginger & Garlic", "Mustard Oil", "Black Soybeans", "Boiled Egg"], steps: ["Marinate grilled meat with spices and mustard oil.", "Fry soybeans until crunchy.", "Mix spices with beaten rice (optional).", "Arrange all items beautifully on a leaf plate."] },
            'sekuwa': { title: "Dharan Sekuwa", chef: "By Chef Ram Sharan Thapa", ingredients: ["Goat or Pork Cubes", "Mustard Oil", "Cumin Powder", "Coriander Powder", "Timur", "Lemon Juice"], steps: ["Mix all spices with mustard oil.", "Marinate meat for at least 4 hours.", "Skewer the meat tightly.", "Grill over charcoal fire until slightly charred."] },
            'selroti': { title: "Sel Roti & Aloo", chef: "By Chef Rahul Babu Shrestha", ingredients: ["Rice Flour", "Sugar", "Ghee", "Cardamom Powder", "Cooking Oil", "Water"], steps: ["Mix rice flour, sugar, ghee, and water to make a semi-liquid batter.", "Let the batter rest for 1 hour.", "Pour batter in a ring shape into hot oil.", "Fry until golden brown on both sides."] }
        };

        const modal = document.getElementById('recipeModal');
        const mTitle = document.getElementById('m-title');
        const mChef = document.getElementById('m-chef');
        const mIng = document.getElementById('m-ing');
        const mSteps = document.getElementById('m-steps');

        window.openModal = function(key) {
            const data = recipes[key]; 
            if(!data) return;
            mTitle.innerText = data.title; 
            mChef.innerText = data.chef;
            mIng.innerHTML = ""; 
            data.ingredients.forEach(i => mIng.innerHTML += `<li><ion-icon name="checkmark-outline"></ion-icon> ${i}</li>`);
            mSteps.innerHTML = ""; 
            data.steps.forEach((s, i) => mSteps.innerHTML += `<div class="step-box"><h4>Step ${i+1}</h4><p>${s}</p></div>`);
            modal.style.display = 'flex';
        };

        window.closeRecipeModal = function() {
            modal.style.display = 'none';
        }

        // --- CART & LOCATION LOGIC ---
        let cart = [];

        window.addToCart = function(name, price) {
            cart.push({name, price});
            updateCartUI();
        }

        window.removeFromCart = function(index) {
            cart.splice(index, 1);
            updateCartUI();
        }

        function updateCartUI() {
            const list = document.getElementById('cart-list-container');
            const totalEl = document.getElementById('modal-cart-total');
            const stickyTotal = document.getElementById('sticky-total');
            const stickyCart = document.getElementById('sticky-cart');
            
            list.innerHTML = "";
            let sum = 0;

            if (cart.length > 0) {
                stickyCart.style.display = 'flex';
                cart.forEach((item, idx) => {
                    sum += item.price;
                    list.innerHTML += `
                        <li class="cart-item">
                            <span>${item.name}</span>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <strong>Rs. ${item.price}</strong>
                                <button class="btn-remove" onclick="removeFromCart(${idx})">X</button>
                            </div>
                        </li>`;
                });
            } else {
                stickyCart.style.display = 'none';
                list.innerHTML = "<li style='text-align:center; padding:10px; color:#888;'>Cart is empty</li>";
            }
            
            totalEl.innerText = sum;
            stickyTotal.innerText = sum;

            // Update hidden input for form
            let orderString = cart.map(i => i.name + " (" + i.price + ")").join(", ");
            orderString += " | Total: Rs." + sum;
            document.getElementById('hidden_order_items').value = orderString;
        }

        window.toggleCartModal = function() {
            const cm = document.getElementById('cartModal');
            cm.style.display = (cm.style.display === 'flex') ? 'none' : 'flex';
        }

        window.showCheckout = function() {
            if(cart.length === 0) { alert("Cart is empty!"); return; }
            document.getElementById('btn-show-checkout').style.display = 'none';
            document.getElementById('checkout-form').style.display = 'block';
        }

        // --- LOCATION VERIFICATION (KATHMANDU) ---
        window.getKathmanduLocation = function() {
            const status = document.getElementById('loc-status');
            const btn = document.getElementById('btn-submit-order');

            if (!navigator.geolocation) {
                status.innerText = "Geolocation not supported.";
                return;
            }

            status.innerText = "Locating...";
            status.style.color = "#e67e22";

            navigator.geolocation.getCurrentPosition((position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Kathmandu Approximate Bounds
                const isKathmandu = (lat >= 27.60 && lat <= 27.85) && (lng >= 85.20 && lng <= 85.55);

                if (isKathmandu) {
                    document.getElementById('location_coords').value = lat + "," + lng;
                    status.innerText = "Location Verified: Kathmandu ✅";
                    status.style.color = "green";
                    btn.disabled = false;
                    btn.style.backgroundColor = "#e67e22";
                } else {
                    status.innerText = "Out of delivery zone. Kathmandu Only ❌";
                    status.style.color = "red";
                    btn.disabled = true;
                    btn.style.backgroundColor = "#ccc";
                }

            }, () => {
                status.innerText = "Location Access Denied.";
                status.style.color = "red";
            });
        }

        // Close modals on outside click
        window.onclick = function(event) {
            if (event.target == modal) { closeRecipeModal(); }
            if (event.target == document.getElementById('cartModal')) { toggleCartModal(); }
        }
    </script>
    <footer class="footer"><div class="container" style="text-align: center;"><p class="copyright">Copyright &copy; 2025 by Omnifood Nepal.</p></div></footer>
</body>
</html>