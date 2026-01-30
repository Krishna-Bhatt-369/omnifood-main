<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Cooking Partners - Omnifood Nepal</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/general.css">
    <style>
        /* --- CHEF HERO --- */
        .chef-hero {
            background-color: #fdf2e9;
            padding: 9.6rem 0;
            text-align: center;
        }

        /* --- DETAILED CHEF SECTIONS --- */
        .section-chef-bio {
            padding: 9.6rem 0;
            border-bottom: 1px solid #eee;
        }
        
        /* Alternating Layout */
        .chef-bio-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 6.4rem;
            align-items: center;
        }

        .chef-image-container {
            position: relative;
        }

        .chef-big-img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        
        .chef-big-img:hover {
            transform: scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .chef-content h3 {
            font-size: 3rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .chef-title {
            display: inline-block;
            background-color: #e67e22;
            color: white;
            font-size: 1.4rem;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 100px;
            margin-bottom: 2.4rem;
            font-weight: 700;
        }

        .chef-bio-text {
            font-size: 1.8rem;
            line-height: 1.8;
            color: #555;
            margin-bottom: 3rem;
        }

        .signature-dish {
            background-color: #fdf2e9;
            padding: 20px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .signature-dish strong {
            font-size: 1.6rem;
            color: #333;
        }
        
        /* Join Team Section */
        .join-team {
            background-color: #e67e22;
            color: white;
            padding: 6rem 0;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 850px) {
            .chef-bio-grid { grid-template-columns: 1fr; gap: 3.2rem; }
            .section-chef-bio:nth-child(even) .chef-bio-grid { direction: ltr; } /* Keeps image on top for mobile */
        }
    </style>
</head>
<body>

    <header class="header">
        <a href="index.php"><img class="logo" src="./img/omnifood-logo.png" alt="Omnifood Logo"></a>
        <nav class="main-nav">
            <ul class="main-nav-list">
                <li><a class="main-nav-link" href="index.php">Home</a></li>
                <li><a class="main-nav-link" href="business.php">For Business</a></li>
                <li><a class="main-nav-link" href="recipes.php">Recipes</a></li>
                <li><a class="main-nav-link nav-cta" href="index.php#pricing">Get Meals</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="chef-hero">
            <div class="container">
                <span class="subheading">Culinary Artists</span>
                <h1 class="heading-primary">Meet the Masters Behind Your Meals</h1>
                <p class="hero-description" style="margin: 0 auto; max-width: 800px;">
                    At Omnifood Nepal, we don't just hire cooks. We partner with local legends who have spent decades perfecting their craft. From the mountains of Mustang to the streets of Kathmandu, here are the hands that feed you.
                </p>
            </div>
        </section>

        <section class="section-chef-bio">
            <div class="container chef-bio-grid">
                <div class="chef-image-container">
                    <img src="./img/chef/Ram Sharan Thapa.jpg" alt="Ram Sharan Thapa" class="chef-big-img">
                </div>
                <div class="chef-content">
                    <h3>Ram Sharan Thapa</h3>
                    <span class="chef-title">Momo Specialist • 20 Years Experience</span>
                    <p class="chef-bio-text">
                        Ram Sharan started his journey in a small street cart in Bouddha. He believes that the secret to the perfect Momo lies not in the dough, but in the ratio of onions to meat in the keema (filling). He grinds his own spices every morning at 4 AM to ensure freshness.
                    </p>
                    <div class="signature-dish">
                        <ion-icon name="flame-outline" style="font-size: 3rem; color: #e67e22;"></ion-icon>
                        <div>
                            <strong>Signature Dish:</strong><br>
                            Jhol Momo with Roasted Tomato & Sesame Achar.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-chef-bio">
            <div class="container chef-bio-grid" style="direction: rtl;"> <div class="chef-image-container" style="direction: ltr;">
                    <img src="./img/chef/Bishal Gurung.webp" alt="Bishal Gurung" class="chef-big-img">
                </div>
                <div class="chef-content" style="direction: ltr;">
                    <h3>Bishal Gurung</h3>
                    <span class="chef-title">Thakali Master • From Mustang</span>
                    <p class="chef-bio-text">
                        Born in the high hills of Mustang, Bishal brings the authentic taste of the Thakali kitchen to Kathmandu. He insists on using 'Jimbu' (a Himalayan herb) sourced directly from his home village. His cooking philosophy is simple: "Respect the ingredient, and the food will respect you."
                    </p>
                    <div class="signature-dish">
                        <ion-icon name="leaf-outline" style="font-size: 3rem; color: #e67e22;"></ion-icon>
                        <div>
                            <strong>Signature Dish:</strong><br>
                            Traditional Thakali Set with Fermented Gundruk.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-chef-bio">
            <div class="container chef-bio-grid">
                <div class="chef-image-container">
                    <img src="./img/chef/Dorje Sherpa.jpeg" alt="Dorje Sherpa" class="chef-big-img">
                </div>
                <div class="chef-content">
                    <h3>Dorje Sherpa</h3>
                    <span class="chef-title">Soup Expert • Himalayan Heritage</span>
                    <p class="chef-bio-text">
                        Dorje specializes in foods that warm the soul. Growing up in the cold climate of Solukhumbu, he mastered the art of bone broths and hand-pulled noodles. His Thukpa is cooked for 12 hours to extract every bit of calcium and flavor, making it the healthiest bowl of soup you'll ever eat.
                    </p>
                    <div class="signature-dish">
                        <ion-icon name="water-outline" style="font-size: 3rem; color: #e67e22;"></ion-icon>
                        <div>
                            <strong>Signature Dish:</strong><br>
                            Spicy Buff Thukpa with Hand-pulled Noodles.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-chef-bio">
            <div class="container chef-bio-grid" style="direction: rtl;">
                <div class="chef-image-container" style="direction: ltr;">
                    <img src="./img/chef/Rahul Babu Shrestha.avif" alt="Rahul Babu Shrestha" class="chef-big-img">
                </div>
                <div class="chef-content" style="direction: ltr;">
                    <h3>Rahul Babu Shrestha</h3>
                    <span class="chef-title">Newari Cuisine • Guardian of Tradition</span>
                    <p class="chef-bio-text">
                        Rahul represents the rich culinary history of the Kathmandu Valley. He specializes in 'Bhoj' (Feast) items. From spicy Choila to crunchy beaten rice, his food is a celebration of festivals. He ensures that every spice mix is made using traditional stone grinders (Silauto).
                    </p>
                    <div class="signature-dish">
                        <ion-icon name="bonfire-outline" style="font-size: 3rem; color: #e67e22;"></ion-icon>
                        <div>
                            <strong>Signature Dish:</strong><br>
                            Samay Baji Platter & Sel Roti.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="join-team">
            <div class="container">
                <h2 class="heading-secondary" style="color: white; margin-bottom: 2rem;">Are you a local culinary master?</h2>
                <p style="font-size: 1.8rem; margin-bottom: 4rem; opacity: 0.9;">
                    We are always looking for passionate chefs to join our cooking partner network. 
                    <br>Help us deliver authentic taste to thousands of customers.
                </p>
                <a href="index.php#contact" class="btn" style="background: white; color: #333;">Join Omnifood Partners</a>
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
                    <li><a class="footer-link" href="business.php">For Business</a></li>
                    <li><a class="footer-link" href="chefs.php" style="color: #e67e22; font-weight: bold;">Cooking partners</a></li>
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