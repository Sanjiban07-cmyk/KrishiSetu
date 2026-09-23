<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>KrishiSetu | Smart Procurement Assistance</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* ================================
           LANDING PAGE
           ================================ */

        .navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-inner {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

 .brand-logo {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.brand-logo svg {
    width: 100%;
    height: 100%;
    display: block;
}

        .brand-name {
            font-size: 23px;
            font-weight: 700;
            color: var(--primary);
        }

        .brand-subtitle {
            display: block;
            font-size: 9px;
            color: var(--text-secondary);
            letter-spacing: 0.3px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .nav-links a {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .language {
            border: none;
            background: transparent;
            color: var(--text-primary);
            font-size: 14px;
            cursor: pointer;
        }


        /* HERO */

        .hero {
            background:
                linear-gradient(
                    90deg,
                    #f5faf6 0%,
                    #f5faf6 48%,
                    rgba(245, 250, 246, 0.85) 65%,
                    rgba(245, 250, 246, 0.25) 100%
                );
            min-height: 570px;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            max-width: 650px;
            padding: 80px 0;
        }

        .hero-tag {
            display: inline-block;
            background: var(--primary-light);
            color: var(--primary);
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .hero h1 {
            font-size: 52px;
            line-height: 1.12;
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        .hero h1 span {
            color: var(--primary);
        }

        .hero-text {
            font-size: 18px;
            color: var(--text-secondary);
            max-width: 580px;
            margin-bottom: 30px;
        }

        .hero-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 40px;
        }

        .hero-buttons .btn {
            min-width: 145px;
        }

        .hero-features {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
        }

        .hero-feature {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .hero-feature-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }
        .hero-feature-icon img {
    width: 17px;
    height: 17px;
    display: block;
}


        /* HERO IMAGE */

        .hero-image {
            position: absolute;
            right: 0;
            top: 0;
            width: 48%;
            height: 100%;
            background:
                linear-gradient(
                    90deg,
                    #f5faf6 0%,
                    rgba(245,250,246,0) 20%
                ),
                url("assets/images/farmer-field.jpg")
                center/cover no-repeat;
        }


        /* FEATURES */

        .features {
            background: white;
            padding: 65px 0;
        }

        .section-heading {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-heading h2 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .section-heading p {
            color: var(--text-secondary);
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .feature-card {
            padding: 25px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            text-align: center;
            transition: 0.2s ease;
        }

        .feature-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-3px);
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            margin: 0 auto 15px;
            border-radius: 12px;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .feature-icon img {
    width: 26px;
    height: 26px;
    display: block;
}

        .feature-card h3 {
            font-size: 17px;
            margin-bottom: 8px;
        }

        .feature-card p {
            font-size: 14px;
            color: var(--text-secondary);
        }


        /* TRUST SECTION */

        .trust-section {
            background: var(--primary-light);
            padding: 55px 0;
        }

        .trust-content {
            text-align: center;
            max-width: 750px;
            margin: auto;
        }

        .trust-content h2 {
            font-size: 30px;
            margin-bottom: 12px;
        }

        .trust-content p {
            color: var(--text-secondary);
        }


        /* FOOTER */

        .footer {
            background: #12372a;
            color: white;
            padding: 35px 0;
        }

        .footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .footer-brand {
            font-weight: 700;
            font-size: 18px;
        }

        .footer-text {
            font-size: 13px;
            color: #c7d8d0;
        }


        /* MOBILE */

        @media (max-width: 900px) {

            .nav-links {
                display: none;
            }

            .hero {
                min-height: auto;
            }

            .hero-image {
                display: none;
            }

            .hero-content {
                max-width: 100%;
                padding: 65px 0;
            }

            .hero h1 {
                font-size: 40px;
            }

            .feature-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {

            .nav-actions .language {
                display: none;
            }

            .nav-actions .btn-outline {
                display: none;
            }

            .brand-name {
                font-size: 20px;
            }

            .hero h1 {
                font-size: 34px;
            }

            .hero-text {
                font-size: 16px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .hero-buttons .btn {
                width: 100%;
            }

            .hero-features {
                flex-direction: column;
                gap: 12px;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .footer-inner {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<!-- ================================
     NAVBAR
     ================================ -->

<header class="navbar">

    <div class="container nav-inner">

<a href="index.php" class="brand">

    <span class="brand-logo" aria-hidden="true">
        <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M39 7C25 8 13 14 10 26c-2 8 3 13 10 12
                   11-1 17-12 19-31Z"
                fill="#087443"
            />
            <path
                d="M10 39c7-9 14-15 24-20"
                fill="none"
                stroke="#055c35"
                stroke-width="3"
                stroke-linecap="round"
            />
            <path
                d="M22 25c-1-5 0-10 4-14"
                fill="none"
                stroke="#f2b84b"
                stroke-width="2.5"
                stroke-linecap="round"
            />
        </svg>
    </span>

    <div>
        <div class="brand-name">KrishiSetu</div>

        <span class="brand-subtitle">
            Farmers • Centres • Better Tomorrow
        </span>
    </div>

</a>


        <nav class="nav-links">

            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#features">Features</a>
            <a href="#contact">Contact</a>

        </nav>


        <div class="nav-actions">

            <select class="language">
                <option>EN</option>
                <option>বাংলা</option>
                <option>हिन्दी</option>
            </select>

            <a href="login.php" class="btn btn-outline">
                Login
            </a>

           <a href="farmer/register.php" class="btn btn-primary">
                Register
            </a>

        </div>

    </div>

</header>


<!-- ================================
     HERO
     ================================ -->

<main>

<section class="hero" id="home">

    <div class="hero-image"></div>

    <div class="container">

        <div class="hero-content">

            <div class="hero-tag">
                🌾 Smart Procurement Assistance
            </div>

            <h1>
                Connecting Farmers
                <br>
                to <span>Better Opportunities</span>
            </h1>

            <p class="hero-text">
                KrishiSetu helps farmers find nearby procurement
                centres, choose suitable slots, and track their
                procurement and payment status — all in one place.
            </p>


            <div class="hero-buttons">

                <a href="login.php" class="btn btn-primary">
                    Get Started →
                </a>

                <a href="#features" class="btn btn-outline">
                    Learn More
                </a>

            </div>


<div class="hero-feature">
    <span class="hero-feature-icon">
        <img src="assets/icons/location.svg" alt="">
    </span>
    Find Nearby Centres
</div>

<div class="hero-feature">
    <span class="hero-feature-icon">
        <img src="assets/icons/recommendation.svg" alt="">
    </span>
    Smart Recommendation
</div>

<div class="hero-feature">
    <span class="hero-feature-icon">
        <img src="assets/icons/calendar.svg" alt="">
    </span>
    Book Procurement Slots
</div>

<div class="hero-feature">
    <span class="hero-feature-icon">
        <img src="assets/icons/status.svg" alt="">
    </span>
    Track Status & Payment
</div>

            </div>

        </div>

    </div>

</section>


<!-- ================================
     FEATURES
     ================================ -->

<section class="features" id="features">

    <div class="container">

        <div class="section-heading">

            <h2>How KrishiSetu Helps</h2>

            <p>
                Simple tools designed to make procurement easier for farmers.
            </p>

        </div>


        <div class="feature-grid">

            <div class="feature-card">

                <div class="feature-icon">
    <img src="assets/icons/location.svg" alt="">
</div>

                <h3>Find Centres</h3>

                <p>
                    Discover nearby authorised procurement centres
                    and view their availability.
                </p>

            </div>


            <div class="feature-card">

               <div class="feature-icon">
 <img src="/krishiSetu/assets/icons/recommendation.svg" alt="Smart Recommendation">
</div>

                <h3>Smart Recommendation</h3>

                <p>
                    Compare distance, queue, capacity and slot
                    availability to find a suitable option.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">📅</div>

                <h3>Book Your Slot</h3>

                <p>
                    Select an available procurement slot and receive
                    a booking token.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">📊</div>

                <h3>Track Status</h3>

                <p>
                    Keep track of your booking, procurement and
                    payment status.
                </p>

            </div>

        </div>

    </div>

</section>


<!-- ================================
     TRUST SECTION
     ================================ -->

<section class="trust-section" id="about">

    <div class="container">

        <div class="trust-content">

            <h2>
                Simple. Transparent. Farmer-Focused.
            </h2>

            <p>
                KrishiSetu acts as an assistance layer that makes
                procurement information easier to understand and
                act upon — helping farmers make better decisions
                about where and when to procure.
            </p>

        </div>

    </div>

</section>

</main>


<!-- ================================
     FOOTER
     ================================ -->

<footer class="footer" id="contact">

    <div class="container footer-inner">

        <div class="footer-brand">
            🌿 KrishiSetu
        </div>

        <div class="footer-text">
            Smart Procurement Assistance for Farmers
        </div>

        <div class="footer-text">
            © 2026 KrishiSetu
        </div>

    </div>

</footer>

</body>
</html>