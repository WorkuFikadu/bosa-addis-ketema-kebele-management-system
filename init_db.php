<?php
$host = 'localhost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents('database.sql');
    
    // Execute multiple queries
    $pdo->exec($sql);
    
    echo "<h2 style='color:green;'>Database and tables successfully initialized!</h2>";
    echo "<p>The 'residents' table and others have been created.</p>";
    echo "<a href='seed.php'>Step 2: Seed with Example Data</a>";
} catch (PDOException $e) {
    die("<h2 style='color:red;'>Initialization failed:</h2> " . $e->getMessage());
}
?>
