<?php
session_start();
include 'connect.php';

// --- DEFAULT STATE: LOCKED ---
$unlocked = false;
$error = "🔒 Please <strong>Log In</strong> to view premium recipes.";
$show_login_btn = true;

// --- CHECK LOGIN & PAYMENT STATUS ---
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $show_login_btn = false; // User is already logged in
    
    // Check Database
    $stmt = $conn->prepare("SELECT payment_status FROM cafe WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $status = strtolower($row['payment_status']);
        
        if ($status === 'paid') {
            $unlocked = true; // UNLOCK CONTENT
        } elseif ($status === 'pending') {
            $error = "⏳ <strong>Payment Pending</strong><br>We have received your request.<br>Please wait for Admin approval.";
        } else {
            $error = "🔒 <strong>Access Denied</strong><br>You need to purchase the <strong>Complete Plan</strong>.";
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

        .btn-view {
            width: 100%; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background-color: #333; color: #fff; padding: 14px; border-radius: 9px; 
            font-weight: 600; font-size: 1.6rem; font-family: inherit; transition: 0.3s; 
        }
        .btn-view:hover { background-color: #e67e22; }

        /* LOCKED SCREEN */
        .access-box {
            max-width: 500px; margin: 80px auto; text-align: center; padding: 40px;
            background: white; border-radius: 11px; box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .locked-icon { font-size: 64px; color: #e67e22; margin-bottom: 20px; }
        .error-banner { background: #ffe3e3; color: #e03131; padding: 20px; border-radius: 9px; margin-bottom: 20px; font-size: 1.6rem; line-height: 1.5; }

        /* --- MODAL CSS --- */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(4px); }
        .modal-content { background: white; width: 95%; max-width: 900px; max-height: 90vh; border-radius: 12px; overflow-y: auto; position: relative; padding: 40px; animation: slideUp 0.3s ease-out; box-shadow: 0 20px 50px rgba(0,0,0,0.2); }
        @keyframes slideUp { from{transform: translateY(40px); opacity: 0;} to{transform: translateY(0); opacity: 1;} }
        
        /* Close Button Fix: Added Z-Index */
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
        
        <?php if (!$unlocked): ?>
        <div class="container">
            <div class="access-box">
                <ion-icon name="lock-closed-outline" class="locked-icon"></ion-icon>
                <h2 class="heading-secondary">The Nepali Cookbook</h2>
                <p style="margin-bottom: 30px; font-size: 1.8rem; line-height: 1.6;">
                    Unlock exclusive access to <strong>Secret Recipes</strong> from our local chefs.
                    <br>Only available to <strong>Complete Plan</strong> members.
                </p>
                
                <div class="error-banner"><?php echo $error; ?></div>
                
                <?php if ($show_login_btn): ?>
                    <a href="signin.php" class="btn btn--full" style="width: 100%; text-decoration: none; display: inline-block;">Log In Now</a>
                <?php else: ?>
                    <a href="index.php#pricing" class="btn btn--full" style="width: 100%; text-decoration: none; display: inline-block;">Go to Home / Buy Plan</a>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
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
            </div>
            <div class="container recipe-grid">
                
                <div class="recipe-card">
                    <div class="recipe-img-box"><img src="./img/meals/meal-1.jpg" class="recipe-img" alt="Jhol Momo"><span class="nepali-badge">🇳🇵 Authentic</span></div>
                    <div class="recipe-content">
                        <h3 class="recipe-title">Secret Jhol Momos</h3>
                        <p class="recipe-desc">The exact recipe for our famous sesame-tomato soup and juicy dumpling filling.</p>
                        <div class="card-footer">
                            <div class="chef-mini"><img src="./img/chef/Ram Sharan Thapa.jpg" alt="Chef Ram"><div class="chef-info"><h4>Ram Sharan</h4><span>Momo Expert</span></div></div>
                            <button class="btn-view" onclick="openModal('momo')"><ion-icon name="restaurant-outline"></ion-icon> View Recipe</button>
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
                            <button class="btn-view" onclick="openModal('dalbhat')"><ion-icon name="restaurant-outline"></ion-icon> View Recipe</button>
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
                            <button class="btn-view" onclick="openModal('thukpa')"><ion-icon name="restaurant-outline"></ion-icon> View Recipe</button>
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
                            <button class="btn-view" onclick="openModal('newari')"><ion-icon name="restaurant-outline"></ion-icon> View Recipe</button>
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
                            <button class="btn-view" onclick="openModal('sekuwa')"><ion-icon name="restaurant-outline"></ion-icon> View Recipe</button>
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
                            <button class="btn-view" onclick="openModal('selroti')"><ion-icon name="restaurant-outline"></ion-icon> View Recipe</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

    </main>

    <div id="recipeModal" class="modal-overlay">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            
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

    <script>
        // Use EventListener to ensure DOM is ready and close button binds correctly
        document.addEventListener("DOMContentLoaded", function() {
            
            // DOM ELEMENTS
            const modal = document.getElementById('recipeModal');
            const closeBtn = document.querySelector('.close-btn');
            const mTitle = document.getElementById('m-title');
            const mChef = document.getElementById('m-chef');
            const mIng = document.getElementById('m-ing');
            const mSteps = document.getElementById('m-steps');

            // RECIPE DATA
            const recipes = {
                'momo': { 
                    title: "Secret Jhol Momos", 
                    chef: "By Chef Ram Sharan Thapa", 
                    ingredients: ["500g Minced Meat", "Ginger Garlic Paste", "Timur (Szechuan Pepper)", "Momo Masala", "Flour Dough", "Roasted Tomatoes", "Sesame Seeds"], 
                    steps: ["Mix meat with spices and let it rest for 30 mins.", "Roll dough into small circles.", "Wrap the filling in the dough.", "Steam for 15 mins.", "Blend roasted tomatoes and sesame for the sauce."] 
                },
                'dalbhat': { 
                    title: "Thakali Dal Bhat", 
                    chef: "By Chef Bishal Gurung", 
                    ingredients: ["Black Lentils (Kalo Dal)", "Rice", "Jimbu (Himalayan Herb)", "Ghee", "Mustard Greens", "Gundruk"], 
                    steps: ["Pressure cook lentils for 20 mins.", "Temper with Ghee and Jimbu.", "Cook rice until fluffy.", "Stir fry greens with garlic.", "Serve hot on a brass plate."] 
                },
                'thukpa': { 
                    title: "Himalayan Thukpa", 
                    chef: "By Chef Dorje Sherpa", 
                    ingredients: ["Hand-pulled Noodles", "Bone Broth", "Veggies (Carrot, Cabbage)", "Soy Sauce", "Cumin Powder", "Green Chili"], 
                    steps: ["Boil the bone broth for 4 hours.", "Boil noodles separately.", "Sauté veggies in a wok.", "Add broth to veggies.", "Mix in noodles and serve hot."] 
                },
                'newari': { 
                    title: "Newari Samay Baji", 
                    chef: "By Chef Rahul Babu Shrestha", 
                    ingredients: ["Beaten Rice (Chiura)", "Grilled Meat (Choila)", "Ginger & Garlic", "Mustard Oil", "Black Soybeans", "Boiled Egg"], 
                    steps: ["Marinate grilled meat with spices and mustard oil.", "Fry soybeans until crunchy.", "Mix spices with beaten rice (optional).", "Arrange all items beautifully on a leaf plate."] 
                },
                'sekuwa': { 
                    title: "Dharan Sekuwa", 
                    chef: "By Chef Ram Sharan Thapa", 
                    ingredients: ["Goat or Pork Cubes", "Mustard Oil", "Cumin Powder", "Coriander Powder", "Timur", "Lemon Juice"], 
                    steps: ["Mix all spices with mustard oil.", "Marinate meat for at least 4 hours.", "Skewer the meat tightly.", "Grill over charcoal fire until slightly charred."] 
                },
                'selroti': { 
                    title: "Sel Roti & Aloo", 
                    chef: "By Chef Rahul Babu Shrestha", 
                    ingredients: ["Rice Flour", "Sugar", "Ghee", "Cardamom Powder", "Cooking Oil", "Water"], 
                    steps: ["Mix rice flour, sugar, ghee, and water to make a semi-liquid batter.", "Let the batter rest for 1 hour.", "Pour batter in a ring shape into hot oil.", "Fry until golden brown on both sides."] 
                }
            };

            // OPEN FUNCTION (Attached to window so HTML buttons can see it)
            window.openModal = function(key) {
                const data = recipes[key]; 
                if(!data) return;

                mTitle.innerText = data.title; 
                mChef.innerText = data.chef;
                
                // Fill Ingredients
                mIng.innerHTML = ""; 
                data.ingredients.forEach(i => mIng.innerHTML += `<li><ion-icon name="checkmark-outline"></ion-icon> ${i}</li>`);
                
                // Fill Steps
                mSteps.innerHTML = ""; 
                data.steps.forEach((s, i) => mSteps.innerHTML += `<div class="step-box"><h4>Step ${i+1}</h4><p>${s}</p></div>`);
                
                modal.style.display = 'flex';
            };

            // CLOSE FUNCTION
            function closeModalFunc() {
                modal.style.display = 'none';
            }

            // ATTACH EVENT LISTENER (Better than onclick)
            if(closeBtn) {
                closeBtn.addEventListener('click', closeModalFunc);
            }

            // CLICK OUTSIDE TO CLOSE
            window.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModalFunc();
                }
            });

        });
    </script>
    <footer class="footer"><div class="container" style="text-align: center;"><p class="copyright">Copyright &copy; 2025 by Omnifood Nepal.</p></div></footer>
</body>
</html>