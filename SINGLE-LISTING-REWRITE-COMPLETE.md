# Single Listing Template - Complete Rewrite Documentation

## Overview
The `single-listing.php` template has been completely rewritten to provide:
- **Comprehensive null handling** for all ACF fields
- **Modular template parts** for maintainability
- **Robust error handling** for missing data
- **Responsive design** with HPH framework integration
- **Performance optimized** asset loading

## Template Structure

### Main Template (`single-listing.php`)
```php
main#primary.hph-single-listing
├── Hero Section (template-parts/listing/hero.php)
├── Main Content Container
│   ├── Left Column (listing content)
│   │   ├── Property Description & Details (main-body.php)
│   │   ├── Photo Gallery & Tour (gallery-tour-section.php) *conditional
│   │   ├── Map Section (map-section.php) *conditional
│   │   ├── Neighborhood Info (neighborhood-section.php) *conditional
│   │   ├── School Information (schools-section.php) *conditional
│   │   └── Similar Listings (similar-listings.php)
│   └── Right Column (sidebar)
│       └── Agent Information (sidebar-agent.php)
```

## Helper Functions

### Core Data Function
`hph_get_safe_listing_data($listing_id)` - Returns comprehensive array with:
- All ACF fields with safe defaults
- Type validation (int, float, array, boolean, string)
- Calculated derived fields (total_bathrooms, price_per_sqft)
- Formatted address components

### Utility Functions
- `hph_build_address($data)` - Builds full address from components
- `hph_has_media($listing_data)` - Checks for photo/video content
- `hph_has_location($listing_data)` - Validates lat/lng coordinates
- `hph_format_price($price)` - Formats currency display
- `hph_format_sqft($sqft)` - Formats square footage
- `hph_format_lot_size($acres, $sqft)` - Formats lot size
- `hph_format_bathrooms($full, $half)` - Formats bathroom count
- `hph_get_status_display($status)` - Maps status codes to display text
- `hph_has_content($value)` - Checks for meaningful content

## Template Parts Created

### 1. `listing-not-found.php`
- 404-style page for missing/invalid listings
- Includes navigation back to listings page
- Uses HPH framework styling

### 2. `neighborhood-section.php`
- Displays subdivision, county, zoning information
- Shows directions with proper formatting
- Conditional rendering based on data availability

### 3. `schools-section.php`
- School district information
- Elementary, middle, and high school details
- Icon-based display with hover effects

### 4. `similar-listings.php`
- Queries related listings by city or property type
- Excludes current listing
- Uses existing listing-card template
- Links to full listings page

## Data Validation

### Field Defaults
All 70+ ACF fields have appropriate defaults:
```php
'listing_price' => 0,
'bedrooms' => 0,
'property_description' => '',
'photo_gallery' => [],
'latitude' => null,
// ... etc
```

### Type Safety
- Numbers: Validated with `is_numeric()` before casting
- Arrays: Checked with `is_array()` before assignment
- Strings: Sanitized with `sanitize_text_field()`
- Booleans: Cast to proper boolean type

### Conditional Rendering
Template parts only load when they have content:
```php
<?php if (hph_has_media($listing_data)) : ?>
    <?php get_template_part('template-parts/listing/gallery-tour-section', null, $template_args); ?>
<?php endif; ?>
```

## Styling

### CSS Location
`assets/css/framework/features/listing/single-listing.css`

### Key Styles
- Responsive grid layout (stacked mobile, 2-column desktop)
- Consistent spacing with HPH framework
- Hover effects on interactive elements
- Mobile-optimized layout adjustments

## Error Handling

### Missing ACF Fields
All field access uses null coalescing with sensible defaults:
```php
$value = get_field('field_name', $listing_id) ?: $default_value;
```

### Missing Template Parts
If a template part doesn't exist, WordPress fails gracefully with no output.

### Invalid Listing IDs
Function returns `false` for invalid listings, triggering not-found template.

## Performance Considerations

### Asset Loading
- CSS only loads on single listing pages
- Conditional JavaScript loading via asset system
- Font Awesome loaded once globally

### Database Queries
- Single data fetch with comprehensive defaults
- Similar listings limited to 3 results
- Proper WordPress query caching

### Image Optimization
- Uses WordPress thumbnail functions
- Lazy loading supported by framework
- Multiple image sizes available

## Backward Compatibility

### Data Migration
- Maintains compatibility with existing ACF field structure
- Graceful degradation for missing fields
- No database changes required

### Template Hierarchy
- Follows WordPress standard template hierarchy
- Can be overridden by child themes
- Existing hooks preserved

## Testing

### Required Test Cases
1. **Valid listing with full data** - All sections display
2. **Minimal listing data** - Only essential sections show
3. **Missing/invalid listing ID** - Shows not-found template
4. **No photos/media** - Gallery section hidden
5. **No location data** - Map section hidden
6. **No school/neighborhood data** - Respective sections hidden

### Browser Testing
- Mobile responsiveness (320px+)
- Desktop layout (1024px+)
- Touch device interactions
- Accessibility compliance

## Future Enhancements

### Potential Additions
- Print-friendly styling
- Social sharing buttons
- Listing favorites functionality
- PDF export option
- Virtual tour integration
- Property comparison tools

### SEO Improvements
- Schema.org markup for real estate
- Open Graph meta tags
- Twitter Card integration
- Breadcrumb navigation

## Maintenance Notes

### Regular Updates
- Monitor ACF field additions/changes
- Update default values as needed
- Test template parts after theme updates
- Validate CSS after framework changes

### Performance Monitoring
- Check page load times
- Monitor database query count
- Review asset loading efficiency
- Test mobile performance
