<?php
session_start();
include 'connect.php';

// --- 1. HANDLE ORDER SUBMISSION (Logged in users via Cart) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION['user_id'])) {
    $order_items = htmlspecialchars($_POST['hidden_order_items']);
    $user_id = $_SESSION['user_id'];
    
    // CAPTURE PHONE & LOCATION
    $location = isset($_POST['location_coords']) ? htmlspecialchars(trim($_POST['location_coords'])) : "";
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : "";
    
    // Append details to Order String
    if(!empty($phone)) {
        $order_items .= " | [Ph: $phone]";
    }
    if(!empty($location)) { 
        $order_items .= " | [Loc: $location]"; 
    }
    
    // UPDATE user's row (save to phone_number column AND order_items)
    $stmt = $conn->prepare("UPDATE cafe SET order_items = ?, phone_number = ?, payment_status = 'pending', status = 'new' WHERE id = ?");
    $stmt->bind_param("ssi", $order_items, $phone, $user_id);
    
    if ($stmt->execute()) {
        header("Location: index.php?status=success");
        exit();
    }
}
?>

<?php
include 'connect.php';

$message = "";
$status = "";

// --- 2. HANDLE SIGN UP FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_SESSION['user_id'])) {
    $name   = htmlspecialchars(trim($_POST['full_name']));
    $email  = htmlspecialchars(trim($_POST['email']));
    $source = htmlspecialchars(trim($_POST['select_where']));
    $phone  = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : "";
    
    $order_items = isset($_POST['order_items']) ? htmlspecialchars(trim($_POST['order_items'])) : "";
    if(empty($order_items)) { $order_items = "Newsletter Signup"; }
    
    // Also append phone to order items text
    if(!empty($phone)) { $order_items .= " | [Ph: $phone]"; }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.php?status=invalid");
        exit();
    } else {
        // INSERT into DB with phone_number column
        $stmt = $conn->prepare("INSERT INTO cafe (name, email, phone_number, order_items, source) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $order_items, $source);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['user_name'] = $name;
            header("Location: index.php?status=success");
            exit();
        } else {
            header("Location: index.php?status=error&msg=" . urlencode($stmt->error));
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Omnifood Nepal &mdash; Never cook again!</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/general.css">
    
    <style>
      /* BUTTONS */
      .btn--sm { display: block; width: 100%; background-color: #e67e22; color: #fff; border: none; border-radius: 9px; padding: 1rem; font-size: 1.4rem; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
      .btn--sm:hover { background-color: #cf711f; }
      .btn--outline-sm { display: block; width: 100%; background-color: #fff; color: #555; border: 2px solid #e67e22; border-radius: 9px; padding: 0.8rem; font-size: 1.4rem; font-weight: 600; cursor: pointer; transition: all 0.3s; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; }
      .btn--outline-sm:hover { background-color: #fdf2e9; color: #cf711f; box-shadow: inset 0 0 0 1px #cf711f; }
      .meal-actions { display: flex; gap: 1.2rem; margin-top: 1.6rem; }
      .btn--sm, .btn--outline-sm { flex: 1; }
      
      /* CART & MODAL */
      .cart-list { list-style: none; padding: 0; margin: 20px 0; max-height: 300px; overflow-y: auto; }
      .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #eee; font-size: 1.6rem; }
      .btn-remove { background: #ffe3e3; color: #e03131; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 1.4rem; margin-left: 15px; transition: 0.2s; }
      .cart-empty-msg { text-align: center; font-size: 1.8rem; color: #888; padding: 20px; }
      
      .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: flex; justify-content: center; align-items: center; z-index: 9999; backdrop-filter: blur(5px); }
      .modal-content { background: white; padding: 40px; border-radius: 12px; text-align: center; max-width: 400px; animation: popUp 0.3s ease-out; position: relative; }
      @keyframes popUp { from {transform: scale(0.8); opacity: 0;} to {transform: scale(1); opacity: 1;} }
      .modal-success { border-top: 5px solid #2ecc71; }
      .modal-error { border-top: 5px solid #e74c3c; }
      .close-btn { position: absolute; top: 15px; right: 20px; font-size: 3rem; cursor: pointer; color: #777; }
      
      #sticky-cart { display: none; position: fixed; bottom: 0; left: 0; width: 100%; background: white; border-top: 3px solid #e67e22; padding: 20px 40px; justify-content: space-between; align-items: center; z-index: 1000; box-shadow: 0 -5px 20px rgba(0,0,0,0.1); box-sizing: border-box; }
      .cart-btn { background-color: #e67e22; color: white; padding: 12px 24px; border: none; border-radius: 9px; font-weight: 600; cursor: pointer; font-size: 1.6rem; transition: 0.3s; }
      
      .chefs-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; margin-top: 30px; }
      .chef-card { text-align: center; }
      .chef-img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.1); margin-bottom: 15px; transition: transform 0.3s; }
      .chef-card:hover .chef-img { transform: scale(1.05); border-color: #e67e22; }
      .chef-name { font-size: 1.8rem; font-weight: 700; color: #333; margin-bottom: 5px; }
      .chef-role { font-size: 1.4rem; color: #777; font-weight: 500; text-transform: uppercase; }
      .logo-text { font-size: 2.2rem; font-weight: 700; color: #888; opacity: 0.6; margin: 0; text-transform: uppercase; transition: 0.3s; cursor: default; }
      
      .btn-location { background-color: #2ecc71; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; width: 100%; margin-bottom: 15px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 1.4rem; }
      .btn-location:disabled { background-color: #ccc; cursor: not-allowed; }

      @media (max-width: 900px) { .chefs-grid { grid-template-columns: 1fr 1fr; } }
      @media (max-width: 550px) { .chefs-grid { grid-template-columns: 1fr; } }
    </style>
  </head>
  <body>

    <?php if (!empty($message)): ?>
    <div class="modal-overlay" id="modalOverlay">
      <div class="modal-content <?php echo ($status === 'success') ? 'modal-success' : 'modal-error'; ?>">
        <h2 class="heading-secondary" style="margin-bottom: 1rem;"><?php echo ($status === 'success') ? 'Success!' : 'Oops!'; ?></h2>
        <p style="font-size:1.6rem; margin-bottom: 2rem; color: #555;"><?php echo $message; ?></p>
        <button class="btn btn--form" onclick="closeModal()">Close</button>
      </div>
    </div>
    <?php endif; ?>

    <header class="header">
      <a href="index.php"><img class="logo" src="./img/omnifood-logo.png" alt="Omnifood Logo"></a>
      <nav class="main-nav">
        <ul class="main-nav-list">
            <li><a class="main-nav-link" href="index.php">Home</a></li>
            <li><a class="main-nav-link" href="recipes.php">Recipes</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><span style="font-size:1.6rem; color:#555;">Hi, <?php echo $_SESSION['user_name']; ?></span></li>
                <li><a class="main-nav-link nav-cta" href="signin.php?logout=true">Logout</a></li>
            <?php else: ?>
                <li><a class="main-nav-link nav-cta" href="signin.php">Log in</a></li>
            <?php endif; ?>
        </ul>
      </nav>
    </header>

    <main>
      <section class="section-hero">
        <div class="hero">
          <div class="hero-text-box">
            <h1 class="heading-primary">A healthy Nepali meal delivered to your door, daily</h1>
            <p class="hero-description">
              The smart 365-days-per-year food subscription. Authentic local flavors tailored to your nutritional needs. We have delivered 250,000+ meals in Kathmandu!
            </p>
            <a href="#cta" class="btn btn--full margin-right-sm">Start eating well</a>
            <a href="#how" class="btn btn--outline">Learn More &darr;</a>
            <div class="delivered-meals">
              <div class="delivered-imgs">
                <img src="./img/customers/customer-1.jpg" alt="Customer">
                <img src="./img/customers/customer-2.jpg" alt="Customer">
                <img src="./img/customers/customer-3.jpg" alt="Customer">
                <img src="./img/customers/customer-4.jpg" alt="Customer">
                <img src="./img/customers/customer-5.jpg" alt="Customer">
                <img src="./img/customers/customer-6.jpg" alt="Customer">
              </div>
              <p class="delivered-text"><span>250,000+</span> meals delivered last year!</p>
            </div>
          </div>
          <div class="hero-img-box">
            <img src="./img/hero.png" class="hero-img" alt="Food Bowls">
          </div>
        </div>
      </section>

      <section class="section-featured">
        <div class="container">
          <h2 class="heading-featured-in">As seen in Nepal's Top Media</h2>
          <div class="logos" style="display: flex; justify-content: space-around; align-items: center; flex-wrap: wrap; gap: 30px;">
            <h3 class="logo-text">The Kathmandu Post</h3>
            <h3 class="logo-text">OnlineKhabar</h3>
            <h3 class="logo-text">RONB</h3>
            <h3 class="logo-text">The Himalayan Times</h3>
            <h3 class="logo-text">Setopati</h3>
          </div>
        </div>
      </section>

      <section class="section-how" id="how">
        <div class="container">
          <span class="subheading">How it works</span>
          <h2 class="heading-secondary">Your daily dose of health in 3 simple steps</h2>
        </div>
        <div class="container grid grid--2-cols grid-center-v">
          <div class="step-text-box">
            <p class="step-number">01</p>
            <h3 class="heading-tertiary">Tell us what you like:</h3>
            <p class="step-description">Omnifood AI creates a personalized weekly plan. It adapts to your taste, whether you love spicy Momos or mild Thukpa.</p>
          </div>
          <div class="step-img-box"><img class="step-img" src="./img/app/app-screen-1.png" alt="App screen 1" /></div>
          
          <div class="step-img-box"><img class="step-img" src="./img/app/app-screen-2.png" alt="App screen 2" /></div>
          <div class="step-text-box">
            <p class="step-number">02</p>
            <h3 class="heading-tertiary">Approve your plan:</h3>
            <p class="step-description">Check your weekly plan. Swap ingredients or change entire meals if you crave something else.</p>
          </div>

          <div class="step-text-box">
            <p class="step-number">03</p>
            <h3 class="heading-tertiary">We cook & deliver:</h3>
            <p class="step-description">Our local chefs cook your meal, and we deliver it to your door in reusable tiffin boxes.</p>
          </div>
          <div class="step-img-box"><img class="step-img" src="./img/app/app-screen-3.png" alt="App screen 3" /></div>
        </div>
      </section>

      <section class="section-meals" id="meals">
        <div class="container center-text">
            <span class="subheading">Meals</span>
            <h2 class="heading-secondary">Omnifood AI chooses from 5,000+ recipes</h2>
        </div>
        <div class="container grid grid--3-cols margin-bottom-md">
            <div class="meal">
                <img src="./img/meals/meal-1.jpg" class="meal-img" alt="Momo" />
                <div class="meal-content">
                    <span class="tag tag--vegetarian">Veg</span>
                    <p class="meal-title">Steam Jhol Momos</p>
                    <ul class="meal-attributes">
                        <li class="meal-attribute"><ion-icon class="meal-icon" name="flame-outline"></ion-icon><span><strong>400</strong> Calories</span></li>
                        <li class="meal-attribute"><ion-icon class="meal-icon" name="restaurant-outline"></ion-icon><span>NutriScore &reg; <strong>92</strong></span></li>
                        <li class="meal-attribute"><ion-icon class="meal-icon" name="star-outline"></ion-icon><span><strong>4.9</strong> Rating</span></li>
                    </ul>
                    <div class="meal-actions">
                        <a href="recipes.php" class="btn--outline-sm">See Recipe</a>
                        <button class="btn--sm" onclick="addToCart('Steam Jhol Momos', 350)">Add +</button>
                    </div>
                </div>
            </div>
            <div class="meal">
                <img src="./img/meals/meal-2.jpg" class="meal-img" alt="Dal Bhat" />
                <div class="meal-content">
                    <span class="tag tag--vegan">Vegan</span>
                    <span class="tag tag--paleo">Healthy</span>
                    <p class="meal-title">Thakali Dal Bhat</p>
                    <ul class="meal-attributes">
                        <li class="meal-attribute"><ion-icon class="meal-icon" name="flame-outline"></ion-icon><span><strong>750</strong> Calories</span></li>
                        <li class="meal-attribute"><ion-icon class="meal-icon" name="restaurant-outline"></ion-icon><span>NutriScore &reg; <strong>98</strong></span></li>
                        <li class="meal-attribute"><ion-icon class="meal-icon" name="star-outline"></ion-icon><span><strong>5.0</strong> Rating</span></li>
                    </ul>
                    <div class="meal-actions">
                        <a href="recipes.php" class="btn--outline-sm">See Recipe</a>
                        <button class="btn--sm" onclick="addToCart('Thakali Dal Bhat', 450)">Add +</button>
                    </div>
                </div>
            </div>
            <div class="diets">
                <h3 class="heading-tertiary">Works with any diet:</h3>
                <ul class="list">
                    <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Vegetarian</span></li>
                    <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Vegan</span></li>
                    <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Gluten-free</span></li>
                    <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Keto</span></li>
                    <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Paleo</span></li>
                    <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Low FODMAP</span></li>
                    <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Kid-friendly</span></li>
                </ul>
            </div>
        </div>
        <div class="container all-recipes"><a href="recipes.php" class="link">See all recipes &rarr;</a></div>
      </section>

      <section class="section-testimonials" id="chefs" style="background-color: #fdf2e9;">
        <div class="container center-text">
            <span class="subheading">The Team</span>
            <h2 class="heading-secondary">Meet our Local Chefs</h2>
            <p style="font-size: 1.6rem; margin-top: 1rem;">The masters behind your daily meals.</p>
        </div>
        <div class="container chefs-grid">
            <div class="chef-card"><img src="./img/chef/Ram Sharan Thapa.jpg" class="chef-img" alt="Chef Ram"><h3 class="chef-name">Ram Bahadur</h3><p class="chef-role">Momo Expert</p></div>
            <div class="chef-card"><img src="./img/chef/Bishal Gurung.webp" class="chef-img" alt="Chef Bishal"><h3 class="chef-name">Bishal Gurung</h3><p class="chef-role">Thakali Master</p></div>
            <div class="chef-card"><img src="./img/chef/Dorje Sherpa.jpeg" class="chef-img" alt="Chef Dorje"><h3 class="chef-name">Dorje Sherpa</h3><p class="chef-role">Himalayan Soups</p></div>
            <div class="chef-card"><img src="./img/chef/Rahul Babu Shrestha.avif" class="chef-img" alt="Chef Rahul"><h3 class="chef-name">Rahul Babu Shrestha</h3><p class="chef-role">Newari Cuisine</p></div>
        </div>
        <div class="gallery" style="margin-top: 4rem;">
            <figure class="gallery-item"><img src="./img/gallery/gallery-1.jpg" alt="Photo of food" /></figure>
            <figure class="gallery-item"><img src="./img/gallery/gallery-2.jpg" alt="Photo of food" /></figure>
            <figure class="gallery-item"><img src="./img/gallery/gallery-3.jpg" alt="Photo of food" /></figure>
            <figure class="gallery-item"><img src="./img/gallery/gallery-4.jpg" alt="Photo of food" /></figure>
            <figure class="gallery-item"><img src="./img/gallery/gallery-5.jpg" alt="Photo of food" /></figure>
            <figure class="gallery-item"><img src="./img/gallery/gallery-6.jpg" alt="Photo of food" /></figure>
            <figure class="gallery-item"><img src="./img/gallery/gallery-7.jpg" alt="Photo of food" /></figure>
            <figure class="gallery-item"><img src="./img/gallery/gallery-8.jpg" alt="Photo of food" /></figure>
            <figure class="gallery-item"><img src="./img/gallery/gallery-9.jpg" alt="Photo of food" /></figure>
            <figure class="gallery-item"><img src="./img/gallery/gallery-10.jpg" alt="Photo of food" /></figure>
            <figure class="gallery-item"><img src="./img/gallery/gallery-11.jpg" alt="Photo of food" /></figure>
            <figure class="gallery-item"><img src="./img/gallery/gallery-12.jpg" alt="Photo of food" /></figure>
        </div>
      </section>

      <section class="section-pricing" id="pricing">
        <div class="container center-text">
          <span class="subheading">Pricing</span>
          <h2 class="heading-secondary">Simple pricing, cancel anytime</h2>
        </div>
        <div class="container grid grid--2-cols margin-bottom-md">
          <div class="pricing-plan pricing-plan--starter">
            <header class="plan-header">
              <p class="plan-name">Starter</p>
              <p class="plan-price"><span>Rs.</span>12,000</p>
              <p class="plan-text">per month. Just Rs 400/meal.</p>
            </header>
            <ul class="list">
              <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>1 meal per day</span></li>
              <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Order from 11am - 9pm</span></li>
              <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Delivery is free</span></li>
              <li class="list-item"><ion-icon class="list-icon" name="close-outline"></ion-icon><span>No recipe access</span></li>
            </ul>
            <div class="plan-sign-up">
              <button class="btn btn--full" onclick="addToCart('Starter Plan', 12000)">Add to Cart</button>
            </div>
          </div>
          <div class="pricing-plan pricing-plan--complete">
            <header class="plan-header">
              <p class="plan-name">Complete</p>
              <p class="plan-price"><span>Rs.</span>20,000</p>
              <p class="plan-text">per month. Just Rs 330/meal.</p>
            </header>
            <ul class="list">
              <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span><strong>2 meals</strong> per day</span></li>
              <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Order <strong>24/7</strong></span></li>
              <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Delivery is free</span></li>
              <li class="list-item"><ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span><strong>Access Secret Recipes</strong></span></li>
            </ul>
            <div class="plan-sign-up">
               <button class="btn btn--full" onclick="addToCart('Complete Plan', 20000)">Add to Cart</button>
            </div>
          </div>
        </div>
        
        <div class="container grid">
          <aside class="plan-details">Prices include all applicable taxes. Users can cancel at any time.</aside>
        </div>
        
        <div class="container grid grid--4-cols">
          <div class="feature"><ion-icon class="feature-icon" name="infinite-outline"></ion-icon><p class="feature-title">Never cook again!</p><p class="feature-text">Our subscriptions cover 365 days per year.</p></div>
          <div class="feature"><ion-icon class="feature-icon" name="nutrition-outline"></ion-icon><p class="feature-title">Local and organic</p><p class="feature-text">Our cooks only use local, fresh, and organic products.</p></div>
          <div class="feature"><ion-icon class="feature-icon" name="leaf-outline"></ion-icon><p class="feature-title">No waste</p><p class="feature-text">All our partners only use reusable containers.</p></div>
          <div class="feature"><ion-icon class="feature-icon" name="pause-outline"></ion-icon><p class="feature-title">Pause anytime</p><p class="feature-text">Going on vacation? Just pause your subscription.</p></div>
        </div>
      </section>

      <section class="section-cta" id="cta">
        <div class="container">
          <div class="cta">
            <div class="cta-text-box">
              
              <?php if(isset($_SESSION['user_id'])): ?>
                  
                  <h2 class="heading-secondary">Welcome Back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
                  <p class="cta-text" style="margin-bottom: 3rem;">We are ready to serve you healthy and tasty meals. Check out our menu or view your cart.</p>
                  <a href="#meals" class="btn btn--form" style="text-decoration: none;">View Menu</a>

              <?php else: ?>

                  <h2 class="heading-secondary">Get your first meal for free</h2>
                  <p class="cta-text">Healthy, tasty and hassle-free meals are waiting for you.</p>
                  
                  <form method="POST" class="cta-form">
                    <input type="hidden" name="order_items" id="hidden-order-items-signup" value="">
                    
                    <div>
                      <label for="full-name">Full Name</label>
                      <input type="text" id="full-name" name="full_name" placeholder="John Smith" required>
                    </div>
                    <div>
                      <label for="email">Email Address</label>
                      <input type="email" name="email" id="email" placeholder="me@example.com" required>
                    </div>
                    
                    <div>
                      <label for="phone">Phone Number</label>
                      <input type="tel" name="phone" id="phone" placeholder="98XXXXXXXX" required>
                    </div>

                    <div>
                      <label for="select-where">Where did you hear from us?</label>
                      <select id="select-where" name="select_where" required>
                        <option value="">Select one option</option>
                        <option value="Friends">Friends</option>
                        <option value="Social Media">Social Media</option>
                        <option value="Ad">Advertisement</option>
                        <option value="Others">Others</option>
                      </select>
                    </div>
                    <button type="submit" class="btn btn--form">Sign Up</button>
                  </form>
              
              <?php endif; ?>

            </div>
            <div class="cta-image-box" role="img" aria-label="Woman enjoying food"></div>
          </div>
        </div>
      </section>
    </main>

    <footer class="footer">
      <div class="container grid grid--footer">
        <div class="logo-col">
          <a href="#" class="footer-logo"><img class="logo" src="./img/omnifood-logo.png" alt="Omnifood Logo"></a>
          <ul class="social-links">
            <li><a class="footer-link" href="#"><ion-icon class="social-icon" name="logo-instagram"></ion-icon></a></li>
            <li><a class="footer-link" href="#"><ion-icon class="social-icon" name="logo-facebook"></ion-icon></a></li>
            <li><a class="footer-link" href="#"><ion-icon class="social-icon" name="logo-twitter"></ion-icon></a></li>
          </ul>
          <p class="copyright">Copyright &copy; 2025 by Omnifood Nepal.</p>
        </div>
        <div class="address-col">
          <p class="footer-heading">Contact</p>
          <address class="contacts">
            <p class="address">Kathmandu, Nepal</p>
            <p><a class="footer-link" href="tel:9800000000">980-000-0000</a></p>
            <p><a class="footer-link" href="mailto:hello@omnifood.com">hello@omnifood.com</a></p>
          </address>
        </div>
        <nav class="nav-col">
          <p class="footer-heading">Account</p>
          <ul class="footer-nav">
            <li><a class="footer-link" href="#">Create account</a></li>
            <li><a class="footer-link" href="signin.php">Sign in</a></li>
            <li><a class="footer-link" href="#">iOS app</a></li>
            <li><a class="footer-link" href="#">Android app</a></li>
          </ul>
        </nav>
        <nav class="nav-col">
          <p class="footer-heading">Company</p>
          <ul class="footer-nav">
            <li><a class="footer-link" href="#">About Omnifood</a></li>
            <li><a class="footer-link" href="business.php">For Business</a></li>
            <li><a class="footer-link" href="chefs.php">Cooking partners</a></li>
            <li><a class="footer-link" href="#">Careers</a></li>
          </ul>
        </nav>
        <nav class="nav-col">
          <p class="footer-heading">Resources</p>
          <ul class="footer-nav">
            <li><a class="footer-link" href="recipes.php" style="font-weight: bold; color: #e67e22;">Recipe Directory</a></li>
            <li><a class="footer-link" href="privacy.php">Privacy & terms</a></li>
            <li><a class="footer-link" href="#">Help center</a></li>
          </ul>
        </nav>
      </div>
    </footer>
    
    <div id="sticky-cart" onclick="toggleCartModal()">
        <div style="font-size: 1.6rem; font-weight: 600;">
            <ion-icon name="cart-outline" style="font-size: 2.4rem; vertical-align: middle; margin-right: 10px;"></ion-icon>
            <span id="cart-count">0</span> Items <span id="cart-total" style="color: #e67e22; margin-left: 10px;">(Rs. 0)</span>
        </div>
        <div style="font-size: 1.6rem; font-weight: 600; display: flex; align-items: center; gap: 5px;">
            View Cart <ion-icon name="chevron-up-outline"></ion-icon>
        </div>
    </div>
    
    <div id="cartModal" class="modal-overlay" style="display: none; z-index: 10000;">
        <div class="modal-content" style="max-width: 500px;">
            <span class="close-btn" onclick="toggleCartModal()">&times;</span>
            <h2 class="heading-secondary">Your Cart</h2>
            <ul id="cart-list-container" class="cart-list"></ul>
            <div style="margin-top: 20px; border-top: 2px solid #fdf2e9; padding-top: 20px; display:flex; justify-content:space-between;">
                <span style="font-size: 1.8rem; font-weight: 700;">Total:</span>
                <span id="modal-cart-total" style="font-size: 2rem; font-weight: 700; color: #e67e22;">Rs. 0</span>
            </div>
            <button class="btn btn--full" onclick="showQRModal()" style="margin-top: 20px; width: 100%;">Proceed to Checkout</button>
        </div>
    </div>

    <div id="qrModal" class="modal-overlay" style="display: none; z-index: 10001;">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <span class="close-btn" onclick="closeQRModal()">&times;</span>
            <h2 class="heading-secondary" style="margin-bottom: 10px;">Scan to Pay</h2>
            <p style="font-size: 1.6rem; margin-bottom: 20px; color: #555;">Scan this code to complete your payment via Instagram.</p>
            
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://instagram.com/krishna_bhatt_69" 
                 alt="Payment QR" 
                 style="border: 5px solid #e67e22; border-radius: 10px; margin-bottom: 20px;">
                 
            <p style="font-size: 1.4rem; color: #888; margin-bottom: 10px;">QR not working?</p>
            
            <a href="https://instagram.com/krishna_bhatt_69" target="_blank" 
               style="display: inline-block; background: #E1306C; color: white; padding: 10px 20px; border-radius: 50px; text-decoration: none; font-size: 1.4rem; font-weight: bold; margin-bottom: 20px;">
               <ion-icon name="logo-instagram"></ion-icon> Pay @krishna_bhatt_69
            </a>

            <input type="tel" id="checkout-phone" placeholder="Enter Contact Number (98XXXXXXXX)" style="width:100%; padding:12px; margin-bottom:15px; border:1px solid #e67e22; border-radius:5px; font-size:1.4rem; text-align:center;">

            <button type="button" class="btn-location" onclick="getKathmanduLocation()">
                <ion-icon name="location-outline"></ion-icon> Verify Location (Kathmandu Only)
            </button>
            <p id="loc-status" style="font-size:1.4rem; color:red; margin-bottom:15px; font-weight:bold;"></p>

            <button class="btn btn--full" id="btn-submit-order" onclick="submitFinalOrder()" disabled style="background-color: #ccc; cursor: not-allowed;">
                I have Scanned & Paid &rarr;
            </button>
        </div>
    </div>

    <form id="checkout-form" method="POST" style="display:none;">
        <input type="hidden" name="hidden_order_items" id="hidden-order-items">
        <input type="hidden" name="location_coords" id="location_coords">
        <input type="hidden" name="phone" id="hidden-phone">
    </form>

    <script>
        let cart = [];

        function addToCart(name, price) {
            cart.push({name, price});
            updateCartUI();
            const btn = event.target; 
            const originalText = btn.innerText;
            btn.innerText = "Added!";
            btn.style.background = "#27ae60";
            setTimeout(() => { btn.innerText = originalText; btn.style.background = ""; }, 1000);
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartUI();
        }

        function updateCartUI() {
            const sticky = document.getElementById('sticky-cart');
            const count = document.getElementById('cart-count');
            const total = document.getElementById('cart-total');
            const input = document.getElementById('hidden-order-items');
            const signupInput = document.getElementById('hidden-order-items-signup');

            if(cart.length > 0) {
                sticky.style.display = 'flex';
                count.innerText = cart.length;
                let sum = cart.reduce((a, b) => a + b.price, 0);
                total.innerText = "(Rs. " + sum.toLocaleString() + ")";
                
                let orderStr = cart.map(i => i.name).join(", ") + " | Total: " + sum;
                input.value = orderStr;
                if(signupInput) signupInput.value = orderStr;
                
                renderCartList();
            } else {
                sticky.style.display = 'none';
                input.value = "";
                renderCartList();
            }
        }

        function toggleCartModal() {
            const modal = document.getElementById('cartModal');
            modal.style.display = (modal.style.display === 'flex') ? 'none' : 'flex';
            renderCartList();
        }

        function renderCartList() {
            const list = document.getElementById('cart-list-container');
            const totalEl = document.getElementById('modal-cart-total');
            list.innerHTML = "";
            let sum = 0;
            
            if(cart.length === 0) {
                list.innerHTML = "<li style='text-align:center; font-size:1.6rem; color:#888;'>Cart is empty.</li>";
                totalEl.innerText = "Rs. 0";
                return;
            }

            cart.forEach((item, idx) => {
                sum += item.price;
                list.innerHTML += `
                    <li style="display:flex; justify-content:space-between; padding:10px; border-bottom:1px solid #eee; font-size:1.6rem;">
                        <span>${item.name}</span>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <strong>Rs. ${item.price}</strong>
                            <button onclick="removeFromCart(${idx})" style="background:#ffe3e3; color:red; border:none; padding:5px; border-radius:5px; cursor:pointer;"><ion-icon name="trash"></ion-icon></button>
                        </div>
                    </li>`;
            });
            totalEl.innerText = "Rs. " + sum.toLocaleString();
        }

        function showQRModal() {
            var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
            
            if(!isLoggedIn) {
                alert("Please login to proceed.");
                window.location.href = "signin.php";
                return;
            }
            
            if(cart.length === 0) {
                alert("Cart is empty!");
                return;
            }

            document.getElementById('cartModal').style.display = 'none';
            document.getElementById('qrModal').style.display = 'flex';
        }

        function closeQRModal() {
            document.getElementById('qrModal').style.display = 'none';
        }

        // --- LOCATION VERIFICATION (KATHMANDU) ---
        function getKathmanduLocation() {
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

                // Kathmandu Bounds
                const isKathmandu = (lat >= 27.60 && lat <= 27.85) && (lng >= 85.20 && lng <= 85.55);

                if (isKathmandu) {
                    document.getElementById('location_coords').value = lat + "," + lng;
                    status.innerText = "Location Verified: Kathmandu ✅";
                    status.style.color = "green";
                    
                    // Enable Submit Button
                    btn.disabled = false;
                    btn.style.backgroundColor = "#e67e22";
                    btn.style.cursor = "pointer";
                } else {
                    status.innerText = "Outside Delivery Zone. Kathmandu Only ❌";
                    status.style.color = "red";
                    btn.disabled = true;
                    btn.style.backgroundColor = "#ccc";
                }

            }, () => {
                status.innerText = "Access Denied.";
                status.style.color = "red";
            });
        }

        function submitFinalOrder() {
            // TRANSFER PHONE VALUE TO HIDDEN FORM
            const phoneVal = document.getElementById('checkout-phone').value;
            if(!phoneVal) { alert("Please enter your contact number."); return; }
            document.getElementById('hidden-phone').value = phoneVal;

            document.getElementById('checkout-form').submit();
        }

        function closeModal() {
            const modal = document.getElementById('modalOverlay');
            if (modal) {
                modal.style.display = 'none';
                window.history.replaceState({}, document.title, "index.php");
            }
        }
    </script>
    
    <a href="admin.php" style="position:fixed; bottom:80px; right:20px; background:#333; color:white; padding:10px 20px; border-radius:50px; text-decoration:none; font-size:1.4rem; z-index:900; display:flex; align-items:center; gap:8px;">
        <ion-icon name="shield-checkmark-outline"></ion-icon> Admin
    </a>
  </body>
</html>