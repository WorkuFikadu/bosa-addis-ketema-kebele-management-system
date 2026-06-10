<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Checking Environment</h1>";

echo "PHP Version: " . phpversion() . "<br>";

echo "<h2>Checking Database</h2>";
try {
    require_once '../config/database.php';
    echo "Database connected successfully.<br>";
    
    $tables = ['individuals', 'milisha_records', 'milisha_id_cards', 'transactions', 'service_prices'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        echo "Table '$table' count: " . $stmt->fetchColumn() . "<br>";
    }
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Checking Justice Files</h2>";
$files = ['modules/justice/index.php', 'modules/justice/milisha_list.php', 'modules/justice/milisha_create.php'];
foreach ($files as $file) {
    $full_path = '../' . $file;
    if (file_exists($full_path)) {
        echo "File '$file' exists.<br>";
        // Check syntax again just in case
        $output = [];
        $return_var = 0;
        exec("c:\\xampp\\php\\php.exe -l \"$full_path\"", $output, $return_var);
        echo "Lint '$file': " . implode("<br>", $output) . "<br>";
    } else {
        echo "File '$file' DOES NOT EXIST.<br>";
    }
}

echo "<h2>Environment Variables</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";
?>
