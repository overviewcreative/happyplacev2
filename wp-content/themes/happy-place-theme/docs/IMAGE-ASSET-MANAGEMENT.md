# Image Asset Management System

## Overview

The Happy Place Theme includes a comprehensive image asset management system that integrates with the template asset management to provide optimized image loading, proper URL encoding, and smart fallbacks.

## Image Helper Functions

### Core Functions

#### `hph_get_image_url($image_path, $check_exists = false)`
Get properly encoded theme image URL with optional existence check.

```php
// Basic usage
$hero_bg = hph_get_image_url('hero-bg.jpg');

// With existence check (returns null if file doesn't exist)
$image_url = hph_get_image_url('property-photos/house-1.jpg', true);

// Handles spaces and special characters automatically
$mariners_image = hph_get_image_url('26590 Mariners Rd. 1.jpg');
```

#### `hph_get_image_url_with_fallback($image_path, $fallback_path = 'placeholder.svg')`
Get image URL with automatic fallback to placeholder if primary image doesn't exist.

```php
// Uses placeholder.svg if hero-bg.jpg doesn't exist
$hero_bg = hph_get_image_url_with_fallback('hero-bg.jpg');

// Custom fallback
$listing_image = hph_get_image_url_with_fallback('listings/123.jpg', 'listing-placeholder.jpg');
```

#### `hph_image($image_path, $args = [])`
Output optimized image tag with all attributes and lazy loading.

```php
// Basic image
hph_image('hero-bg.jpg', [
    'alt' => 'Hero background',
    'class' => 'hero-image'
]);

// Advanced image with responsive support
hph_image('property-hero.jpg', [
    'alt' => 'Property exterior',
    'class' => 'responsive-image',
    'loading' => 'eager', // For above-fold images
    'responsive' => true,
    'sizes' => ['sm' => 480, 'md' => 768, 'lg' => 1200]
]);
```

## Integration with Template Parts

### Automatic Registration

When template parts use images, the asset system automatically tracks and optimizes them:

```php
// In template-parts/sections/hero.php
get_template_part('template-parts/sections/hero', null, [
    'background_image' => hph_get_image_url('hero-bg.jpg'),
    'style' => 'image'
]);
```

The system recognizes image usage and:
- Preloads critical images
- Applies lazy loading to below-fold images
- Generates responsive image sets when needed
- Provides fallbacks for missing images

### Template Asset Mapping

Images are mapped to template parts in the asset configuration:

```php
// In class-hph-assets.php
'sections/hero' => array(
    'css' => array('hph-hero-sections'),
    'js' => array('hero-interactions'),
    'images' => array(
        'critical' => ['hero-bg.jpg', 'hero.jpg'], // Preloaded
        'lazy' => ['hero-overlay.png'], // Lazy loaded
        'fallbacks' => ['placeholder.svg'] // Always available
    ),
),
```

## Image Categories and Optimization

### Critical Images (Above-the-fold)
Images that appear in the initial viewport are preloaded:

```php
// Automatically preloaded for hero sections
$critical_images = [
    'hero-bg.jpg',
    'hero.jpg',
    'front-page-hero.jpg'
];
```

### Lazy-loaded Images (Below-the-fold)
Images that appear later in the page load asynchronously:

```php
// Automatically lazy-loaded
hph_image('property-gallery-1.jpg', [
    'loading' => 'lazy', // Default behavior
    'class' => 'gallery-image'
]);
```

### Responsive Images
For images that need multiple sizes:

```php
// Generates srcset and sizes attributes
$responsive_data = hph_get_responsive_image_data('hero-large.jpg', [
    'sm' => 480,
    'md' => 768,
    'lg' => 1200,
    'xl' => 1920
]);
```

## File Organization

### Directory Structure
```
assets/
└── images/
    ├── hero-bg.jpg              # Hero backgrounds
    ├── hero.jpg
    ├── placeholder.svg          # Fallback images
    ├── listing-placeholder.jpg
    ├── agent-placeholder.jpg
    ├── property-photos/         # Property images
    │   ├── 26590 Mariners Rd. 1.jpg
    │   ├── 26590 Mariners Rd. 2.jpg
    │   └── ...
    ├── icons/                   # UI icons
    │   └── map-marker-property.svg
    └── placeholders/            # Various placeholders
        ├── city-placeholder.jpg
        ├── community-placeholder.jpg
        └── team-placeholder.jpg
```

### Naming Conventions

1. **Hero Images**: `hero-{variant}.jpg`
2. **Placeholders**: `{type}-placeholder.{ext}`
3. **Property Photos**: Use descriptive names, spaces OK
4. **Icons**: `{purpose}-{variant}.svg`

## URL Encoding and Special Characters

The system automatically handles:

- **Spaces**: "26590 Mariners Rd. 1.jpg" → "26590%20Mariners%20Rd.%201.jpg"
- **Special Characters**: Properly encoded for web URLs
- **Unicode**: Full Unicode filename support

```php
// All of these work automatically
$images = [
    'Property & Home.jpg',
    'Château Image.jpg',
    '26590 Mariners Rd. 1.jpg',
    'файл.jpg' // Cyrillic
];

foreach ($images as $image) {
    $url = hph_get_image_url($image); // Properly encoded
}
```

## Performance Optimizations

### Preloading Strategy

Critical images are preloaded in the `<head>`:

```php
// Automatic preloading for template parts
add_action('wp_head', function() {
    $critical_images = hph_get_critical_images_for_page();
    hph_preload_images($critical_images);
});
```

### Lazy Loading

Non-critical images use native lazy loading:

```html
<!-- Generated automatically -->
<img src="image.jpg" loading="lazy" alt="Description">
```

### Image Existence Checking

Prevent 404s with existence checking:

```php
// Only outputs image if file exists
$image_url = hph_get_image_url('might-not-exist.jpg', true);
if ($image_url) {
    echo '<img src="' . esc_url($image_url) . '" alt="Image">';
}
```

## Best Practices

### Template Part Usage

1. **Always use helper functions**:
   ```php
   // Good
   'background_image' => hph_get_image_url('hero-bg.jpg'),
   
   // Avoid
   'background_image' => get_template_directory_uri() . '/assets/images/hero-bg.jpg',
   ```

2. **Provide meaningful alt text**:
   ```php
   hph_image('property-exterior.jpg', [
       'alt' => 'Modern 3-bedroom home exterior with landscaped front yard'
   ]);
   ```

3. **Use appropriate loading strategies**:
   ```php
   // Hero images (above fold)
   hph_image('hero-bg.jpg', ['loading' => 'eager']);
   
   // Gallery images (below fold)
   hph_image('gallery-1.jpg', ['loading' => 'lazy']);
   ```

### File Management

1. **Optimize images before upload**
2. **Use appropriate formats** (JPEG for photos, PNG for graphics, SVG for icons)
3. **Include fallback images** for dynamic content
4. **Test with missing images** to ensure graceful degradation

### Development Workflow

1. **Add images to appropriate subdirectories**
2. **Update asset mappings** if adding new template parts
3. **Test image loading** across different page types
4. **Monitor performance** in browser dev tools

## Integration Examples

### Hero Section with Smart Image Selection

```php
// Automatically selects best available image
$hero_image = hph_smart_image_select([
    [
        'condition' => is_front_page(),
        'image' => 'front-page-hero.jpg'
    ],
    [
        'condition' => is_page('about'),
        'image' => 'about-hero.jpg'
    ],
    [
        'condition' => true, // fallback
        'image' => 'hero-bg.jpg'
    ]
]);

get_template_part('template-parts/sections/hero', null, [
    'background_image' => $hero_image,
    'style' => 'image'
]);
```

### Property Gallery with Lazy Loading

```php
// Property images with automatic optimization
$property_images = [
    '26590 Mariners Rd. 1.jpg',
    '26590 Mariners Rd. 2.jpg',
    '26590 Mariners Rd. 3.jpg'
];

foreach ($property_images as $index => $image) {
    hph_image($image, [
        'alt' => "Property view " . ($index + 1),
        'class' => 'property-gallery-image',
        'loading' => $index === 0 ? 'eager' : 'lazy' // First image eager
    ]);
}
```

The image asset management system works seamlessly with the template asset management to provide optimized, reliable image loading throughout the Happy Place Theme.
