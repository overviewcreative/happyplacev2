<?php
/**
 * Base Avatar Component
 * User avatar display with comprehensive configuration options
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Avatar component configuration
 */
$defaults = [
    // Content
    'user_id' => 0,
    'name' => '',
    'email' => '',
    'image_url' => '',
    'fallback_icon' => 'user',
    
    // Behavior
    'clickable' => false,
    'href' => '',
    'target' => '_self',
    
    // Style variants
    'variant' => 'default', // default, outlined, shadow
    'shape' => 'circle', // circle, square, rounded
    'size' => 'md', // xs, sm, md, lg, xl, 2xl
    
    // Badge/status
    'show_status' => false,
    'status' => 'offline', // online, offline, away, busy
    'badge_content' => '',
    'badge_position' => 'bottom-right', // top-left, top-right, bottom-left, bottom-right
    
    // Initials
    'use_initials' => true,
    'initials_count' => 2,
    'force_initials' => false,
    
    // Group avatar
    'is_group' => false,
    'group_members' => [], // Array of member data
    'max_visible' => 3,
    'show_count' => true,
    
    // Accessibility
    'alt_text' => '',
    'title' => '',
    'role' => '',
    
    // CSS classes
    'container_class' => '',
    'image_class' => '',
    'initials_class' => '',
    
    // Data attributes
    'data_attributes' => [],
    
    // Advanced features
    'lazy_load' => true,
    'high_res' => false,
    'gravatar_default' => 'mp', // mp, identicon, monsterid, wavatar, retro, blank
];

$props = wp_parse_args(hph_get_arg() ?? [], $defaults);

// Generate unique ID
$avatar_id = $props['id'] ?? 'hph-avatar-' . wp_unique_id();

// Get user data if user_id provided
if ($props['user_id'] && !$props['name'] && !$props['email']) {
    $user = get_userdata($props['user_id']);
    if ($user) {
        $props['name'] = $user->display_name;
        $props['email'] = $user->user_email;
    }
}

// Determine image source
$image_url = '';
if ($props['image_url']) {
    $image_url = $props['image_url'];
} elseif ($props['email']) {
    // Use Gravatar
    $gravatar_url = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($props['email'])));
    $gravatar_params = [
        'd' => $props['gravatar_default'],
        's' => 200 // Base size, will be scaled via CSS
    ];
    $image_url = add_query_arg($gravatar_params, $gravatar_url);
}

// Generate initials
$initials = '';
if ($props['use_initials'] && $props['name']) {
    $name_parts = explode(' ', trim($props['name']));
    $initials_array = [];
    
    foreach ($name_parts as $part) {
        if (count($initials_array) >= $props['initials_count']) break;
        if (!empty($part)) {
            $initials_array[] = strtoupper(substr($part, 0, 1));
        }
    }
    
    $initials = implode('', $initials_array);
}

// Determine what to show
$show_image = $image_url && !$props['force_initials'];
$show_initials = !$show_image && $initials;
$show_icon = !$show_image && !$show_initials;

// Build CSS classes
$container_classes = [
    'hph-avatar',
    'hph-avatar--' . $props['variant'],
    'hph-avatar--' . $props['shape'],
    'hph-avatar--' . $props['size'],
];

if ($props['clickable']) {
    $container_classes[] = 'hph-avatar--clickable';
}

if ($props['show_status']) {
    $container_classes[] = 'hph-avatar--with-status';
}

if ($props['badge_content'] || $props['show_status']) {
    $container_classes[] = 'hph-avatar--with-badge';
}

if ($props['is_group']) {
    $container_classes[] = 'hph-avatar--group';
}

if (!empty($props['container_class'])) {
    $container_classes[] = $props['container_class'];
}

// Data attributes
$data_attrs = [];
if (!empty($props['data_attributes'])) {
    $data_attrs = $props['data_attributes'];
}

// Alt text
$alt_text = $props['alt_text'] ?: ($props['name'] ?: 'User avatar');
$title_text = $props['title'] ?: $props['name'];

// Generate initials background color based on name
function generate_avatar_color($name) {
    $colors = [
        '#F87171', '#FB923C', '#FBBF24', '#A3E635', '#34D399',
        '#22D3EE', '#60A5FA', '#A78BFA', '#F472B6', '#FB7185'
    ];
    
    if (empty($name)) return $colors[0];
    
    $hash = 0;
    for ($i = 0; $i < strlen($name); $i++) {
        $hash = ord($name[$i]) + (($hash << 5) - $hash);
    }
    
    return $colors[abs($hash) % count($colors)];
}

$initials_bg_color = generate_avatar_color($props['name']);

// Size mapping for icons
$icon_sizes = [
    'xs' => '12',
    'sm' => '14', 
    'md' => '18',
    'lg' => '24',
    'xl' => '32',
    '2xl' => '40'
];
$icon_size = $icon_sizes[$props['size']] ?? '18';
?>

<?php if ($props['is_group'] && !empty($props['group_members'])): ?>
    <!-- Group Avatar -->
    <div 
        id="<?php echo esc_attr($avatar_id); ?>"
        class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
        <?php foreach ($data_attrs as $key => $value): ?>
            <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
        <?php endforeach; ?>
        <?php if ($title_text): ?>title="<?php echo esc_attr($title_text); ?>"<?php endif; ?>
        <?php if ($props['role']): ?>role="<?php echo esc_attr($props['role']); ?>"<?php endif; ?>
    >
        <div class="hph-avatar__group-container">
            <?php 
            $visible_members = array_slice($props['group_members'], 0, $props['max_visible']);
            $remaining_count = count($props['group_members']) - count($visible_members);
            
            foreach ($visible_members as $index => $member):
                $member_props = wp_parse_args($member, [
                    'name' => '',
                    'image_url' => '',
                    'email' => ''
                ]);
                
                // Recursive call for individual member
                hph_component('base/avatar', array_merge($member_props, [
                    'size' => $props['size'],
                    'variant' => $props['variant'],
                    'shape' => $props['shape'],
                    'container_class' => 'hph-avatar__group-member',
                    'is_group' => false,
                    'style' => '--avatar-offset: ' . ($index * 0.75) . 'rem;'
                ]));
            endforeach;
            ?>
            
            <?php if ($remaining_count > 0 && $props['show_count']): ?>
                <div class="hph-avatar__group-count hph-avatar--<?php echo esc_attr($props['size']); ?> hph-avatar--<?php echo esc_attr($props['shape']); ?>">
                    <span class="hph-avatar__count-text">+<?php echo esc_html($remaining_count); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- Single Avatar -->
    <?php
    $tag = $props['clickable'] ? 'a' : 'div';
    $tag_attrs = [];
    
    if ($props['clickable'] && $props['href']) {
        $tag_attrs['href'] = $props['href'];
        $tag_attrs['target'] = $props['target'];
    }
    ?>
    
    <<?php echo $tag; ?>
        id="<?php echo esc_attr($avatar_id); ?>"
        class="<?php echo esc_attr(implode(' ', $container_classes)); ?>"
        <?php foreach ($tag_attrs as $key => $value): ?>
            <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
        <?php endforeach; ?>
        <?php foreach ($data_attrs as $key => $value): ?>
            <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
        <?php endforeach; ?>
        <?php if ($title_text): ?>title="<?php echo esc_attr($title_text); ?>"<?php endif; ?>
        <?php if ($props['role']): ?>role="<?php echo esc_attr($props['role']); ?>"<?php endif; ?>
    >
        <div class="hph-avatar__container">
            <?php if ($show_image): ?>
                <img 
                    class="hph-avatar__image <?php echo esc_attr($props['image_class']); ?>"
                    src="<?php echo esc_url($image_url); ?>"
                    alt="<?php echo esc_attr($alt_text); ?>"
                    <?php if ($props['lazy_load']): ?>loading="lazy"<?php endif; ?>
                    <?php if ($props['high_res']): ?>
                        srcset="<?php echo esc_url($image_url); ?> 1x, <?php echo esc_url(add_query_arg('s', 400, $image_url)); ?> 2x"
                    <?php endif; ?>
                >
            
            <?php elseif ($show_initials): ?>
                <div 
                    class="hph-avatar__initials <?php echo esc_attr($props['initials_class']); ?>"
                    style="background-color: <?php echo esc_attr($initials_bg_color); ?>;"
                    aria-label="<?php echo esc_attr($alt_text); ?>"
                >
                    <span class="hph-avatar__initials-text">
                        <?php echo esc_html($initials); ?>
                    </span>
                </div>
            
            <?php elseif ($show_icon): ?>
                <div 
                    class="hph-avatar__icon"
                    aria-label="<?php echo esc_attr($alt_text); ?>"
                >
                    <?php
                    hph_component('base/icon', [
                        'name' => $props['fallback_icon'],
                        'size' => $icon_size
                    ]);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if ($props['show_status'] || $props['badge_content']): ?>
                <div class="hph-avatar__badge hph-avatar__badge--<?php echo esc_attr($props['badge_position']); ?>">
                    <?php if ($props['show_status']): ?>
                        <span 
                            class="hph-avatar__status hph-avatar__status--<?php echo esc_attr($props['status']); ?>"
                            aria-label="<?php echo esc_attr(ucfirst($props['status'])); ?>"
                        ></span>
                    <?php endif; ?>
                    
                    <?php if ($props['badge_content']): ?>
                        <span class="hph-avatar__badge-content">
                            <?php echo wp_kses_post($props['badge_content']); ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </<?php echo $tag; ?>>
<?php endif; ?>
