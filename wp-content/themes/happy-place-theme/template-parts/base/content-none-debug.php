<?php
/**
 * Debug Content None Component
 */

echo "<!-- DEBUG: content-none-debug loaded -->\n";
echo "<div style='background: yellow; padding: 10px; border: 2px solid orange;'>";
echo "<h3>DEBUG: Content None Component</h3>";

// Security check
if (!defined('ABSPATH')) {
    echo "<p>ABSPATH not defined - exiting</p>";
    exit;
}

echo "<p>✅ ABSPATH check passed</p>";

// Get component args - works with both systems
$component_args = $GLOBALS['hph_component_args'] ?? get_query_var('args', []);
echo "<p><strong>Global args:</strong> " . print_r($GLOBALS['hph_component_args'] ?? 'NONE', true) . "</p>";
echo "<p><strong>Query var args:</strong> " . print_r(get_query_var('args', 'NONE'), true) . "</p>";

if (empty($component_args)) {
    // Fallback to template system args
    $template_args = get_query_var('template_args');
    if (is_array($template_args)) {
        $component_args = $template_args;
    }
}
$args = $component_args;

$args = wp_parse_args($args, [
    'context' => 'general',
    'title' => null,
    'message' => null,
    'show_search' => false,
    'show_links' => true
]);

echo "<p><strong>Final parsed args:</strong> " . print_r($args, true) . "</p>";

// Context-specific messages
$context_messages = [
    'test' => [
        'title' => 'Test Component',
        'message' => 'This is a test of the component system.'
    ],
    'general' => [
        'title' => 'Content Not Available',
        'message' => 'The requested content is not available at this time.'
    ]
];

$context_content = $context_messages[$args['context']] ?? $context_messages['general'];
$title = $args['title'] ?? $context_content['title'];
$message = $args['message'] ?? $context_content['message'];

echo "<p><strong>Context:</strong> " . $args['context'] . "</p>";
echo "<p><strong>Title:</strong> " . $title . "</p>";
echo "<p><strong>Message:</strong> " . $message . "</p>";

?>

<div class="hph-content-none" data-component="content-none" data-context="<?php echo esc_attr($args['context']); ?>">
    <div class="content-none-container">
        <div class="content-none-icon">
            <div class="icon-wrapper">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        
        <div class="content-none-content">
            <h2 class="content-none-title"><?php echo esc_html($title); ?></h2>
            <p class="content-none-message"><?php echo esc_html($message); ?></p>
        </div>
    </div>
</div>

<style>
.hph-content-none {
    min-height: 20vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: #f9f9f9;
    border: 2px solid #ccc;
}
.content-none-container {
    max-width: 400px;
    text-align: center;
}
.icon-wrapper {
    display: inline-flex;
    width: 3rem;
    height: 3rem;
    background: #e5e5e5;
    border-radius: 50%;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}
.icon {
    width: 1.5rem;
    height: 1.5rem;
    color: #666;
}
.content-none-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.content-none-message {
    color: #666;
}
</style>

<?php
echo "</div>";
echo "<!-- DEBUG: content-none-debug complete -->\n";
?>