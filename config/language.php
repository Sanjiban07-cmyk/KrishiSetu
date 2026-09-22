<?php

/*
 * KrishiSetu Global Language System
 *
 * Supported languages:
 * en = English
 * hi = Hindi
 * bn = Bengali
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$allowedLanguages = [
    'en',
    'hi',
    'bn'
];

/*
 * Make sure a valid language is selected
 */
if (
    !isset($_SESSION['language']) ||
    !in_array($_SESSION['language'], $allowedLanguages, true)
) {
    $_SESSION['language'] = 'en';
}

$currentLanguage = $_SESSION['language'];

/*
 * Load selected language file
 */
$languageFile = __DIR__ . '/../languages/' . $currentLanguage . '.php';

if (!file_exists($languageFile)) {
    $languageFile = __DIR__ . '/../languages/en.php';
}

$translations = require $languageFile;


/*
 * Main translation function
 */
function t(string $key, array $replace = []): string
{
    global $translations;

    $text = $translations[$key] ?? $key;

    foreach ($replace as $placeholder => $value) {
        $text = str_replace(
            '{' . $placeholder . '}',
            (string)$value,
            $text
        );
    }

    return $text;
}


/*
 * Translate database status values
 */
function translateStatus(?string $status): string
{
    if ($status === null || $status === '') {
        return '';
    }

    $key = 'status_' . strtolower($status);

    return t($key);
}

?>