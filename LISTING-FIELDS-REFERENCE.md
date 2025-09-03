# Listing Fields Reference - Happy Place Plugin

This document catalogs all available listing fields from the plugin's bridge functions, organized by component where they would logically be used.

## 📋 TODO LIST - Component Development

### ✅ Completed
- [x] Created comprehensive field reference documentation
- [x] Updated component organization recommendations with user specifications
- [x] **HERO Component Implementation - COMPLETE**
  - [x] Implement `adapt_listing_hero()` method in AdapterService
  - [x] Update `listing/hero.php` to use adapter service
  - [x] Add carousel background support with navigation & indicators
  - [x] Add "Updated X Days Ago" badge functionality
  - [x] Implement proper null handlers for missing data
  - [x] Add responsive design and mobile optimization
  - [x] Add interactive carousel with JavaScript controls
  - [x] Add status badges with variant styling
  - [x] Add share/save action buttons

### 🔥 Ready for Testing
- [ ] **Test HERO Component** - Verify with sample listing data

### 📋 Pending Components
- [ ] **DETAILS Component** - Property specifications, financial info
- [ ] **FEATURES Component** - Interior/exterior features, amenities
- [ ] **GALLERY Component** - Images, virtual tours, floor plans  
- [ ] **CONTACT-FORM Component** - Lead generation forms with context
- [ ] **LOCATION Component** (Future) - Geographic data, neighborhood info

### 📝 Documentation Updates Needed
- [ ] Update component integration examples
- [ ] Add adapter service usage patterns
- [ ] Document component testing procedures

---

## 🏠 Basic Property Information

### **Core Details**
- `hpt_get_listing_title($listing_id)` - Property title
- `hpt_get_listing_price($listing_id)` - Raw price value
- `hpt_get_listing_price_formatted($listing_id)` - Formatted price display
- `hpt_get_listing_price_raw($listing_id)` - Raw numeric price
- `hpt_get_listing_price_per_sqft($listing_id)` - Price per square foot
- `hpt_get_listing_status($listing_id)` - Listing status (active, sold, etc.)
- `hpt_get_listing_status_label($listing_id)` - Human-readable status
- `hpt_get_listing_status_badge($listing_id)` - Status with styling info
- `hpt_get_listing_mls_number($listing_id)` - MLS listing number
- `hpt_get_listing_property_type($listing_id)` - Property type (house, condo, etc.)
- `hpt_get_listing_property_type_label($listing_id)` - Human-readable property type
- `hpt_get_listing_style($listing_id)` - Architectural style
- `hpt_get_listing_date($listing_id)` - Listing date

### **Physical Specifications**
- `hpt_get_listing_bedrooms($listing_id)` - Number of bedrooms
- `hpt_get_listing_bathrooms($listing_id)` - Total bathrooms (full + half)
- `hpt_get_listing_bathrooms_full($listing_id)` - Full bathrooms only
- `hpt_get_listing_bathrooms_half($listing_id)` - Half bathrooms only  
- `hpt_get_listing_bathrooms_formatted($listing_id)` - Formatted bathroom display
- `hpt_get_listing_square_feet($listing_id)` - Interior square footage
- `hpt_get_listing_square_feet_formatted($listing_id)` - Formatted square footage
- `hpt_get_listing_year_built($listing_id)` - Year property was built
- `hpt_get_listing_garage_spaces($listing_id)` - Number of garage spaces

### **Lot & Land Information**
- `hpt_get_listing_lot_size($listing_id)` - Lot size (smart unit detection)
- `hpt_get_listing_lot_size_acres($listing_id)` - Lot size in acres
- `hpt_get_listing_lot_size_sqft($listing_id)` - Lot size in square feet
- `hpt_get_listing_lot_size_in_sqft($listing_id)` - Convert to square feet
- `hpt_get_listing_lot_size_formatted($listing_id)` - Formatted lot size display
- `hpt_get_listing_lot_features($listing_id)` - Lot-specific features

## 📍 Address & Location

### **Address Components**
- `hpt_get_listing_address($listing_id, $format = 'full')` - Complete address array/string
- `hpt_get_listing_street_address($listing_id)` - Full street address
- `hpt_get_listing_street_number($listing_id)` - House number
- `hpt_get_listing_street_name($listing_id)` - Street name only
- `hpt_get_listing_street_type($listing_id)` - Street type (St, Ave, etc.)
- `hpt_get_listing_street_prefix($listing_id)` - Street prefix (N, S, etc.)
- `hpt_get_listing_street_suffix($listing_id)` - Street suffix
- `hpt_get_listing_unit_number($listing_id)` - Unit/apartment number
- `hpt_get_listing_city($listing_id)` - City
- `hpt_get_listing_state($listing_id)` - State
- `hpt_get_listing_zip_code($listing_id)` - ZIP code
- `hpt_get_listing_county($listing_id)` - County
- `hpt_get_listing_neighborhood($listing_id)` - Neighborhood name

### **Privacy & Display Controls**
- `hpt_get_listing_address_display($listing_id)` - Address display preference
- `hpt_get_listing_address_public($listing_id, $format = 'full')` - Public-safe address
- `hpt_get_listing_parcel_number($listing_id)` - Property parcel number

### **Geographic & Mapping**
- `hpt_get_listing_coordinates($listing_id)` - Latitude/longitude coordinates
- `hpt_get_listing_map_data($listing_id)` - Complete map integration data
- `hpt_get_listing_property_boundaries($listing_id)` - Property boundary data

## 📝 Content & Marketing

### **Descriptions & Marketing**
- `hpt_get_listing_description($listing_id)` - Main property description
- `hpt_get_listing_marketing_title($listing_id)` - Marketing headline
- `hpt_get_listing_highlights($listing_id)` - Key selling points
- `hpt_get_listing_showing_instructions($listing_id)` - Showing instructions
- `hpt_get_listing_internal_notes($listing_id)` - Internal agent notes (private)

### **Media & Virtual Assets**
- `hpt_get_listing_featured_image($listing_id, $size = 'large')` - Primary image
- `hpt_get_listing_gallery($listing_id)` - Photo gallery array
- `hpt_get_listing_gallery_data($listing_id)` - Enhanced gallery data
- `hpt_get_listing_gallery_enhanced($listing_id)` - Gallery with metadata
- `hpt_get_listing_virtual_tour($listing_id)` - Virtual tour embed
- `hpt_get_listing_virtual_tour_url($listing_id)` - Virtual tour URL
- `hpt_get_listing_video($listing_id)` - Property video
- `hpt_get_listing_floor_plans($listing_id)` - Floor plan images/PDFs

## 🏗️ Features & Amenities

### **Property Features (Comprehensive)**
- `hpt_get_listing_features($listing_id)` - All features array
- `hpt_get_listing_features_categorized($listing_id)` - Features grouped by type
- `hpt_get_listing_amenities($listing_id)` - Property amenities
- `hpt_get_listing_additional_features($listing_id)` - Additional feature list

### **Interior Features**
- `hpt_get_listing_interior_features($listing_id)` - Interior-specific features
- `hpt_get_listing_kitchen_appliances($listing_id)` - Kitchen appliances list
- `hpt_get_listing_flooring($listing_id)` - Flooring types/materials
- `hpt_get_listing_heating($listing_id)` - Heating system info
- `hpt_get_listing_cooling($listing_id)` - Cooling/AC system info
- `hpt_get_listing_heating_cooling($listing_id)` - Combined HVAC info

### **Exterior & Structural**
- `hpt_get_listing_exterior_features($listing_id)` - Exterior features
- `hpt_get_listing_parking_garage($listing_id)` - Parking details
- `hpt_get_listing_parking($listing_id)` - General parking info
- `hpt_get_listing_construction($listing_id)` - Construction materials
- `hpt_get_listing_roof_info($listing_id)` - Roof details
- `hpt_get_listing_foundation($listing_id)` - Foundation information

### **Systems & Utilities**
- `hpt_get_listing_utilities($listing_id)` - Utility information
- `hpt_get_listing_security_features($listing_id)` - Security systems
- `hpt_get_listing_accessibility_features($listing_id)` - Accessibility features
- `hpt_get_listing_green_features($listing_id)` - Eco-friendly features

## 💰 Financial Information

### **Costs & Fees**
- `hpt_get_listing_property_taxes($listing_id)` - Annual property taxes
- `hpt_get_listing_property_taxes_formatted($listing_id)` - Formatted taxes
- `hpt_get_listing_hoa_fees($listing_id)` - HOA fees (raw)
- `hpt_get_listing_hoa_fees_formatted($listing_id)` - HOA fees (formatted)
- `hpt_get_listing_estimated_insurance($listing_id)` - Insurance estimates
- `hpt_get_listing_estimated_utilities($listing_id)` - Utility estimates

### **Commission & Business**
- `hpt_get_listing_buyer_commission($listing_id)` - Buyer agent commission
- `hpt_get_listing_commission($listing_id)` - General commission info

## 👥 People & Relationships

### **Agent Information**
- `hpt_get_listing_agent($listing_id)` - Primary listing agent
- `hpt_get_listing_agent_id($listing_id)` - Agent ID
- `hpt_get_listing_co_agent($listing_id)` - Co-listing agent
- `hpt_get_listing_office($listing_id)` - Listing office
- `hpt_get_listing_overview_agent_data($listing_id)` - Agent overview data

### **Community & Context**
- `hpt_get_listing_community($listing_id)` - Community/subdivision
- `hpt_get_listing_open_houses($listing_id, $upcoming_only = true)` - Open house schedule

## 📊 Analytics & Performance

### **Engagement Metrics**
- `hpt_get_listing_views($listing_id, $period = 'total')` - View statistics
- `hpt_get_listing_saves_count($listing_id)` - Number of saves/favorites

### **Overview & Summaries**
- `hpt_get_listing_overview_details($listing_id)` - Key details summary

## 🌍 Neighborhood & Lifestyle

### **Walkability & Transit**
- `hpt_get_listing_walk_score($listing_id)` - Walk Score rating
- `hpt_get_listing_transit_score($listing_id)` - Transit Score rating  
- `hpt_get_listing_bike_score($listing_id)` - Bike Score rating

### **Area Information**
- `hpt_get_listing_neighborhood_description($listing_id)` - Neighborhood overview
- `hpt_get_listing_crime_rating($listing_id)` - Safety/crime statistics
- `hpt_get_listing_noise_level($listing_id)` - Noise level data
- `hpt_get_listing_demographics($listing_id)` - Area demographics

### **Nearby Amenities**
- `hpt_get_listing_nearby_places($listing_id)` - Points of interest
- `hpt_get_listing_nearby_schools($listing_id)` - School information
- `hpt_get_listing_commute_times($listing_id)` - Commute data to major areas

---

## 📦 Component Organization Recommendations

### **HERO Component (`listing/hero.php`)**
**Primary Fields:**
- Title, Price, Status, Street Address, City, State, Zip, Carousel Background, Null Handlers
- Key stats: Bedrooms, Bathrooms, Square Feet, Lot Size
- Status badge, Property Type, Updated X Days Ago Badege

### **DETAILS Component (`listing/details.php`)**
**Property Specifications:**
- All physical specs, Year Built, Lot Size
- Property Type, Style, MLS Number
- Financial info: Taxes, HOA, Estimates

### **FEATURES Component (`listing/features.php`)**
**Amenities & Features:**
- Interior/Exterior Features, Appliances, HVAC
- Security, Accessibility, Green Features
- Parking, Construction, Systems

### **GALLERY Component (`listing/gallery.php`)**
**Visual Content:**
- Featured Image, Gallery, Virtual Tour, Video
- Floor Plans

### **CONTACT-FORM Component (`listing/contact-form.php`)**
**Lead Generation:**
- Agent info, Contact details, Showing Instructions
- Property context (Title, Price, Address)

### **LOCATION Component (Future)**
**Geographic & Area:**
- Address components, Coordinates, Map data
- Neighborhood info, Walkability scores
- Schools, Commute times, Demographics

---

*This reference covers all 100+ available listing bridge functions from the Happy Place plugin. Use this to determine which fields belong in which components for optimal organization and user experience.*