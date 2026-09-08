<?php
// Minimal diagnostic to verify vercel-php runtime is working
echo "<h2>PHP Runtime Test</h2>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "</p>";
echo "<p>Working Dir: " . getcwd() . "</p>";
echo "<p>__DIR__: " . __DIR__ . "</p>";
echo "<p>Project root (dirname(__DIR__)): " . dirname(__DIR__) . "</p>";

// Check if project files are accessible
$root = dirname(__DIR__);
$files_to_check = ['index.php', 'about.php', 'config/database.php', 'includes/lang.php'];

echo "<h3>File Access Check:</h3><ul>";
foreach ($files_to_check as $f) {
    $exists = is_file($root . '/' . $f);
    echo "<li><code>$f</code>: " . ($exists ? "✅ EXISTS" : "❌ NOT FOUND") . "</li>";
}
echo "</ul>";

echo "<p style='color:green;font-weight:bold'>✅ PHP runtime is working correctly!</p>";
