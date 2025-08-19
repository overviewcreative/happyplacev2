<?php
/**
 * Validate Personal Access Token Format
 */

$token = "patM4ULeXoGRmKns6.d9be29a96ded648326504aef076c15d0c29d9ed05f81155f20a2fb90a7587dc2";

echo "<h1>Personal Access Token Validation</h1>";

echo "<h2>Token Analysis</h2>";
echo "<ul>";
echo "<li><strong>Full Token:</strong> " . $token . "</li>";
echo "<li><strong>Length:</strong> " . strlen($token) . " characters</li>";
echo "<li><strong>Starts with 'pat':</strong> " . (strpos($token, 'pat') === 0 ? '✅ YES' : '❌ NO') . "</li>";
echo "</ul>";

// Test the regex pattern used in the code
$pattern = '/^(pat|key)[a-zA-Z0-9]{14,}/';
$matches_pattern = preg_match($pattern, $token);

echo "<h2>Pattern Validation</h2>";
echo "<ul>";
echo "<li><strong>Regex Pattern:</strong> <code>" . $pattern . "</code></li>";
echo "<li><strong>Matches Pattern:</strong> " . ($matches_pattern ? '✅ YES' : '❌ NO') . "</li>";
echo "</ul>";

if (!$matches_pattern) {
    echo "<h2>⚠️ Problem Found</h2>";
    echo "<p>The token doesn't match the expected pattern. This might be why the sync isn't working.</p>";
    
    // Check if it contains invalid characters
    if (preg_match('/[^a-zA-Z0-9.]/', $token)) {
        echo "<p>❌ Token contains invalid characters (only letters, numbers, and dots are typically allowed)</p>";
    }
    
    // Check the format more carefully
    if (strpos($token, '.') !== false) {
        $parts = explode('.', $token);
        echo "<h3>Token Parts Analysis</h3>";
        echo "<ul>";
        foreach ($parts as $i => $part) {
            echo "<li><strong>Part " . ($i + 1) . ":</strong> " . $part . " (" . strlen($part) . " chars)</li>";
        }
        echo "</ul>";
    }
}

echo "<h2>📝 Expected Format</h2>";
echo "<p>Airtable Personal Access Tokens should:</p>";
echo "<ul>";
echo "<li>Start with 'pat'</li>";
echo "<li>Be followed by at least 14 alphanumeric characters</li>";
echo "<li>May contain dots as separators</li>";
echo "</ul>";

echo "<h2>🔧 Suggested Fix</h2>";
echo "<p>If the token format is different than expected, we may need to update the validation regex.</p>";
?>
