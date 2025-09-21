<?php
/**
 * Content None Component
 * For when no content is found or access is denied
 * Works with both hph_component() and get_template_part() systems
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get component args - works with both systems
$component_args = $GLOBALS['hph_component_args'] ?? get_query_var('args', []);
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

// Context-specific messages
$context_messages = [
    'listing' => [
        'title' => __('Property Not Found', 'happy-place-theme'),
        'message' => __('Sorry, this property listing is not available or may have been removed.', 'happy-place-theme')
    ],
    'agent' => [
        'title' => __('Agent Not Found', 'happy-place-theme'),
        'message' => __('Sorry, this agent profile is not available.', 'happy-place-theme')
    ],
    'search' => [
        'title' => __('No Results Found', 'happy-place-theme'),
        'message' => __('Sorry, no properties match your search criteria. Try adjusting your filters.', 'happy-place-theme')
    ],
    'access' => [
        'title' => __('Access Restricted', 'happy-place-theme'),
        'message' => __('You do not have permission to view this content.', 'happy-place-theme')
    ],
    'general' => [
        'title' => __('Content Not Available', 'happy-place-theme'),
        'message' => __('The requested content is not available at this time.', 'happy-place-theme')
    ]
];

// Get context-specific content or use defaults
$context_content = $context_messages[$args['context']] ?? $context_messages['general'];
$title = $args['title'] ?? $context_content['title'];
$message = $args['message'] ?? $context_content['message'];
?>

<div class="hph-content-none" data-component="content-none" data-context="<?php echo esc_attr($args['context']); ?>">
    <div class="content-none-container">
        
        <!-- Icon Section -->
        <div class="content-none-icon">
            <div class="icon-wrapper">
                <?php if ($args['context'] === 'search'): ?>
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <?php elseif ($args['context'] === 'listing'): ?>
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <?php elseif ($args['context'] === 'agent'): ?>
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <?php elseif ($args['context'] === 'access'): ?>
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <?php else: ?>
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="content-none-content">
            <h2 class="content-none-title">
                <?php echo esc_html($title); ?>
            </h2>
            
            <p class="content-none-message">
                <?php echo esc_html($message); ?>
            </p>
        </div>
        
        <!-- Search Form -->
        <?php if ($args['show_search'] || $args['context'] === 'search'): ?>
        <div class="content-none-search">
            <form method="get" action="<?php echo esc_url(home_url('/')); ?>" class="search-form">
                <div class="search-input-group">
                    <input type="search" 
                           name="s" 
                           placeholder="<?php esc_attr_e('Search properties...', 'happy-place-theme'); ?>"
                           value="<?php echo esc_attr(get_search_query()); ?>"
                           class="search-input">
                    <button type="submit" class="search-button">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Action Links -->
        <?php if ($args['show_links']): ?>
        <div class="content-none-actions">
            
            <a href="<?php echo esc_url(home_url('/')); ?>" class="action-btn action-btn--primary">
                <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <?php _e('Go Home', 'happy-place-theme'); ?>
            </a>
            
            <?php if (get_post_type_archive_link('listing')): ?>
            <a href="<?php echo esc_url(get_post_type_archive_link('listing')); ?>" class="action-btn action-btn--secondary">
                <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <?php _e('Browse Properties', 'happy-place-theme'); ?>
            </a>
            <?php endif; ?>
            
            <?php if (get_post_type_archive_link('agent')): ?>
            <a href="<?php echo esc_url(get_post_type_archive_link('agent')); ?>" class="action-btn action-btn--secondary">
                <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <?php _e('Find an Agent', 'happy-place-theme'); ?>
            </a>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
    </div>
</div>

<style>
.hph-content-none {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
}

.content-none-container {
    max-width: 600px;
    text-align: center;
}

.content-none-icon {
    margin-bottom: 2rem;
}

.icon-wrapper {
    display: inline-flex;
    width: 6rem;
    height: 6rem;
    background: #f3f4f6;
    border-radius: 50%;
    align-items: center;
    justify-content: center;
}

.icon {
    width: 3rem;
    height: 3rem;
    color: #9ca3af;
}

.content-none-title {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.content-none-message {
    font-size: 1.125rem;
    color: #6b7280;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.content-none-search {
    margin-bottom: 2rem;
}

.search-form {
    max-width: 400px;
    margin: 0 auto;
}

.search-input-group {
    position: relative;
    display: flex;
}

.search-input {
    flex: 1;
    padding: 0.875rem 3rem 0.875rem 1rem;
    border: 2px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.15s ease;
}

.search-input:focus {
    outline: none;
    border-color: var(--hph-primary);
    box-shadow: 0 0 0 3px rgba(var(--hph-primary-rgb), 0.1);
}

.search-button {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    width: 2rem;
    height: 2rem;
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    transition: color 0.15s ease;
}

.search-button:hover {
    color: #374151;
}

.search-button svg {
    width: 1.25rem;
    height: 1.25rem;
}

.content-none-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s ease;
    min-width: 160px;
    justify-content: center;
}

.action-btn--primary {
    background: var(--hph-primary);
    color: white;
}

.action-btn--primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(var(--hph-primary-rgb), 0.4);
}

.action-btn--secondary {
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;
}

.action-btn--secondary:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.btn-icon {
    width: 1.25rem;
    height: 1.25rem;
}

@media (max-width: 768px) {
    .hph-content-none {
        padding: 2rem 1rem;
    }
    
    .content-none-title {
        font-size: 1.75rem;
    }
    
    .content-none-message {
        font-size: 1rem;
    }
    
    .content-none-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .action-btn {
        width: 100%;
        max-width: 280px;
    }
}
</style>
