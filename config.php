<?php
session_start();
date_default_timezone_set('Africa/Dar_es_Salaam');

// Site Configuration for Tanzania
define('SITE_NAME', 'Chakula Express');
define('SITE_URL', 'http://localhost/chakula-express');
define('CURRENCY', 'TSh');
define('CURRENCY_SYMBOL', 'TSh');
define('COUNTRY_CODE', 'TZ');
define('COUNTRY_PHONE_CODE', '+255');
define('DEFAULT_CITY', 'Dar es Salaam');

// Business Details
define('BUSINESS_NAME', 'Chakula Express Tanzania');
define('VAT_RATE', 0.18); // 18% VAT in Tanzania

// Payment Gateways
define('MPESA_CONSUMER_KEY', 'YOUR_MPESA_KEY');
define('MPESA_CONSUMER_SECRET', 'YOUR_MPESA_SECRET');
define('MPESA_SHORTCODE', 'YOUR_SHORTCODE');
define('MPESA_PASSKEY', 'YOUR_PASSKEY');
define('TIGO_PESA_API_KEY', 'YOUR_TIGO_KEY');
define('AIRTEL_MONEY_API_KEY', 'YOUR_AIRTEL_KEY');
define('HALOPESA_API_KEY', 'YOUR_HALO_KEY');

// Include database connection
require_once 'database.php';
require_once 'functions.php';
?>