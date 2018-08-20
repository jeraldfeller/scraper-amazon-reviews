<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
session_start();

define('DB_USER', 'scaleway_ftp');
define('DB_PWD', 'bb163Kc7iler');
define('DB_NAME', 'amazon_review_db');
define('DB_HOST', 'localhost');
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME .'');


?>
