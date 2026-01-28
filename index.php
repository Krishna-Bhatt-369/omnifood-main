<?php
include 'connect.php';

$message = "";
$status = "";

// 1. HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name   = htmlspecialchars(trim($_POST['full_name']));
    $email  = htmlspecialchars(trim($_POST['email']));
    $source = htmlspecialchars(trim($_POST['select_where']));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Redirect with invalid error
        header("Location: index.php?status=invalid");
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO cafe (name, email, source) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $source);
        
        if ($stmt->execute()) {
            // SUCCESS: Redirect to clear form data
            header("Location: index.php?status=success");
            exit();
        } else {
            // ERROR: Redirect with error message
            header("Location: index.php?status=error&msg=" . urlencode($stmt->error));
            exit();
        }
        $stmt->close();
    }
}

// 2. CHECK URL PARAMETERS TO SHOW POPUP
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $message = "✅ Submitted successfully! Welcome to Omnifood.";
        $status = "success";
    } elseif ($_GET['status'] === 'invalid') {
        $message = "❌ Invalid email address. Please try again.";
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
    <title>Omnifood &mdash; Never cook again!</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/general.css">
    <style>
      /* Admin Floating Button */
      .admin-badge {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background-color: #333;
        color: #fff;
        padding: 1rem 1.6rem;
        border-radius: 50px;
        font-size: 1.4rem;
        font-weight: 600;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 2px solid transparent;
      }

      .admin-badge:hover {
        background-color: #e67e22;
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(230, 126, 34, 0.3);
      }

      /* --- POPUP MODAL STYLES --- */
      .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none; 
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(5px);
      }
      .modal-content {
        background: #fff;
        padding: 4.8rem;
        border-radius: 12px;
        max-width: 50rem;
        text-align: center;
        box-shadow: 0 2.4rem 4.8rem rgba(0, 0, 0, 0.2);
        animation: popUp 0.3s ease-out;
      }
      @keyframes popUp {
        0% { transform: scale(0.8); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
      }
      .modal-icon { font-size: 7.4rem; margin-bottom: 2.4rem; }
      .modal-success .modal-icon { color: #51cf66; }
      .modal-error .modal-icon { color: #fa5252; }
      .modal-title { font-size: 3rem; color: #333; margin-bottom: 1.6rem; font-weight: 700; }
      .modal-text { font-size: 1.8rem; line-height: 1.5; color: #555; margin-bottom: 3.2rem; }
      .btn--modal { background-color: #e67e22; color: #fff; border: none; padding: 1.2rem 3.2rem; border-radius: 9px; font-weight: 600; cursor: pointer; font-size: 1.8rem; transition: all 0.3s; }
      .btn--modal:hover { background-color: #cf711f; }
    </style>
  </head>
  <body>

    <?php if (!empty($message)): ?>
    <div class="modal-overlay" id="modalOverlay" style="display: flex;">
      <div class="modal-content <?php echo ($status === 'success') ? 'modal-success' : 'modal-error'; ?>">
        <div class="modal-icon">
          <ion-icon name="<?php echo ($status === 'success') ? 'checkmark-circle-outline' : 'alert-circle-outline'; ?>"></ion-icon>
        </div>
        <h2 class="modal-title"><?php echo ($status === 'success') ? 'Success!' : 'Oops!'; ?></h2>
        <p class="modal-text"><?php echo $message; ?></p>
        <button class="btn--modal" onclick="closeModal()">Close</button>
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
          <li><a class="main-nav-link" href="#testimonials">Testimonials</a></li>
          <li><a class="main-nav-link" href="#pricing">Pricing</a></li>
          <li><a class="main-nav-link nav-cta" href="#cta">Try for free</a></li>
        </ul>
      </nav>
    </header>

    <main>
      <section class="section-hero">
        <div class="hero">
          <div class="hero-text-box">
            <h1 class="heading-primary">A healthy meal delivered to your door, every single day</h1>
            <p class="hero-description">
              The smart 365-days-per-year food subscription that will make you eat healthy again. Tailored to your personal tastes and nutritional needs.
            </p>
            <a href="#cta" class="btn btn--full margin-right-sm">Start eating well</a>
            <a href="#how" class="btn btn--outline">Learn More &darr;</a>
            <div class="delivered-meals">
              <div class="delivered-imgs">
                <img src="./img/customers/customer-1.jpg" alt="Customer photo">
                <img src="./img/customers/customer-2.jpg" alt="Customer photo">
                <img src="./img/customers/customer-3.jpg" alt="Customer photo">
                <img src="./img/customers/customer-4.jpg" alt="Customer photo">
                <img src="./img/customers/customer-5.jpg" alt="Customer photo">
                <img src="./img/customers/customer-6.jpg" alt="Customer photo">
              </div>
              <p class="delivered-text"><span>250,000+</span> meals delivered last year!</p>
            </div>
          </div>
          <div class="hero-img-box">
            <img src="./img/hero.png" class="hero-img" alt="Woman enjoying food, meals in storage container and food bowls on a table">
          </div>
        </div>
      </section>

      <section class="section-featured">
        <div class="container">
          <h2 class="heading-featured-in">As featured in</h2>
          <div class="logos">
            <img src="./img/logos/techcrunch.png" alt="Techcrunch" />
            <img src="./img/logos/business-insider.png" alt="Business Insider" />
            <img src="./img/logos/the-new-york-times.png" alt="The New York Times" />
            <img src="./img/logos/forbes.png" alt="Forbes" />
            <img src="./img/logos/usa-today.png" alt="USA Today Logo" />
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
            <h3 class="heading-tertiary">Tell us what you like (and what not): </h3>
            <p class="step-description">
              Never again waste time thinking about what to eat! Omnifood AI will create a 100% personalized weekly meal plan just for you. It makes sure you get all the nutrients and vitamins you need, no matter what diet you follow!
            </p>
          </div>
          <div class="step-img-box">
            <img class="step-img" src="./img/app/app-screen-1.png" alt="iPhone app preferences" />
          </div>

          <div class="step-img-box">
            <img class="step-img" src="./img/app/app-screen-2.png" alt="Meal approving plan" />
          </div>
          <div class="step-text-box">
            <p class="step-number">02</p>
            <h3 class="heading-tertiary">Approve your weekly meal plan: </h3>
            <p class="step-description">
              Once per week, approve the meal plan generated for you by Omnifood AI. You can change ingredients, swap entire meals, or even add your own recipes.
            </p>
          </div>

          <div class="step-text-box">
            <p class="step-number">03</p>
            <h3 class="heading-tertiary">Receive meals at convenient time: </h3>
            <p class="step-description">
              Best chefs in town will cook your selected meal every day, and we will deliver it to your door whenever works best for you. You can change delivery schedule and address daily!
            </p>
          </div>
          <div class="step-img-box">
            <img class="step-img" src="./img/app/app-screen-3.png" alt="Delivery Screen" />
          </div>
        </div>
      </section>

      <section class="section-meals" id="meals">
        <div class="container center-text">
          <span class="subheading">Meals</span>
          <h2 class="heading-secondary">Omnifood AI chooses from 5,000+ recipes</h2>
        </div>
        
        <div class="container grid grid--3-cols margin-bottom-md">
          <div class="meal">
            <img src="./img/meals/meal-1.jpg" class="meal-img" alt="Japanese Gyozas" />
            <div class="meal-content">
              <div class="meal-tags">
                <span class="tag tag--vegetarian">Vegetarian</span>
              </div>
              <p class="meal-title">Japanese Gyozas</p>
              <ul class="meal-attributes">
                <li class="meal-attribute">
                  <ion-icon class="meal-icon" name="flame-outline"></ion-icon>
                  <span><strong>650</strong> Calories</span>
                </li>
                <li class="meal-attribute">
                  <ion-icon class="meal-icon" name="restaurant-outline"></ion-icon>
                  <span>NutriScore &reg; <strong>74</strong></span>
                </li>
                <li class="meal-attribute">
                  <ion-icon class="meal-icon" name="star-outline"></ion-icon>
                  <span><strong>4.9</strong> Rating <strong>(678)</strong></span>
                </li>
              </ul>
            </div>
          </div>
          <div class="meal">
            <img src="./img/meals/meal-2.jpg" class="meal-img" alt="Avocado Salad" />
            <div class="meal-content">
              <div class="meal-tags">
                <span class="tag tag--vegan">Vegan</span>
                <span class="tag tag--paleo">Paleo</span>
              </div>
              <p class="meal-title">Avocado Salad</p>
              <ul class="meal-attributes">
                <li class="meal-attribute">
                  <ion-icon class="meal-icon" name="flame-outline"></ion-icon>
                  <span><strong>400</strong> Calories</span>
                </li>
                <li class="meal-attribute">
                  <ion-icon class="meal-icon" name="restaurant-outline"></ion-icon>
                  <span>NutriScore &reg; <strong>92</strong></span>
                </li>
                <li class="meal-attribute">
                  <ion-icon class="meal-icon" name="star-outline"></ion-icon>
                  <span><strong>4.8</strong> Rating <strong>(441)</strong></span>
                </li>
              </ul>
            </div>
          </div>
          <div class="diets">
            <h3 class="heading-tertiary">Works with any diet:</h3>
            <ul class="list">
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                <span>Vegetarian</span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                <span>Vegan</span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                <span>Pescatarian</span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                <span>Gluten-free</span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                <span>Lactose-free</span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                <span>Keto</span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                <span>Paleo</span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                <span>Low FODMAP</span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                <span>Kid-friendly</span>
              </li>
            </ul>
          </div>
        </div>
        <div class="container all-recipes">
          <a href="#" class="link">See all recipes &rarr;</a>
        </div>
      </section>

      <section class="section-testimonials" id="testimonials">
        <div class="testimonials-container">
          <span class="subheading">Testimonials</span>
          <h2 class="heading-secondary">Once you try it, you can't get back</h2>

          <div class="testimonials">
          <figure>
            <img class="testimonial-img" src="./img/customers/dave.jpg" alt="Photo of customer Dave" />
            <blockquote class="testimonial-text">
              Inexpensive, healthy and great-tasting meals, without even having to order manually! It feels truly magical.
            </blockquote>
            <p class="testimonial-name">&mdash; Dave Bryson</p>
          </figure>

          <figure>
            <img class="testimonial-img" src="./img/customers/ben.jpg" alt="Photo of customer Ben Hadley" />
            <blockquote class="testimonial-text">
              The AI algorithm is crazy good, it chooses the right meals for me every time. It's amazing not to worry about food anymore!
            </blockquote>
            <p class="testimonial-name">&mdash; Ben Hadley</p>
          </figure>

          <figure>
            <img class="testimonial-img" src="./img/customers/steve.jpg" alt="Photo of customer Steve" />
            <blockquote class="testimonial-text">
              Omnifood is a life saver! I just started a company, so there's no time for cooking. I couldn't live without my daily meals now!
            </blockquote>
            <p class="testimonial-name">&mdash; Steve Miller</p>
          </figure>
          
          <figure>
            <img class="testimonial-img" src="./img/customers/hannah.jpg" alt="Photo of customer Hannah Smith" />
            <blockquote class="testimonial-text">
              I got Omnifood for the whole family, and it frees up so much time! Plus, everything is organic and vegan and without plastic.
            </blockquote>
            <p class="testimonial-name">&mdash; Hannah Smith</p>
          </figure>
        </div>
      </div>
        <div class="gallery">
          <figure class="gallery-item">
            <img src="./img/gallery/gallery-1.jpg" alt="Photo of beautifully arranged food" />
          </figure>
          <figure class="gallery-item">
            <img src="./img/gallery/gallery-2.jpg" alt="Photo of beautifully arranged food" />
          </figure>
          <figure class="gallery-item">
            <img src="./img/gallery/gallery-3.jpg" alt="Photo of beautifully arranged food" />
          </figure>
          <figure class="gallery-item">
            <img src="./img/gallery/gallery-4.jpg" alt="Photo of beautifully arranged food" />
          </figure>
          <figure class="gallery-item">
            <img src="./img/gallery/gallery-5.jpg" alt="Photo of beautifully arranged food" />
          </figure>
          <figure class="gallery-item">
            <img src="./img/gallery/gallery-6.jpg" alt="Photo of beautifully arranged food" />
          </figure>
          <figure class="gallery-item">
            <img src="./img/gallery/gallery-7.jpg" alt="Photo of beautifully arranged food" />
          </figure>
          <figure class="gallery-item">
            <img src="./img/gallery/gallery-8.jpg" alt="Photo of beautifully arranged food" />
          </figure>
          <figure class="gallery-item">
            <img src="./img/gallery/gallery-9.jpg" alt="Photo of beautifully arranged food" />
          </figure>
          <figure class="gallery-item">
            <img src="./img/gallery/gallery-10.jpg" alt="Photo of beautifully arranged food" />
          </figure>
          <figure class="gallery-item">
            <img src="./img/gallery/gallery-11.jpg" alt="Photo of beautifully arranged food" />
          </figure>
          <figure class="gallery-item">
            <img src="./img/gallery/gallery-12.jpg" alt="Photo of beautifully arranged food" />
          </figure>
        </div>
      </section>

      <section class="section-pricing" id="pricing">
        <div class="container center-text">
          <span class="subheading">Pricing</span>
          <h2 class="heading-secondary">Eating well without breaking the bank</h2>
        </div>

        <div class="container grid grid--2-cols margin-bottom-md">
          <div class="pricing-plan pricing-plan--starter">
            <header class="plan-header">
              <p class="plan-name">Starter</p>
              <p class="plan-price"><span>$</span>399</p>
              <p class="plan-text">per month. That's just $13 per meal!</p>
            </header>
            <ul class="list">
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline" role="img"></ion-icon>
                <span>1 meal per day</span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline" role="img"></ion-icon>
                <span>Order from 11am and 9pm</span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline" role="img"></ion-icon>
                <span>Delivery is free</span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="close-outline" role="img"></ion-icon>
                <span>No access to latest recipes</span>
              </li>
            </ul>
            <div class="plan-sign-up">
              <a href="#cta" class="btn btn--full">Start eating well</a>
            </div>
          </div>

          <div class="pricing-plan pricing-plan--complete">
            <header class="plan-header">
              <p class="plan-name">Complete</p>
              <p class="plan-price"><span>$</span>649</p>
              <p class="plan-text">per month. That's just $11 per meal!</p>
            </header>
            <ul class="list">
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline" role="img"></ion-icon>
                <span><strong>2 meals</strong> per day</span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline" role="img"></ion-icon>
                <span>Order <strong>24/7</strong></span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline" role="img"></ion-icon>
                <span>Delivery is free</span>
              </li>
              <li class="list-item">
                <ion-icon class="list-icon" name="checkmark-outline" role="img"></ion-icon>
                <span>Get access to latest recipes</span>
              </li>
            </ul>
            <div class="plan-sign-up">
              <a href="#cta" class="btn btn--full">Start eating well</a>
            </div>
          </div>
        </div>

        <div class="container grid">
          <aside class="plan-details">
            Prices include all applicable taxes. Users can cancel at any time. Both plans include the following:
          </aside>
        </div>

        <div class="container grid grid--4-cols">
          <div class="feature">
            <ion-icon class="feature-icon" name="infinite-outline" role="img"></ion-icon>
            <p class="feature-title">Never cook again!</p>
            <p class="feature-text">Our subscriptions cover 365 days per year, even including major holidays.</p>
          </div>

          <div class="feature">
            <ion-icon class="feature-icon" name="nutrition-outline" role="img"></ion-icon>
            <p class="feature-title">Local and organic</p>
            <p class="feature-text">Our cooks only use local, fresh, and organic products to prepare your meals.</p>
          </div>

          <div class="feature">
            <ion-icon class="feature-icon" name="leaf-outline" role="img"></ion-icon>
            <p class="feature-title">No waste</p>
            <p class="feature-text">All our partners only use reusable containers to package all your meals.</p>
          </div>

          <div class="feature">
            <ion-icon class="feature-icon" name="pause-outline" role="img"></ion-icon>
            <p class="feature-title">Pause anytime</p>
            <p class="feature-text">Going on vacation? Just pause your subscription and it will be waiting for you.</p>
          </div>
        </div>
      </section>

      <section class="section-cta" id="cta">
        <div class="container">
          <div class="cta">
            <div class="cta-text-box">
              <h2 class="heading-secondary">Get your first meal for free</h2>
              <p class="cta-text">
                Healthy, tasty and hassle-free meals are waiting for you. Start eating well today. You can cancel or pause anytime. And the first meal is on us!
              </p>
              
              <form method="POST" class="cta-form">
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
                    <option value="Friends">Friends & Family</option>
                    <option value="YouTube">YouTube</option>
                    <option value="Facebook">Facebook</option>
                    <option value="Instagram">Instagram</option>
                    <option value="Search Engine">Search Engine</option>
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
          <a href="#" class="footer-logo">
            <img class="logo" src="./img/omnifood-logo.png" alt="Omnifood Logo">
          </a>
          <ul class="social-links">
            <li><a class="footer-link" href="#"><ion-icon class="social-icon" name="logo-instagram"></ion-icon></a></li>
            <li><a class="footer-link" href="#"><ion-icon class="social-icon" name="logo-facebook"></ion-icon></a></li>
            <li><a class="footer-link" href="#"><ion-icon class="social-icon" name="logo-twitter"></ion-icon></a></li>
          </ul>
          <p class="copyright">Copyright &copy; 2025 by Omnifood, Inc. All right reserved.</p>
        </div>
        <div class="address-col">
          <p class="footer-heading">Contact us</p>
          <address class="contacts">
            <p class="address">
              623 Harrison St., 2nd Floor, San Francisco, CA 94107
            </p>
            <p>
              <a class="footer-link" href="tel:415-201-6370">415-201-6370</a><br />
              <a class="footer-link" href="mailto:hello@omnifood.com">hello@omnifood.com</a>
            </p>
          </address>
        </div>
        <nav class="nav-col">
          <p class="footer-heading">Account</p>
          <ul class="footer-nav">
            <li><a class="footer-link" href="create-account.php">Create account</a></li>
            <li><a class="footer-link" href="signin.php">Sign in</a></li>
            <li><a class="footer-link" href="ios-app.php">iOS app</a></li>
            <li><a class="footer-link" href="android-app.php">Android app</a></li>
          </ul>
        </nav>
        <nav class="nav-col">
          <p class="footer-heading">Company</p>
          <ul class="footer-nav">
            <li><a class="footer-link" href="about.php">About Omnifood</a></li>
            <li><a class="footer-link" href="business.php">For Business</a></li>
            <li><a class="footer-link" href="partners.php">Cooking partners</a></li>
            <li><a class="footer-link" href="careers.php">Careers</a></li>
          </ul>
        </nav>
        <nav class="nav-col">
          <p class="footer-heading">Resources</p>
          <ul class="footer-nav">
            <li><a class="footer-link" href="recipes.php">Recipe directory</a></li>
            <li><a class="footer-link" href="help.php">Help center</a></li>
            <li><a class="footer-link" href="privacy.php">Privacy & terms</a></li>
          </ul>
        </nav>
      </div>
    </footer>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
      function closeModal() { 
          document.getElementById('modalOverlay').style.display = 'none'; 
          // Optional: Clean URL so the popup doesn't return on manual refresh
          window.history.replaceState({}, document.title, "index.php");
      }
    </script>
    <a href="admin.php" class="admin-badge">
      <ion-icon name="shield-checkmark-outline"></ion-icon>
      <span>Admin</span>
    </a>
  </body>
</html>