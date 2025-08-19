# Spacing Audit & Guidelines

## Overview
This document outlines the spacing utilities and patterns implemented across the Happy Place theme templates.

## CSS Custom Properties (Variables)
```css
--hph-space-xs: 0.5rem;   /* 8px */
--hph-space-sm: 0.75rem;  /* 12px */
--hph-space-md: 1.5rem;   /* 24px */
--hph-space-lg: 2.5rem;   /* 40px */
--hph-space-xl: 3rem;     /* 48px */
--hph-space-2xl: 4rem;    /* 64px */
--hph-space-3xl: 6rem;    /* 96px */
```

## Spacing Utility Classes

### Section Spacing
- `.section-header-spacing` - Default section header bottom margin (var(--hph-space-lg))
- `.section-header-spacing-sm` - Small section header spacing (var(--hph-space-md))
- `.section-header-spacing-lg` - Large section header spacing (var(--hph-space-xl))

### Content Spacing
- `.content-spacing > * + *` - Vertical spacing between content elements
- `.content-spacing-sm > * + *` - Smaller content spacing
- `.content-spacing-lg > * + *` - Larger content spacing

### Hero Spacing
- `.hero-content-spacing > * + *` - Base spacing between hero elements
- Specific hero element spacing for titles, subtitles, buttons, and search

### Card Spacing
- `.card-content-spacing > * + *` - Spacing within card content
- `.card-header-spacing` - Space below card headers
- `.card-footer-spacing` - Space above card footers

### Form Spacing
- `.form-spacing > * + *` - Spacing between form elements
- `.form-spacing-sm/.form-spacing-lg` - Variations
- `.form-row-spacing` - Gap for form rows (grid)

### List Spacing
- `.list-spacing > li + li` - Spacing between list items
- `.list-spacing-sm/.list-spacing-lg` - Variations

## Template Implementation

### front-page.php
✅ **Updated spacing:**
- Hero content uses `.hero-content-spacing`
- All section headers use `.section-header-spacing`
- Form elements use `.form-row-spacing`

**Sections Updated:**
- Hero section
- Featured listings section header
- Featured agents section header  
- Services section header
- Open houses section header

### template-parts/advanced-search-form.php
✅ **Updated spacing:**
- Form grids use `.form-row-spacing` instead of `gap-4`
- Consistent spacing between form sections

### Spacing File Organization
✅ **Properly organized:**
- All spacing utilities are in `/assets/css/framework/utilities/spacing.css`
- Removed duplicate spacing from `utilities.css`
- Clean separation of concerns

## Spacing Patterns

### Section Headers
```php
<div class="section-header text-center section-header-spacing">
    <h2 class="section-title">Title</h2>
    <p class="section-subtitle">Subtitle</p>
</div>
```

### Form Layouts
```php
<div class="grid grid-cols-1 md:grid-cols-3 form-row-spacing">
    <!-- form fields -->
</div>
```

### Content Areas
```php
<div class="content-spacing">
    <p>Paragraph 1</p>
    <p>Paragraph 2</p>
    <div>Other content</div>
</div>
```

## Mobile Responsiveness
All spacing utilities are built mobile-first and scale appropriately across breakpoints:
- Base spacing works for mobile
- Larger screens get proportionally increased spacing
- CSS custom properties ensure consistency

## Best Practices

1. **Use semantic spacing classes** instead of utility margins/padding when possible
2. **Maintain consistent vertical rhythm** with standardized spacing
3. **Group related elements** with appropriate spacing utilities
4. **Test across breakpoints** to ensure proper scaling
5. **Use CSS variables** for maintainable spacing values

## Next Steps

1. ✅ Audit front-page.php spacing - COMPLETE
2. ✅ Update template-parts spacing - COMPLETE  
3. ⏳ Audit archive templates spacing - IN PROGRESS
4. ⏳ Audit single templates spacing - PENDING
5. ⏳ Test responsive spacing behavior - PENDING

## Notes
- All spacing follows a consistent scale based on CSS custom properties
- Mobile-first approach ensures proper scaling
- Semantic class names improve maintainability
- Grid gap utilities provide consistent spacing in layout containers
