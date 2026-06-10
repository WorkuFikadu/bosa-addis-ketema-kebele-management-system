<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

require 'config/database.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    include 'modules/reports/index.php';
} catch (Throwable $e) {
    echo "<h1>Error Caught!</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
