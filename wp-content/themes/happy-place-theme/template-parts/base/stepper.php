<?php
/**
 * Base Stepper Component
 * Step-by-step progress indicator for multi-step processes
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stepper component configuration
 */
$defaults = [
    // Steps configuration
    'steps' => [], // Array of step objects
    'current_step' => 1, // Current active step (1-based)
    'completed_steps' => [], // Array of completed step numbers
    
    // Visual variants
    'variant' => 'horizontal', // horizontal, vertical
    'style' => 'default', // default, minimal, dots, numbers, progress
    'size' => 'md', // sm, md, lg
    
    // Step content
    'show_labels' => true, // Show step labels
    'show_descriptions' => false, // Show step descriptions
    'show_numbers' => true, // Show step numbers
    'show_icons' => false, // Show step icons
    'show_connector' => true, // Show connecting lines
    
    // Interactive features
    'clickable' => false, // Allow clicking to navigate steps
    'allow_skip' => false, // Allow skipping to future steps
    'linear' => true, // Must complete steps in order
    
    // Visual features
    'animated' => true, // Animate transitions
    'show_progress' => false, // Show progress percentage
    'compact' => false, // Compact layout
    
    // Behavior
    'on_step_click' => '', // JavaScript callback for step clicks
    'on_step_change' => '', // JavaScript callback for step changes
    
    // CSS classes
    'container_class' => '',
    'step_class' => '',
    'connector_class' => '',
    
    // Data attributes
    'data_attributes' => [],
];

$props = wp_parse_args($props ?? [], $defaults);

// Generate unique ID
$stepper_id = $props['id'] ?? 'hph-stepper-' . wp_unique_id();

// Validate and normalize steps
if (empty($props['steps'])) {
    $props['steps'] = [
        ['label' => 'Step 1', 'id' => 'step-1'],
        ['label' => 'Step 2', 'id' => 'step-2'],
        ['label' => 'Step 3', 'id' => 'step-3'],
    ];
}

// Ensure steps have required properties
$normalized_steps = [];
foreach ($props['steps'] as $index => $step) {
    $step_number = $index + 1;
    $normalized_steps[] = wp_parse_args($step, [
        'id' => 'step-' . $step_number,
        'label' => 'Step ' . $step_number,
        'description' => '',
        'icon' => '',
        'status' => 'pending', // pending, active, completed, error
        'clickable' => $props['clickable'],
        'disabled' => false,
    ]);
}

$props['steps'] = $normalized_steps;
$total_steps = count($props['steps']);

// Calculate step statuses
foreach ($props['steps'] as $index => &$step) {
    $step_number = $index + 1;
    
    if (in_array($step_number, $props['completed_steps'])) {
        $step['status'] = 'completed';
    } elseif ($step_number === $props['current_step']) {
        $step['status'] = 'active';
    } elseif ($step_number < $props['current_step']) {
        $step['status'] = 'completed';
    } else {
        $step['status'] = 'pending';
    }
    
    // Handle clickability
    if ($props['linear'] && !$props['allow_skip']) {
        $step['clickable'] = $step['status'] !== 'pending' || $step_number <= $props['current_step'] + 1;
    }
}

// Calculate progress percentage
$progress_percentage = $props['current_step'] > 0 ? round(($props['current_step'] / $total_steps) * 100) : 0;

// Build CSS classes
$container_classes = [
    'hph-stepper',
    'hph-stepper--' . $props['variant'],
    'hph-stepper--' . $props['style'],
    'hph-stepper--' . $props['size'],
];

if ($props['animated']) {
    $container_classes[] = 'hph-stepper--animated';
}

if ($props['compact']) {
    $container_classes[] = 'hph-stepper--compact';
}

if ($props['clickable']) {
    $container_classes[] = 'hph-stepper--clickable';
}

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

// Data attributes
$data_attrs = [
    'data-stepper-variant' => $props['variant'],
    'data-stepper-style' => $props['style'],
    'data-stepper-current' => $props['current_step'],
    'data-stepper-total' => $total_steps,
    'data-stepper-progress' => $progress_percentage,
];

if ($props['clickable']) {
    $data_attrs['data-clickable'] = 'true';
}

if ($props['linear']) {
    $data_attrs['data-linear'] = 'true';
}

if (!empty($props['data_attributes'])) {
    $data_attrs = array_merge($data_attrs, $props['data_attributes']);
}

// Helper functions
function get_step_icon($step, $step_number, $show_numbers, $show_icons) {
    if ($show_icons && !empty($step['icon'])) {
        return $step['icon'];
    }
    
    switch ($step['status']) {
        case 'completed':
            return 'check';
        case 'error':
            return 'x';
        case 'active':
        case 'pending':
        default:
            return $show_numbers ? $step_number : 'circle';
    }
}

function render_step_content($step, $step_number, $props) {
    ?>
    <div class="hph-stepper__step-content">
        <?php if ($props['show_labels']): ?>
            <div class="hph-stepper__step-label">
                <?php echo esc_html($step['label']); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($props['show_descriptions'] && !empty($step['description'])): ?>
            <div class="hph-stepper__step-description">
                <?php echo wp_kses_post($step['description']); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

function render_step_indicator($step, $step_number, $props) {
    $icon = get_step_icon($step, $step_number, $props['show_numbers'], $props['show_icons']);
    ?>
    <div class="hph-stepper__step-indicator">
        <?php if ($props['show_numbers'] && $step['status'] !== 'completed' && $step['status'] !== 'error'): ?>
            <span class="hph-stepper__step-number"><?php echo esc_html($step_number); ?></span>
        <?php else: ?>
            <span class="hph-stepper__step-icon">
                <?php
                get_template_part('template-parts/base/icon', null, [
                    'name' => $icon,
                    'size' => $props['size'] === 'sm' ? 'xs' : 'sm'
                ]);
                ?>
            </span>
        <?php endif; ?>
    </div>
    <?php
}
?>

<div 
    id="<?php echo esc_attr($stepper_id); ?>"
    class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
    <?php foreach ($data_attrs as $key => $value): ?>
        <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
    <?php endforeach; ?>
>
    <?php if ($props['show_progress']): ?>
        <div class="hph-stepper__progress">
            <div class="hph-stepper__progress-bar">
                <div 
                    class="hph-stepper__progress-fill" 
                    style="width: <?php echo esc_attr($progress_percentage); ?>%"
                ></div>
            </div>
            <div class="hph-stepper__progress-text">
                Step <?php echo esc_html($props['current_step']); ?> of <?php echo esc_html($total_steps); ?>
                (<?php echo esc_html($progress_percentage); ?>%)
            </div>
        </div>
    <?php endif; ?>

    <div class="hph-stepper__steps">
        <?php foreach ($props['steps'] as $index => $step): ?>
            <?php 
            $step_number = $index + 1;
            $is_last = $index === count($props['steps']) - 1;
            
            $step_classes = [
                'hph-stepper__step',
                'hph-stepper__step--' . $step['status'],
            ];
            
            if ($step['clickable'] && !$step['disabled']) {
                $step_classes[] = 'hph-stepper__step--clickable';
            }
            
            if ($step['disabled']) {
                $step_classes[] = 'hph-stepper__step--disabled';
            }
            
            if (!empty($props['step_class'])) {
                $step_classes[] = $props['step_class'];
            }
            
            $step_attrs = [
                'class' => implode(' ', $step_classes),
                'data-step' => $step_number,
                'data-step-id' => $step['id'],
                'data-step-status' => $step['status'],
            ];
            
            if ($step['clickable'] && !$step['disabled']) {
                $step_attrs['role'] = 'button';
                $step_attrs['tabindex'] = '0';
                $step_attrs['aria-label'] = 'Go to ' . $step['label'];
                
                if ($props['on_step_click']) {
                    $step_attrs['data-hph-step-click'] = str_replace('{step}', $step_number, $props['on_step_click']);
                }
            }
            ?>
            
            <div <?php foreach ($step_attrs as $key => $value): ?><?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>" <?php endforeach; ?>>
                <?php render_step_indicator($step, $step_number, $props); ?>
                <?php render_step_content($step, $step_number, $props); ?>
            </div>

            <?php if (!$is_last && $props['show_connector']): ?>
                <div class="hph-stepper__connector <?php echo esc_attr($props['connector_class']); ?>">
                    <div class="hph-stepper__connector-line"></div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<?php
/**
 * Usage Examples:
 * 
 * Property listing process:
 * get_template_part('template-parts/base/stepper', null, [
 *     'current_step' => 2,
 *     'steps' => [
 *         [
 *             'id' => 'property-details',
 *             'label' => 'Property Details',
 *             'description' => 'Basic information about your property'
 *         ],
 *         [
 *             'id' => 'photos-media',
 *             'label' => 'Photos & Media',
 *             'description' => 'Upload property images and videos'
 *         ],
 *         [
 *             'id' => 'pricing',
 *             'label' => 'Pricing',
 *             'description' => 'Set your asking price and terms'
 *         ],
 *         [
 *             'id' => 'review-publish',
 *             'label' => 'Review & Publish',
 *             'description' => 'Review and publish your listing'
 *         ]
 *     ],
 *     'show_descriptions' => true,
 *     'clickable' => true,
 *     'show_progress' => true
 * ]);
 * 
 * Vertical stepper for mobile:
 * get_template_part('template-parts/base/stepper', null, [
 *     'variant' => 'vertical',
 *     'current_step' => 1,
 *     'steps' => [
 *         ['label' => 'Search Properties', 'icon' => 'search'],
 *         ['label' => 'Schedule Viewing', 'icon' => 'calendar'],
 *         ['label' => 'Submit Application', 'icon' => 'file-text'],
 *         ['label' => 'Get Approved', 'icon' => 'check-circle']
 *     ],
 *     'show_icons' => true,
 *     'show_numbers' => false
 * ]);
 * 
 * Minimal progress indicator:
 * get_template_part('template-parts/base/stepper', null, [
 *     'style' => 'dots',
 *     'current_step' => 3,
 *     'steps' => [
 *         ['label' => 'Contact Info'],
 *         ['label' => 'Preferences'],
 *         ['label' => 'Verification'],
 *         ['label' => 'Complete']
 *     ],
 *     'show_labels' => false,
 *     'compact' => true
 * ]);
 * 
 * Mortgage application process:
 * get_template_part('template-parts/base/stepper', null, [
 *     'current_step' => 2,
 *     'completed_steps' => [1],
 *     'steps' => [
 *         [
 *             'label' => 'Pre-qualification',
 *             'description' => 'Quick eligibility check',
 *             'status' => 'completed'
 *         ],
 *         [
 *             'label' => 'Documentation',
 *             'description' => 'Upload required documents',
 *             'status' => 'active'
 *         ],
 *         [
 *             'label' => 'Underwriting',
 *             'description' => 'Application review process'
 *         ],
 *         [
 *             'label' => 'Approval',
 *             'description' => 'Final loan approval'
 *         ]
 *     ],
 *     'linear' => true,
 *     'show_descriptions' => true,
 *     'on_step_click' => 'navigateToStep({step})'
 * ]);
 */
?>
