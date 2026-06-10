<?php
$file = __DIR__ . '/../modules/reports/index.php';
$content = file_get_contents($file);

// Find the exact position after the housing checkbox closing div and before border-top
$needle = 'chk-housing';
$pos = strpos($content, $needle);

if ($pos === false) {
    die("ERROR: chk-housing not found");
}

// Find the </div> after chk-housing
$divClose = strpos($content, '</div>', $pos);

// What comes next after that </div>?
echo "Context around housing checkbox:<br>";
echo "<pre>" . htmlspecialchars(substr($content, $pos - 50, 400)) . "</pre>";

// Check if services checkbox already exists
if (strpos($content, 'chk-services') !== false) {
    echo "<br><b style='color:green'>✓ chk-services already exists! Nothing to do.</b>";
    exit;
}

// Inject the new checkbox between the housing </div> and the border-top div
$housingBlock = substr($content, $pos - 100, 400);
echo "<hr>Housing block found. Performing replacement...<br>";

// Find the closing </div> of the housing form-check div, then insert the new checkbox before border-top
$insertAfter  = "                </div>\n                <div class=\"border-top pt-2";
$insertBefore = "                </div>\n                <div class=\"form-check mb-3\">\n                    <input class=\"form-check-input section-toggle\" type=\"checkbox\" value=\"services\" id=\"chk-services\" checked>\n                    <label class=\"form-check-label small fw-bold\" for=\"chk-services\">6. Kebele Services</label>\n                </div>\n                <div class=\"border-top pt-2";

$count = 0;
$new = str_replace($insertAfter, $insertBefore, $content, $count);

if ($count === 0) {
    // Try with Windows line endings
    $insertAfter2  = "                </div>\r\n                <div class=\"border-top pt-2";
    $insertBefore2 = "                </div>\r\n                <div class=\"form-check mb-3\">\r\n                    <input class=\"form-check-input section-toggle\" type=\"checkbox\" value=\"services\" id=\"chk-services\" checked>\r\n                    <label class=\"form-check-label small fw-bold\" for=\"chk-services\">6. Kebele Services</label>\r\n                </div>\r\n                <div class=\"border-top pt-2";
    $new = str_replace($insertAfter2, $insertBefore2, $content, $count);
}

if ($count > 0) {
    file_put_contents($file, $new);
    echo "<b style='color:green'>✓ SUCCESS! Checkbox inserted ($count replacement(s) made)</b>";
} else {
    echo "<b style='color:red'>✗ FAILED: Pattern not matched with either line endings</b>";
}
