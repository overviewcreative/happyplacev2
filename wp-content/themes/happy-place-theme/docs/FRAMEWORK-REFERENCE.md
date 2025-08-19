# HPH Framework - Complete Component & Class Reference

## Overview
HPH Framework is a comprehensive CSS framework built specifically for real estate websites. This document provides a complete reference of all available classes and components.

## Table of Contents

1. [Base Styles](#base-styles)
2. [Typography](#typography)
3. [Colors](#colors)
4. [Layout System](#layout-system)
5. [Spacing Utilities](#spacing-utilities)
6. [Components](#components)
7. [Real Estate Components](#real-estate-components)
8. [Utility Classes](#utility-classes)

---

## Base Styles

### Reset & Normalize
- Modern CSS reset with sensible defaults
- Consistent box-sizing: border-box
- Responsive images by default

### Variables
The framework uses CSS custom properties for theming and consistency:

#### Color Variables
```css
/* Brand Colors */
--hph-primary: #51bae0;
--hph-primary-dark: #0284c7;
--hph-primary-light: #7dd3fc;
--hph-secondary: #38bdf8;

/* Semantic Colors */
--hph-success: #059669;
--hph-warning: #d97706;
--hph-danger: #dc2626;
--hph-info: #38bdf8;

/* Gray Scale */
--hph-gray-50 to --hph-gray-900;

/* Real Estate Status Colors */
--hph-status-available: #059669;
--hph-status-pending: #f59e0b;
--hph-status-sold: #dc2626;
--hph-status-coming-soon: #8b5cf6;
```

#### Spacing Variables
```css
--hph-space-xs: 0.5rem;
--hph-space-sm: 0.75rem;
--hph-space-md: 1.5rem;
--hph-space-lg: 2.5rem;
--hph-space-xl: 3rem;
--hph-space-2xl: 4rem;
--hph-space-3xl: 6rem;
```

---

## Typography

### Font Families
- `.font-primary` - Poppins (headings)
- `.font-secondary` - Inter (body text)
- `.font-display` - Poppins (display text)
- `.font-mono` - Monospace fonts

### Text Sizes
- `.text-xs` - 0.75rem
- `.text-sm` - 0.875rem
- `.text-base` - 1rem
- `.text-lg` - 1.125rem
- `.text-xl` - 1.25rem
- `.text-2xl` - 1.5rem
- `.text-3xl` - 1.875rem
- `.text-4xl` - 2.25rem
- `.text-5xl` - 3rem
- `.text-6xl` - 3.75rem

### Font Weights
- `.font-light` - 300
- `.font-normal` - 400
- `.font-medium` - 500
- `.font-semibold` - 600
- `.font-bold` - 700
- `.font-extrabold` - 800

### Text Colors
- `.text-primary` - Brand primary color
- `.text-secondary` - Brand secondary color
- `.text-success` - Success color
- `.text-warning` - Warning color
- `.text-danger` - Danger color
- `.text-info` - Info color
- `.text-gray-{50-900}` - Gray scale variations

---

## Colors

### Background Colors
- `.bg-primary` - Primary background
- `.bg-primary-light` - Light primary background
- `.bg-primary-dark` - Dark primary background
- `.bg-secondary` - Secondary background
- `.bg-success` - Success background
- `.bg-warning` - Warning background
- `.bg-danger` - Danger background
- `.bg-info` - Info background
- `.bg-white` - White background
- `.bg-gray-{50-900}` - Gray scale backgrounds

### Border Colors
- `.border-primary` - Primary border
- `.border-secondary` - Secondary border
- `.border-success` - Success border
- `.border-warning` - Warning border
- `.border-danger` - Danger border
- `.border-gray-{100-900}` - Gray border variations

---

## Layout System

### Container
- `.container` - Responsive max-width container with auto margins

### Grid System
- `.grid` - CSS Grid container
- `.grid-cols-{1-12}` - Grid column definitions
- `.gap-{1-8}` - Grid gap spacing
- `.col-span-{1-12}` - Column span utilities

### Flexbox
- `.flex` - Flex container
- `.flex-col` - Flex column direction
- `.flex-row` - Flex row direction
- `.flex-wrap` - Allow flex wrapping
- `.flex-nowrap` - Prevent flex wrapping
- `.flex-1` - Flex grow 1
- `.flex-auto` - Flex auto
- `.flex-none` - Flex none

### Alignment
- `.justify-start` - Justify content start
- `.justify-center` - Justify content center
- `.justify-end` - Justify content end
- `.justify-between` - Justify content space-between
- `.justify-around` - Justify content space-around
- `.items-start` - Align items start
- `.items-center` - Align items center
- `.items-end` - Align items end

---

## Spacing Utilities

### Margin
- `.m-{0-32}` - Margin all sides
- `.mx-{0-32}` - Margin horizontal
- `.my-{0-32}` - Margin vertical
- `.mt-{0-32}` - Margin top
- `.mr-{0-32}` - Margin right
- `.mb-{0-32}` - Margin bottom
- `.ml-{0-32}` - Margin left

### Padding
- `.p-{0-32}` - Padding all sides
- `.px-{0-32}` - Padding horizontal
- `.py-{0-32}` - Padding vertical
- `.pt-{0-32}` - Padding top
- `.pr-{0-32}` - Padding right
- `.pb-{0-32}` - Padding bottom
- `.pl-{0-32}` - Padding left

### Semantic Spacing
- `.section-header-spacing` - Section header margin
- `.content-spacing` - Content vertical rhythm
- `.hero-content-spacing` - Hero section spacing
- `.card-content-spacing` - Card internal spacing
- `.form-spacing` - Form element spacing
- `.list-spacing` - List item spacing

### Space Between
- `.space-y-{1-16}` - Vertical space between children
- `.space-x-{1-16}` - Horizontal space between children

---

## Components

### Buttons

#### Base Button
- `.btn` - Base button class

#### Button Variants
- `.btn-primary` - Primary button
- `.btn-secondary` - Secondary button
- `.btn-success` - Success button
- `.btn-warning` - Warning button
- `.btn-danger` - Danger button
- `.btn-info` - Info button
- `.btn-outline` - Outline button
- `.btn-ghost` - Ghost button
- `.btn-link` - Link button

#### Button Sizes
- `.btn-xs` - Extra small button
- `.btn-sm` - Small button
- `.btn-lg` - Large button
- `.btn-xl` - Extra large button

#### Button States
- `.btn-loading` - Loading state
- `.btn-active` - Active state
- `.btn-disabled` - Disabled state

#### Special Buttons
- `.btn-icon` - Icon-only button
- `.btn-floating` - Floating action button
- `.btn-gradient` - Gradient button

### Cards

#### Base Card
- `.card` - Base card component

#### Card Parts
- `.card-header` - Card header section
- `.card-body` - Card body content
- `.card-footer` - Card footer section
- `.card-image` - Card image container

#### Card Variants
- `.card-elevated` - Elevated shadow
- `.card-flat` - No shadow
- `.card-bordered` - With border
- `.card-feature` - Feature card style
- `.card-stat` - Statistics card
- `.card-pricing` - Pricing card

#### Card Modifiers
- `.card-hoverable` - Hover effects
- `.card-clickable` - Clickable card

### Forms

#### Form Elements
- `.form-input` - Text input
- `.form-select` - Select dropdown
- `.form-textarea` - Textarea
- `.form-checkbox` - Checkbox
- `.form-radio` - Radio button
- `.form-label` - Form label
- `.form-help` - Help text

#### Form States
- `.form-input-valid` - Valid state
- `.form-input-invalid` - Invalid state
- `.form-input-warning` - Warning state

#### Form Feedback
- `.form-feedback` - Feedback message
- `.form-feedback-valid` - Valid feedback
- `.form-feedback-invalid` - Invalid feedback
- `.form-feedback-warning` - Warning feedback

#### Form Layouts
- `.form-group` - Form field group
- `.form-row` - Form row
- `.form-inline` - Inline form

### Badges

#### Base Badge
- `.badge` - Base badge

#### Badge Variants
- `.badge-primary` - Primary badge
- `.badge-secondary` - Secondary badge
- `.badge-success` - Success badge
- `.badge-warning` - Warning badge
- `.badge-danger` - Danger badge
- `.badge-info` - Info badge

#### Badge Sizes
- `.badge-sm` - Small badge
- `.badge-lg` - Large badge

#### Badge Styles
- `.badge-outline` - Outline style
- `.badge-soft` - Soft style
- `.badge-pill` - Pill shape

### Alerts

#### Base Alert
- `.alert` - Base alert component

#### Alert Variants
- `.alert-primary` - Primary alert
- `.alert-success` - Success alert
- `.alert-warning` - Warning alert
- `.alert-danger` - Danger alert
- `.alert-info` - Info alert

#### Alert Elements
- `.alert-heading` - Alert heading
- `.alert-text` - Alert text
- `.alert-link` - Alert link

#### Alert Modifiers
- `.alert-dismissible` - Dismissible alert
- `.alert-solid` - Solid background

### Navigation

#### Navbar
- `.navbar` - Navigation bar
- `.navbar-brand` - Brand/logo area
- `.navbar-nav` - Navigation menu
- `.navbar-item` - Navigation item
- `.navbar-link` - Navigation link

#### Navbar Variants
- `.navbar-primary` - Primary navbar
- `.navbar-light` - Light navbar
- `.navbar-dark` - Dark navbar
- `.navbar-transparent` - Transparent navbar

#### Breadcrumbs
- `.breadcrumb` - Breadcrumb navigation
- `.breadcrumb-item` - Breadcrumb item

#### Pagination
- `.pagination` - Pagination component
- `.pagination-item` - Pagination item
- `.pagination-link` - Pagination link

---

## Real Estate Components

### Property Cards
- `.property-card` - Property listing card
- `.property-image` - Property image container
- `.property-badge` - Property status badge
- `.property-price` - Price display
- `.property-features` - Features list
- `.property-address` - Address display

### Agent Cards
- `.agent-card` - Agent profile card
- `.agent-avatar` - Agent photo
- `.agent-info` - Agent information
- `.agent-contact` - Contact buttons
- `.agent-rating` - Rating display
- `.agent-stats` - Agent statistics

### Property Features
- `.feature-item` - Individual feature
- `.feature-icon` - Feature icon
- `.feature-text` - Feature text
- `.features-grid` - Features grid layout

### Price Displays
- `.price-display` - Main price display
- `.price-range` - Price range
- `.price-per-sqft` - Price per square foot
- `.price-change` - Price change indicator

### Property Status
Real estate-specific status badges using CSS variables:
- Available, Pending, Sold, Coming Soon, Off Market
- Residential, Commercial, Land, Rental, Luxury
- Hot, Featured, Premium, Exclusive

### Search Components
- `.search-form` - Property search form
- `.search-filters` - Filter controls
- `.search-results` - Results container
- `.search-summary` - Results summary

---

## Utility Classes

### Display
- `.block` - Block display
- `.inline` - Inline display
- `.inline-block` - Inline block
- `.flex` - Flex display
- `.grid` - Grid display
- `.hidden` - Hidden element
- `.sr-only` - Screen reader only

### Position
- `.static` - Static position
- `.relative` - Relative position
- `.absolute` - Absolute position
- `.fixed` - Fixed position
- `.sticky` - Sticky position

### Top/Right/Bottom/Left
- `.top-{0-96}` - Top positioning
- `.right-{0-96}` - Right positioning
- `.bottom-{0-96}` - Bottom positioning
- `.left-{0-96}` - Left positioning

### Z-Index
- `.z-{0-50}` - Z-index values

### Width & Height
- `.w-{size}` - Width utilities
- `.h-{size}` - Height utilities
- `.min-w-{size}` - Minimum width
- `.min-h-{size}` - Minimum height
- `.max-w-{size}` - Maximum width
- `.max-h-{size}` - Maximum height

### Overflow
- `.overflow-hidden` - Hidden overflow
- `.overflow-auto` - Auto overflow
- `.overflow-scroll` - Scroll overflow

### Borders
- `.border` - Border all sides
- `.border-{t|r|b|l}` - Border specific sides
- `.border-{0-8}` - Border width
- `.border-solid` - Solid border
- `.border-dashed` - Dashed border
- `.border-dotted` - Dotted border

### Border Radius
- `.rounded` - Base border radius
- `.rounded-sm` - Small radius
- `.rounded-lg` - Large radius
- `.rounded-full` - Full radius (circle)
- `.rounded-none` - No radius

### Shadows
- `.shadow-none` - No shadow
- `.shadow-sm` - Small shadow
- `.shadow` - Default shadow
- `.shadow-lg` - Large shadow
- `.shadow-xl` - Extra large shadow
- `.shadow-2xl` - 2XL shadow
- `.shadow-inner` - Inner shadow

### Opacity
- `.opacity-{0-100}` - Opacity values

### Transforms
- `.scale-{50-150}` - Scale transforms
- `.rotate-{0-180}` - Rotation transforms
- `.translate-x-{size}` - X translation
- `.translate-y-{size}` - Y translation

### Transitions
- `.transition` - All properties
- `.transition-colors` - Color transitions
- `.transition-opacity` - Opacity transitions
- `.transition-transform` - Transform transitions

### Responsive Utilities
All utilities can be prefixed with breakpoint indicators:
- `sm:` - Small screens (640px+)
- `md:` - Medium screens (768px+)
- `lg:` - Large screens (1024px+)
- `xl:` - Extra large screens (1280px+)
- `2xl:` - 2XL screens (1536px+)

---

## Real Estate Specific Variables

### Property Status Colors
```css
--hph-status-available: #059669;
--hph-status-pending: #f59e0b;
--hph-status-sold: #dc2626;
--hph-status-coming-soon: #8b5cf6;
--hph-status-off-market: #6b7280;
--hph-status-contingent: #fb923c;
--hph-status-new: #3b82f6;
--hph-status-reduced: #10b981;
```

### Property Type Colors
```css
--hph-type-residential: #51bae0;
--hph-type-commercial: #8b5cf6;
--hph-type-land: #84cc16;
--hph-type-rental: #f97316;
--hph-type-luxury: #fbbf24;
--hph-type-investment: #6366f1;
```

### Marketing Colors
```css
--hph-hot: #ef4444;
--hph-featured: #fbbf24;
--hph-premium: #a78bfa;
--hph-exclusive: #1f2937;
```

### Agent Status Colors
```css
--hph-agent-online: #10b981;
--hph-agent-offline: #9ca3af;
--hph-agent-busy: #f59e0b;
--hph-agent-verified: #3b82f6;
```

---

## Usage Examples

### Property Card Example
```html
<div class="card property-card">
    <div class="card-image relative">
        <img src="property.jpg" alt="Property" class="w-full h-48 object-cover">
        <span class="badge badge-success absolute top-3 left-3">For Sale</span>
        <div class="property-price absolute top-3 right-3 bg-primary text-white px-3 py-1 rounded-full">
            $450,000
        </div>
    </div>
    <div class="card-body">
        <h3 class="card-title">Modern Family Home</h3>
        <p class="property-address text-gray-600">123 Main St, Anytown, CA</p>
        <div class="property-features flex gap-4 mt-3">
            <span><i class="fas fa-bed"></i> 3 bed</span>
            <span><i class="fas fa-bath"></i> 2 bath</span>
            <span><i class="fas fa-square"></i> 1,500 sqft</span>
        </div>
    </div>
</div>
```

### Search Form Example
```html
<form class="search-form bg-white rounded-lg p-6 shadow-lg">
    <div class="grid grid-cols-1 md:grid-cols-3 form-row-spacing">
        <input type="text" class="form-input" placeholder="Location">
        <select class="form-select">
            <option>Property Type</option>
        </select>
        <button class="btn btn-primary">Search</button>
    </div>
</form>
```

---

## Framework Statistics

- **Total Components:** 50+
- **Utility Classes:** 200+
- **Color Variables:** 40+
- **Spacing Variables:** 7
- **Responsive Breakpoints:** 5
- **Real Estate Specific:** 25+ components

This framework provides everything needed to build modern, responsive real estate websites with consistent design patterns and professional styling.
