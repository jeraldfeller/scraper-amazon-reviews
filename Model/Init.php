<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
session_start();

define('DB_USER', 'root');
define('DB_PWD', '');
define('DB_NAME', 'amazon_review_db');
define('DB_HOST', 'localhost');
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME .'');

define('ROOT_DIR', '/var/www/html/amzrs/scraper-amazon-reviews/');
?>
