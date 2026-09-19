<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | KrishiSetu</title>

<link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .register-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
            background: var(--background);
        }

        .register-wrapper {
            width: 100%;
            max-width: 500px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .register-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .register-brand-logo {
            width: 42px;
            height: 42px;
        }

        .register-brand-logo svg {
            width: 100%;
            height: 100%;
        }

        .register-brand-name {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
        }

        .register-subtitle {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .register-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 32px;
        }

        .register-title {
            font-size: 25px;
            margin-bottom: 7px;
            color: var(--text-primary);
        }

        .register-description {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 25px;
        }

        .register-form-group {
            margin-bottom: 18px;
        }

        .register-label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .register-input,
        .register-select {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 15px;
            outline: none;
            background: white;
            color: var(--text-primary);
        }

        .register-input:focus,
        .register-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(8, 116, 67, 0.1);
        }

        .register-button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: var(--radius-md);
            background: var(--primary);
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 5px;
        }

        .register-button:hover {
            background: var(--primary-dark);
        }

        .register-login {
            text-align: center;
            margin-top: 22px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .register-login a {
            color: var(--primary);
            font-weight: 600;
        }

        .register-back {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
        }

        .register-back a {
            color: var(--text-secondary);
        }

        @media (max-width: 500px) {
            .register-card {
                padding: 24px 20px;
            }

            .register-title {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>

<div class="register-page">

    <div class="register-wrapper">

        <!-- Brand -->
        <div class="register-header">

          <a href="../index.php" class="register-brand">

                <span class="register-brand-logo">

                    <svg viewBox="0 0 48 48"
                         xmlns="http://www.w3.org/2000/svg">

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

                <span class="register-brand-name">
                    KrishiSetu
                </span>

            </a>

            <div class="register-subtitle">
                Smart Procurement Assistance for Farmers
            </div>

        </div>


        <!-- Registration Card -->
        <div class="register-card">

            <h1 class="register-title">
                Create Farmer Account
            </h1>

            <p class="register-description">
                Register to access procurement services and manage your bookings.
            </p>


            <form action="register_process.php" method="POST">

                <!-- Name -->
                <div class="register-form-group">

                    <label class="register-label" for="name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="register-input"
                        placeholder="Enter your full name"
                        required
                    >

                </div>


                <!-- Mobile -->
                <div class="register-form-group">

                    <label class="register-label" for="mobile">
                        Mobile Number
                    </label>

                    <input
                        type="tel"
                        id="mobile"
                        name="mobile"
                        class="register-input"
                        placeholder="Enter your mobile number"
                        maxlength="15"
                        required
                    >

                </div>


                <!-- Village -->
                <div class="register-form-group">

                    <label class="register-label" for="village">
                        Village
                    </label>

                    <input
                        type="text"
                        id="village"
                        name="village"
                        class="register-input"
                        placeholder="Enter your village"
                        required
                    >

                </div>


                <!-- District -->
                <div class="register-form-group">

                    <label class="register-label" for="district">
                        District
                    </label>

                    <input
                        type="text"
                        id="district"
                        name="district"
                        class="register-input"
                        placeholder="Enter your district"
                        required
                    >

                </div>


                <!-- State -->
                <div class="register-form-group">

                    <label class="register-label" for="state">
                        State
                    </label>

                    <input
                        type="text"
                        id="state"
                        name="state"
                        class="register-input"
                        placeholder="Enter your state"
                        value="West Bengal"
                        required
                    >

                </div>


                <!-- Password -->
                <div class="register-form-group">

                    <label class="register-label" for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="register-input"
                        placeholder="Create a password"
                        required
                    >

                </div>


                <!-- Confirm Password -->
                <div class="register-form-group">

                    <label class="register-label" for="confirm_password">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="register-input"
                        placeholder="Confirm your password"
                        required
                    >

                </div>


                <!-- Language -->
                <div class="register-form-group">

                    <label class="register-label" for="language">
                        Preferred Language
                    </label>

                    <select
                        id="language"
                        name="language"
                        class="register-select"
                    >

                        <option value="en">English</option>
                        <option value="bn">বাংলা (Bengali)</option>
                        <option value="hi">हिन्दी (Hindi)</option>

                    </select>

                </div>


                <!-- Button -->
                <button type="submit" class="register-button">
                    Create Account
                </button>

            </form>


            <!-- Login -->
            <div class="register-login">

                Already have an account?
                <a href="../login.php">Login here</a>

            </div>


            <!-- Back -->
            <div class="register-back">

               <a href="../index.php">
                    ← Back to KrishiSetu
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>