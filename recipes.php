<?php
session_start();
include 'connect.php';

// --- 1. LOGOUT LOGIC ---
if (isset($_GET['logout'])) {
    unset($_SESSION['recipe_access']);
    unset($_SESSION['user_email']);
    session_destroy();
    header("Location: recipes.php");
    exit();
}

$error = "";
$unlocked = false;

// --- 2. HANDLE LOGIN / UNLOCK ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_email'])) {
    $email = htmlspecialchars(trim($_POST['email']));

    // Check if user bought the 'Complete Plan'
    $stmt = $conn->prepare("SELECT * FROM cafe WHERE email = ? AND order_items LIKE '%Complete Plan%'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['recipe_access'] = true;
        $_SESSION['user_email'] = $email;
        header("Location: recipes.php");
        exit();
    } else {
        $error = "❌ Access Denied. You need the 'Complete Plan' to view these recipes.";
    }
}

// --- 3. CHECK SESSION ---
if (isset($_SESSION['recipe_access']) && $_SESSION['recipe_access'] === true) {
    $unlocked = true;
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

        /* --- FEATURED SECTION --- */
        .section-featured { padding: 4.8rem 0 3.2rem 0; }
        .heading-featured { 
            font-size: 1.4rem; 
            text-transform: uppercase; 
            letter-spacing: 0.75px; 
            font-weight: 500; 
            text-align: center; 
            margin-bottom: 2.4rem; 
            color: #888; 
        }
        
        .logos { 
            display: flex; 
            justify-content: space-around; 
            align-items: center; 
            flex-wrap: wrap; 
            gap: 30px;
        }
        
        .logos img { 
            height: 3.2rem; 
            width: auto; 
            filter: grayscale(100%); 
            opacity: 0.6; 
            transition: all 0.3s;
            display: block; 
        }
        
        .logos img:hover { 
            filter: grayscale(0%); 
            opacity: 1; 
            transform: scale(1.1); 
        }

        /* --- CHEFS SECTION --- */
        .section-chefs { padding: 9.6rem 0; background-color: #fae5d3; } 
        .chefs-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; text-align: center; }
        .chef-profile img { width: 15rem; height: 15rem; border-radius: 50%; margin-bottom: 2rem; border: 5px solid #fff; transition: 0.3s; object-fit: cover;}
        .chef-profile:hover img { transform: scale(1.05); border-color: #e67e22; }
        .chef-name { font-size: 2rem; font-weight: 700; color: #333; margin-bottom: 0.5rem; }
        .chef-role { font-size: 1.4rem; color: #e67e22; text-transform: uppercase; font-weight: 500; }

        /* --- RECIPE GRID (Robust Layout) --- */
        .section-meals { padding: 9.6rem 0; }
        .recipe-grid { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 48px; 
            align-items: stretch; /* Ensures all cards stretch to same height */
        }

        .recipe-card {
            background: white; border-radius: 12px; overflow: hidden;
            box-shadow: 0 15px 30px rgba(0,0,0,0.08); transition: all 0.3s;
            display: flex; flex-direction: column; height: 100%;
        }
        .recipe-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.15); }
        
        /* Ensures image box is always consistent size regardless of image ratio */
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
        .access-input { width: 100%; padding: 16px; font-size: 18px; border: 1px solid #ddd; border-radius: 9px; margin-bottom: 20px; }
        .locked-icon { font-size: 64px; color: #e67e22; margin-bottom: 20px; }
        .error-banner { background: #ffe3e3; color: #e03131; padding: 15px; border-radius: 9px; margin-bottom: 20px; display: <?php echo $error ? 'block' : 'none'; ?>; }

        /* MODAL */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(4px); }
        .modal-content { background: white; width: 90%; max-width: 800px; max-height: 85vh; border-radius: 12px; overflow-y: auto; position: relative; padding: 40px; animation: slideUp 0.3s ease-out; }
        @keyframes slideUp { from{transform: translateY(40px); opacity: 0;} to{transform: translateY(0); opacity: 1;} }
        .close-btn { position: absolute; top: 20px; right: 25px; font-size: 3.6rem; cursor: pointer; color: #555; line-height: 0.6; }
        .close-btn:hover { color: #e67e22; }

        @media (max-width: 950px) { .recipe-grid { grid-template-columns: 1fr 1fr; } .chefs-grid { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 600px) { 
            .recipe-grid { grid-template-columns: 1fr; } 
            .chefs-grid { grid-template-columns: 1fr; } 
            .logos { flex-wrap: wrap; gap: 20px; justify-content: center; } 
            .logos img { height: 2.4rem; } 
        }
    </style>
</head>
<body>

    <header class="header">
        <a href="index.php"><img class="logo" src="./img/omnifood-logo.png" alt="Omnifood Logo"></a>
        <nav class="main-nav">
            <ul class="main-nav-list">
                <li><a class="main-nav-link" href="index.php">Home</a></li>
                <?php if ($unlocked): ?>
                    <li><a class="main-nav-link nav-cta" href="recipes.php?logout=true" style="background-color: #e74c3c;">Lock Cookbook</a></li>
                <?php else: ?>
                    <li><a class="main-nav-link nav-cta" href="index.php#pricing">Get Access</a></li>
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
                <form method="POST">
                    <input type="email" name="email" class="access-input" placeholder="Enter your registered email" required>
                    <button type="submit" name="verify_email" class="btn btn--full" style="width: 100%;">Unlock Recipes</button>
                </form>
            </div>
        </div>

        <?php else: ?>
        <section class="section-featured">
            <div class="container">
                <h2 class="heading-featured">Featured in Nepali Media</h2>
                <div class="logos">
                    <img src="img/logos/kathmandu-post.png" alt="The Kathmandu Post">
                    <img src="img/logos/foodmandu.png" alt="Foodmandu">
                    <img src="img/logos/onlinekhabar.png" alt="OnlineKhabar">
                    <img src="img/logos/himalayan-times.png" alt="The Himalayan Times">
                    <img src="img/logos/ronb.png" alt="Routine of Nepal Banda">
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
                            <div class="chef-mini"><img src="./img/chef-1.jpg" alt="Chef Ram"><div class="chef-info"><h4>Chef Ram</h4><span>Momo Expert</span></div></div>
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
                            <div class="chef-mini"><img src="./img/chef-2.jpg" alt="Chef Bishal"><div class="chef-info"><h4>Chef Bishal</h4><span>Thakali Master</span></div></div>
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
                            <div class="chef-mini"><img src="./img/chef-3.jpg" alt="Chef Dorje"><div class="chef-info"><h4>Chef Dorje</h4><span>Soup Master</span></div></div>
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
                            <div class="chef-mini"><img src="./img/chef-4.jpg" alt="Chef Arjun"><div class="chef-info"><h4>Chef Arjun</h4><span>Newari Cuisine</span></div></div>
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
                            <div class="chef-mini"><img src="./img/chef-1.jpg" alt="Chef Ram"><div class="chef-info"><h4>Chef Ram</h4><span>Grill Master</span></div></div>
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
                            <div class="chef-mini"><img src="./img/chef-4.jpg" alt="Chef Arjun"><div class="chef-info"><h4>Chef Arjun</h4><span>Festival Special</span></div></div>
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
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <div class="modal-header"><h2 class="modal-title" id="m-title"></h2><p class="modal-subtitle" id="m-chef"></p></div>
            <div class="recipe-details" style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 40px;">
                <div><h3 class="heading-tertiary">Ingredients</h3><ul class="ing-list" id="m-ing"></ul></div>
                <div><h3 class="heading-tertiary">Instructions</h3><div id="m-steps"></div></div>
            </div>
        </div>
    </div>

    <script>
        const recipes = {
            'momo': { title: "Secret Jhol Momos", chef: "By Chef Ram Bahadur", ingredients: ["500g Minced Meat", "Ginger Garlic Paste", "Timur (Szechuan Pepper)", "Momo Masala", "Flour Dough", "Roasted Tomatoes", "Sesame Seeds"], steps: ["Mix meat with spices.", "Roll dough and wrap filling.", "Steam for 15 mins.", "Blend sauce ingredients."] },
            'dalbhat': { title: "Thakali Dal Bhat", chef: "By Chef Bishal Gurung", ingredients: ["Black Lentils", "Rice", "Jimbu", "Ghee", "Mustard Greens", "Gundruk"], steps: ["Cook lentils.", "Temper with Ghee and Jimbu.", "Cook rice.", "Stir fry greens."] },
            'thukpa': { title: "Himalayan Thukpa", chef: "By Chef Dorje Sherpa", ingredients: ["Noodles", "Bone Broth", "Veggies", "Soy Sauce", "Cumin", "Chili"], steps: ["Boil noodles.", "Sauté veggies.", "Add broth.", "Mix."] },
            'newari': { title: "Newari Samay Baji", chef: "By Chef Arjun Shrestha", ingredients: ["Beaten Rice", "Grilled Meat (Choila)", "Ginger", "Mustard Oil", "Black Soybeans", "Egg"], steps: ["Marinate meat.", "Fry beans.", "Arrange on plate."] },
            'sekuwa': { title: "Dharan Sekuwa", chef: "By Chef Ram Bahadur", ingredients: ["Meat Cubes", "Mustard Oil", "Cumin", "Coriander", "Timur"], steps: ["Marinate meat for 4 hours.", "Skewer.", "Grill over charcoal."] },
            'selroti': { title: "Sel Roti & Aloo", chef: "By Chef Arjun Shrestha", ingredients: ["Rice Flour", "Sugar", "Ghee", "Cardamom", "Oil"], steps: ["Make batter.", "Rest 1 hour.", "Pour in ring shape in oil.", "Fry till golden."] }
        };
        const modal = document.getElementById('recipeModal');
        const mTitle = document.getElementById('m-title'), mChef = document.getElementById('m-chef'), mIng = document.getElementById('m-ing'), mSteps = document.getElementById('m-steps');

        function openModal(key) {
            const data = recipes[key]; if(!data) return;
            mTitle.innerText = data.title; mChef.innerText = data.chef;
            mIng.innerHTML = ""; data.ingredients.forEach(i => mIng.innerHTML += `<li>📍 ${i}</li>`);
            mSteps.innerHTML = ""; data.steps.forEach((s, i) => mSteps.innerHTML += `<div style="margin-bottom:15px"><h4>Step ${i+1}</h4><p style="font-size:1.6rem">${s}</p></div>`);
            modal.style.display = 'flex';
        }
        function closeModal() { modal.style.display = 'none'; }
        window.onclick = function(e) { if (e.target == modal) closeModal(); }
    </script>
    <footer class="footer"><div class="container" style="text-align: center;"><p class="copyright">Copyright &copy; 2025 by Omnifood Nepal.</p></div></footer>
</body>
</html>