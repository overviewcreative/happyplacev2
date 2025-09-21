<?php
/**
 * Fello Search Widget Form Template
 *
 * Form template for embedding the Fello home search widget
 *
 * @package HappyPlaceTheme
 * @since 3.2.0
 */

// Extract arguments
$args = wp_parse_args($args, [
    'title' => __('Search Homes', 'happy-place-theme'),
    'description' => __('Find your perfect home with our advanced search tools.', 'happy-place-theme'),
    'widget_id' => '652e76f5b287290025266a1c',
    'modal_context' => false,
    'variant' => 'default'
]);
?>

<div class="hph-form hph-form--fello-widget <?php echo $args['modal_context'] ? 'hph-form--modal' : ''; ?>">

    <?php if (!$args['modal_context']): ?>
    <!-- Form Header (hidden in modal context) -->
    <div class="hph-form-header">
        <h2 class="hph-form-title"><?php echo esc_html($args['title']); ?></h2>
        <?php if ($args['description']): ?>
        <p class="hph-form-description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Fello Widget Container -->
    <div class="hph-fello-widget-container">

        <!-- Loading State -->
        <div class="hph-fello-loading" id="fello-loading">
            <div class="hph-fello-spinner"></div>
            <p><?php esc_html_e('Loading search tools...', 'happy-place-theme'); ?></p>
        </div>

        <!-- Fello Search Widget -->
        <div class="hph-fello-widget-wrapper" id="fello-widget-wrapper" style="display: none;">
            <!-- The Fello widget will be inserted here by the script -->
            <fello-search-widget widget-id="<?php echo esc_attr($args['widget_id']); ?>"></fello-search-widget>
        </div>

        <!-- Error State -->
        <div class="hph-fello-error" id="fello-error" style="display: none;">
            <div class="hph-fello-error-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
                    <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
                </svg>
            </div>
            <h3><?php esc_html_e('Search Temporarily Unavailable', 'happy-place-theme'); ?></h3>
            <p><?php esc_html_e('Please try again later or contact us directly for assistance.', 'happy-place-theme'); ?></p>
            <button type="button" class="hph-btn hph-btn--primary hph-btn--sm" onclick="openHphFormModal('general-contact')">
                <?php esc_html_e('Contact Us', 'happy-place-theme'); ?>
            </button>
        </div>

    </div>
</div>

<style>
/* Fello Widget Styling */
.hph-fello-widget-container {
    position: relative;
    min-height: 300px;
    background: var(--hph-white);
    border-radius: var(--hph-radius-lg);
    overflow: hidden;
}

/* Loading State */
.hph-fello-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 2rem;
    text-align: center;
}

.hph-fello-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid var(--hph-gray-200);
    border-top: 3px solid var(--hph-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.hph-fello-loading p {
    color: var(--hph-gray-600);
    margin: 0;
    font-size: 0.95rem;
}

/* Widget Wrapper */
.hph-fello-widget-wrapper {
    width: 100%;
    min-height: 300px;
}

/* Ensure Fello widget is responsive */
.hph-fello-widget-wrapper fello-search-widget {
    display: block;
    width: 100%;
    min-height: 300px;
}

/* Error State */
.hph-fello-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 2rem;
    text-align: center;
}

.hph-fello-error-icon {
    color: var(--hph-error, #ef4444);
    margin-bottom: 1rem;
}

.hph-fello-error h3 {
    color: var(--hph-gray-900);
    margin: 0 0 0.5rem 0;
    font-size: 1.125rem;
    font-weight: 600;
}

.hph-fello-error p {
    color: var(--hph-gray-600);
    margin: 0 0 1.5rem 0;
    line-height: 1.5;
}

/* Modal Context Adjustments */
.hph-form--modal.hph-form--fello-widget {
    background: transparent;
    border: none;
    box-shadow: none;
    padding: 0;
}

.hph-form--modal .hph-fello-widget-container {
    border-radius: 0;
    box-shadow: none;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .hph-fello-loading,
    .hph-fello-error {
        padding: 2rem 1rem;
    }

    .hph-fello-spinner {
        width: 32px;
        height: 32px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadingElement = document.getElementById('fello-loading');
    const widgetWrapper = document.getElementById('fello-widget-wrapper');
    const errorElement = document.getElementById('fello-error');

    // Function to show error state
    function showError() {
        if (loadingElement) loadingElement.style.display = 'none';
        if (widgetWrapper) widgetWrapper.style.display = 'none';
        if (errorElement) errorElement.style.display = 'flex';
    }

    // Function to show widget
    function showWidget() {
        if (loadingElement) loadingElement.style.display = 'none';
        if (errorElement) errorElement.style.display = 'none';
        if (widgetWrapper) widgetWrapper.style.display = 'block';
    }

    // Load Fello widget script if not already loaded
    if (!document.querySelector('script[src*="widget.hifello.com"]')) {
        const script = document.createElement('script');
        script.src = 'https://widget.hifello.com/search-widget.js';
        script.async = true;
        script.defer = true;

        script.onload = function() {
            // Script loaded successfully
            console.log('Fello widget script loaded');

            // Wait a bit for the widget to initialize
            setTimeout(() => {
                const widget = document.querySelector('fello-search-widget');
                if (widget) {
                    showWidget();
                } else {
                    console.warn('Fello widget element not found');
                    showError();
                }
            }, 1000);
        };

        script.onerror = function() {
            console.error('Failed to load Fello widget script');
            showError();
        };

        document.head.appendChild(script);
    } else {
        // Script already exists, just show the widget
        setTimeout(() => {
            const widget = document.querySelector('fello-search-widget');
            if (widget) {
                showWidget();
            } else {
                showError();
            }
        }, 500);
    }

    // Fallback timeout - show error if widget doesn't load within 10 seconds
    setTimeout(() => {
        if (loadingElement && loadingElement.style.display !== 'none') {
            console.warn('Fello widget loading timeout');
            showError();
        }
    }, 10000);
});
</script>