<?php
include 'connect.php';

$message = "";
$status = "";

// --- 1. HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name   = htmlspecialchars(trim($_POST['full_name']));
    $email  = htmlspecialchars(trim($_POST['email']));
    $source = htmlspecialchars(trim($_POST['select_where']));
    
    // Capture order items
    $order_items = isset($_POST['order_items']) ? htmlspecialchars(trim($_POST['order_items'])) : "";
    if(empty($order_items)) { $order_items = "Newsletter Signup"; }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.php?status=invalid");
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO cafe (name, email, order_items, source) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $order_items, $source);
        
        if ($stmt->execute()) {
            header("Location: index.php?status=success");
            exit();
        } else {
            header("Location: index.php?status=error&msg=" . urlencode($stmt->error));
            exit();
        }
        $stmt->close();
    }
}

// --- 2. POPUP MESSAGES ---
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $message = "✅ Namaste! Your order has been placed successfully.";
        $status = "success";
    } elseif ($_GET['status'] === 'invalid') {
        $message = "❌ Invalid email. Please try again.";
        $status = "error";
    } elseif ($_GET['status'] === 'error') {
        $message = "❌ Error: " . htmlspecialchars($_GET['msg']);
        $status = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Omnifood Nepal &mdash; Never cook again!</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/general.css">
    
    <style>
      /* --- POPUP & CART STYLES --- */
      .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.7); display: flex; justify-content: center;
        align-items: center; z-index: 9999; backdrop-filter: blur(5px);
      }
      .modal-content {
        background: white; padding: 40px; border-radius: 12px;
        text-align: center; max-width: 400px; animation: popUp 0.3s ease-out;
      }
      @keyframes popUp { from {transform: scale(0.8); opacity: 0;} to {transform: scale(1); opacity: 1;} }
      .modal-success { border-top: 5px solid #2ecc71; }
      .modal-error { border-top: 5px solid #e74c3c; }
      
      /* STICKY CART */
      #sticky-cart {
          display: none; position: fixed; bottom: 0; left: 0; width: 100%;
          background: white; border-top: 3px solid #e67e22; padding: 20px 40px;
          justify-content: space-between; align-items: center; z-index: 1000;
          box-shadow: 0 -5px 20px rgba(0,0,0,0.1); box-sizing: border-box;
      }
      .cart-btn {
          background-color: #e67e22; color: white; padding: 12px 24px;
          border: none; border-radius: 9px; font-weight: 600; cursor: pointer;
          font-size: 1.6rem; transition: 0.3s;
      }
      .cart-btn:hover { background-color: #cf711f; }

      /* CHEF CARDS */
      .chefs-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; margin-top: 30px; }
      .chef-card { text-align: center; }
      .chef-img {
            width: 150px; height: 150px; border-radius: 50%; object-fit: cover;
            border: 4px solid #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.1); margin-bottom: 15px;
            transition: transform 0.3s;
      }
      .chef-card:hover .chef-img { transform: scale(1.05); border-color: #e67e22; }
      .chef-name { font-size: 1.8rem; font-weight: 700; color: #333; margin-bottom: 5px; }
      .chef-role { font-size: 1.4rem; color: #777; font-weight: 500; text-transform: uppercase; }

      /* LOGO TEXT (For Nepali Media) */
      .logo-text {
          font-size: 2.2rem;
          font-weight: 700;
          color: #888;
          opacity: 0.6;
          margin: 0;
          text-transform: uppercase;
          transition: 0.3s;
          cursor: default;
      }
      .logo-text:hover { opacity: 1; color: #555; }
      
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
      <a href="index.php">
        <img class="logo" src="./img/omnifood-logo.png" alt="Omnifood Logo">
      </a>
      <nav class="main-nav">
        <ul class="main-nav-list">
          <li><a class="main-nav-link" href="#how">How it works</a></li>
          <li><a class="main-nav-link" href="#meals">Meals</a></li>
          <li><a class="main-nav-link" href="#chefs">Our Chefs</a></li>
          <li><a class="main-nav-link" href="#pricing">Pricing</a></li>
          <li><a class="main-nav-link nav-cta" href="#cta">Try for free</a></li>
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
        <div class="container all-recipes">
          <a href="recipes.php" class="link">See all recipes &rarr;</a>
        </div>
      </section>

      <section class="section-testimonials" id="chefs" style="background-color: #fdf2e9;">
        <div class="container center-text">
            <span class="subheading">The Team</span>
            <h2 class="heading-secondary">Meet our Local Chefs</h2>
            <p style="font-size: 1.6rem; margin-top: 1rem;">The masters behind your daily meals.</p>
        </div>
        
        <div class="container chefs-grid">
            
            <div class="chef-card">
                <img src="./img/chef/Ram Sharan Thapa.jpg" class="chef-img" alt="Chef Ram">
                <h3 class="chef-name">Ram Bahadur</h3>
                <p class="chef-role">Momo Expert</p>
            </div>

            <div class="chef-card">
                <img src="./img/chef/Bishal Gurung.webp" class="chef-img" alt="Chef Bishal">
                <h3 class="chef-name">Bishal Gurung</h3>
                <p class="chef-role">Thakali Master</p>
            </div>

            <div class="chef-card">
                 <img src="./img/chef/Dorje Sherpa.jpeg" class="chef-img" alt="Chef Dorje">
                <h3 class="chef-name">Dorje Sherpa</h3>
                <p class="chef-role">Himalayan Soups</p>
            </div>

            <div class="chef-card">
                 <img src="./img/chef/Rahul Babu Shrestha.avif" class="chef-img" alt="Chef Rahul">
                <h3 class="chef-name">Rahul Babu Shrestha</h3>
                <p class="chef-role">Newari Cuisine</p>
            </div>

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
              <h2 class="heading-secondary">Get your first meal for free</h2>
              <p class="cta-text">Healthy, tasty and hassle-free meals are waiting for you.</p>
              
              <form method="POST" class="cta-form">
                <input type="hidden" name="order_items" id="hidden-order-items" value="">
                
                <div>
                  <label for="full-name">Full Name</label>
                  <input type="text" id="full-name" name="full_name" placeholder="John Smith" required>
                </div>
                <div>
                  <label for="email">Email Address</label>
                  <input type="email" name="email" id="email" placeholder="me@example.com" required>
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
            <li><a class="footer-link" href="#">Sign in</a></li>
            <li><a class="footer-link" href="#">iOS app</a></li>
            <li><a class="footer-link" href="#">Android app</a></li>
          </ul>
        </nav>
        <nav class="nav-col">
          <p class="footer-heading">Company</p>
          <ul class="footer-nav">
            <li><a class="footer-link" href="#">About Omnifood</a></li>
            <li><a class="footer-link" href="#">For Business</a></li>
            <li><a class="footer-link" href="#">Cooking partners</a></li>
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

    <div id="sticky-cart">
        <div style="font-size: 1.6rem; font-weight: 600;">
            Cart: <span id="cart-count" style="color:#e67e22">0</span> items 
            <span id="cart-total" style="color:#555; margin-left:10px;">(Rs. 0)</span>
        </div>
        <button onclick="document.getElementById('cta').scrollIntoView({behavior:'smooth'})" class="cart-btn">Checkout</button>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
      // 1. MODAL LOGIC
      function closeModal() {
        document.getElementById('modalOverlay').style.display = 'none';
        window.history.replaceState({}, document.title, "index.php");
      }

      // 2. CART LOGIC
      let cart = [];
      function addToCart(name, price) {
        cart.push({name, price});
        updateCart();
      }
      function updateCart() {
        const cartBar = document.getElementById('sticky-cart');
        const countSpan = document.getElementById('cart-count');
        const totalSpan = document.getElementById('cart-total');
        const hiddenInput = document.getElementById('hidden-order-items');

        if(cart.length > 0) {
            cartBar.style.display = 'flex';
            countSpan.innerText = cart.length;
            
            let total = cart.reduce((sum, item) => sum + item.price, 0);
            totalSpan.innerText = "(Rs. " + total.toLocaleString() + ")";
            
            // Map items to string for PHP
            hiddenInput.value = cart.map(i => i.name).join(", ");
        } else {
            cartBar.style.display = 'none';
        }
      }
    </script>
    
    <a href="admin.php" style="position:fixed; bottom:80px; right:20px; background:#333; color:white; padding:10px 20px; border-radius:50px; text-decoration:none; font-size:1.4rem; z-index:900; display:flex; align-items:center; gap:8px;">
        <ion-icon name="shield-checkmark-outline"></ion-icon> Admin
    </a>

  </body>
</html>