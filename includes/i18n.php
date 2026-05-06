<?php
/**
 * Simple i18n for visitor/kiosk screens.
 */

define('SUPPORTED_LANGS', ['en', 'si', 'ta']);
define('DEFAULT_LANG', 'en');

function getPreferredLangFromHeader($acceptLanguage) {
    if (!$acceptLanguage) return DEFAULT_LANG;
    $acceptLanguage = strtolower($acceptLanguage);
    // Very small mapping for Sinhala/Tamil.
    if (strpos($acceptLanguage, 'si') !== false) return 'si';
    if (strpos($acceptLanguage, 'ta') !== false) return 'ta';
    return DEFAULT_LANG;
}

function currentLang() {
    if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], SUPPORTED_LANGS, true)) {
        return $_SESSION['lang'];
    }
    return DEFAULT_LANG;
}

function setLang($lang) {
    if (in_array($lang, SUPPORTED_LANGS, true)) {
        $_SESSION['lang'] = $lang;
    }
}

// Initialize language choice
if (session_status() === PHP_SESSION_ACTIVE) {
    $langParam = $_GET['lang'] ?? null;
    if ($langParam && in_array($langParam, SUPPORTED_LANGS, true)) {
        setLang($langParam);
    } elseif (!isset($_SESSION['lang'])) {
        setLang(getPreferredLangFromHeader($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    }
}

function loadTranslations($lang) {
    $base = dirname(__DIR__);
    $path = $base . '/languages/' . $lang . '.php';
    if (is_file($path)) {
        $data = require $path;
        if (is_array($data)) return $data;
    }
    return [];
}

// Load current and fallback
$__i18n_lang = currentLang();
$__i18n = loadTranslations($__i18n_lang);
$__i18n_fallback = ($__i18n_lang === DEFAULT_LANG) ? $__i18n : loadTranslations(DEFAULT_LANG);

function t($key, $vars = []) {
    global $__i18n, $__i18n_fallback;
    $value = $__i18n[$key] ?? $__i18n_fallback[$key] ?? $key;
    if (!is_string($value)) {
        $value = $key;
    }
    foreach ($vars as $k => $v) {
        $value = str_replace('{' . $k . '}', (string)$v, $value);
    }
    return $value;
}

