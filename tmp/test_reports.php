<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'admin';
include 'modules/reports/index.php';
