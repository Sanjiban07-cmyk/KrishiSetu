<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | KrishiSetu</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
            background: var(--background);
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .login-brand-logo {
            width: 42px;
            height: 42px;
        }

        .login-brand-logo svg {
            width: 100%;
            height: 100%;
        }

        .login-brand-name {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
        }

        .login-subtitle {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .login-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 32px;
        }

        .login-title {
            font-size: 25px;
            margin-bottom: 7px;
            color: var(--text-primary);
        }

        .login-description {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 25px;
        }

        .login-form-group {
            margin-bottom: 20px;
        }

        .login-label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .login-input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 15px;
            outline: none;
            transition: 0.2s ease;
        }

        .login-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(8, 116, 67, 0.1);
        }

        .login-button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: var(--radius-md);
            background: var(--primary);
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .login-button:hover {
            background: var(--primary-dark);
        }

        .login-register {
            text-align: center;
            margin-top: 22px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .login-register a {
            color: var(--primary);
            font-weight: 600;
        }

        .login-back {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
        }

        .login-back a {
            color: var(--text-secondary);
        }

        @media (max-width: 500px) {
            .login-card {
                padding: 24px 20px;
            }

            .login-title {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>

<div class="login-page">

    <div class="login-wrapper">

        <!-- Brand -->
        <div class="login-header">

            <a href="index.php" class="login-brand">

                <span class="login-brand-logo">
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

                <span class="login-brand-name">
                    KrishiSetu
                </span>

            </a>

            <div class="login-subtitle">
                Smart Procurement Assistance for Farmers
            </div>

        </div>


        <!-- Login Card -->
        <div class="login-card">

            <h1 class="login-title">
                Welcome Back
            </h1>

            <p class="login-description">
                Login to access your procurement dashboard.
            </p>


            <form action="login_process.php" method="POST">

                <!-- Mobile -->
                <div class="login-form-group">

                    <label class="login-label" for="mobile">
                        Mobile Number
                    </label>

                    <input
                        type="tel"
                        id="mobile"
                        name="mobile"
                        class="login-input"
                        placeholder="Enter your mobile number"
                        maxlength="15"
                        required
                    >

                </div>


                <!-- Password -->
                <div class="login-form-group">

                    <label class="login-label" for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="login-input"
                        placeholder="Enter your password"
                        required
                    >

                </div>


                <!-- Login Button -->
                <button type="submit" class="login-button">
                    Login
                </button>

            </form>


            <!-- Register -->
            <div class="login-register">

                Don't have an account?
                <a href="register.php">Register here</a>

            </div>


            <!-- Back -->
            <div class="login-back">

                <a href="index.php">
                    ← Back to KrishiSetu
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>