<?php
session_start();

/*
 * Global language selection
 * The selected language is stored in session and can be used
 * throughout the farmer panel after login.
 */
$languages = [
    'en' => 'English',
    'hi' => 'हिन्दी',
    'bn' => 'বাংলা'
];

$current_language = $_SESSION['language'] ?? 'en';

if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $languages)) {
    $current_language = $_GET['lang'];
    $_SESSION['language'] = $current_language;
}

$text = [
    'en' => [
        'title' => 'Login | KrishiSetu',
        'subtitle' => 'Smart Procurement Assistance for Farmers',
        'welcome' => 'Welcome Back',
        'description' => 'Login to access your procurement dashboard.',
        'mobile' => 'Mobile Number',
        'mobile_placeholder' => 'Enter your mobile number',
        'password' => 'Password',
        'password_placeholder' => 'Enter your password',
        'login' => 'Login',
        'no_account' => "Don't have an account?",
        'register' => 'Register here',
        'back' => '← Back to KrishiSetu',
        'language' => 'Language'
    ],
    'hi' => [
        'title' => 'लॉगिन | कृषिसेतु',
        'subtitle' => 'किसानों के लिए स्मार्ट खरीद सहायता',
        'welcome' => 'वापसी पर स्वागत है',
        'description' => 'अपने खरीद डैशबोर्ड तक पहुँचने के लिए लॉगिन करें।',
        'mobile' => 'मोबाइल नंबर',
        'mobile_placeholder' => 'अपना मोबाइल नंबर दर्ज करें',
        'password' => 'पासवर्ड',
        'password_placeholder' => 'अपना पासवर्ड दर्ज करें',
        'login' => 'लॉगिन',
        'no_account' => 'क्या आपका खाता नहीं है?',
        'register' => 'यहाँ रजिस्टर करें',
        'back' => '← कृषिसेतु पर वापस जाएँ',
        'language' => 'भाषा'
    ],
    'bn' => [
        'title' => 'লগইন | কৃষিসেতু',
        'subtitle' => 'কৃষকদের জন্য স্মার্ট ক্রয় সহায়তা',
        'welcome' => 'আবার স্বাগতম',
        'description' => 'আপনার ক্রয় ড্যাশবোর্ডে প্রবেশ করতে লগইন করুন।',
        'mobile' => 'মোবাইল নম্বর',
        'mobile_placeholder' => 'আপনার মোবাইল নম্বর লিখুন',
        'password' => 'পাসওয়ার্ড',
        'password_placeholder' => 'আপনার পাসওয়ার্ড লিখুন',
        'login' => 'লগইন',
        'no_account' => 'আপনার কি অ্যাকাউন্ট নেই?',
        'register' => 'এখানে রেজিস্টার করুন',
        'back' => '← কৃষিসেতুতে ফিরে যান',
        'language' => 'ভাষা'
    ]
];

$t = $text[$current_language];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($t['title']) ?></title>

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
            text-decoration: none;
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

        .language-box {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 18px;
        }

        .language-box label {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .language-select {
            padding: 9px 34px 9px 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--surface);
            color: var(--text-primary);
            font-size: 14px;
            cursor: pointer;
            outline: none;
        }

        .language-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(8, 116, 67, 0.1);
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
            box-sizing: border-box;
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

        <div class="login-header">

            <a href="index.php" class="login-brand">

                <span class="login-brand-logo">
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

                <span class="login-brand-name">KrishiSetu</span>

            </a>

            <div class="login-subtitle">
                <?= htmlspecialchars($t['subtitle']) ?>
            </div>

        </div>

        <!-- Language selector -->
        <div class="language-box">
            <label for="language"><?= htmlspecialchars($t['language']) ?>:</label>

            <select
                id="language"
                class="language-select"
                onchange="changeLanguage(this.value)"
            >
                <?php foreach ($languages as $code => $name): ?>
                    <option
                        value="<?= htmlspecialchars($code) ?>"
                        <?= $current_language === $code ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="login-card">

            <h1 class="login-title">
                <?= htmlspecialchars($t['welcome']) ?>
            </h1>

            <p class="login-description">
                <?= htmlspecialchars($t['description']) ?>
            </p>

            <form action="login_process.php" method="POST">

                <!-- Keep selected language when login is submitted -->
                <input
                    type="hidden"
                    name="language"
                    value="<?= htmlspecialchars($current_language) ?>"
                >

                <div class="login-form-group">

                    <label class="login-label" for="mobile">
                        <?= htmlspecialchars($t['mobile']) ?>
                    </label>

                    <input
                        type="tel"
                        id="mobile"
                        name="mobile"
                        class="login-input"
                        placeholder="<?= htmlspecialchars($t['mobile_placeholder']) ?>"
                        maxlength="15"
                        required
                    >

                </div>

                <div class="login-form-group">

                    <label class="login-label" for="password">
                        <?= htmlspecialchars($t['password']) ?>
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="login-input"
                        placeholder="<?= htmlspecialchars($t['password_placeholder']) ?>"
                        required
                    >

                </div>

                <button type="submit" class="login-button">
                    <?= htmlspecialchars($t['login']) ?>
                </button>

            </form>

            <div class="login-register">
                <?= htmlspecialchars($t['no_account']) ?>
                <a href="register.php?lang=<?= urlencode($current_language) ?>">
                    <?= htmlspecialchars($t['register']) ?>
                </a>
            </div>

            <div class="login-back">
                <a href="index.php">
                    <?= htmlspecialchars($t['back']) ?>
                </a>
            </div>

        </div>

    </div>

</div>

<script>
function changeLanguage(language) {
    const url = new URL(window.location.href);
    url.searchParams.set('lang', language);
    window.location.href = url.toString();
}
</script>

</body>
</html>
