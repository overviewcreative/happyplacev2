# Single Agent Template - Complete Implementation

## Overview
Enhanced single-agent.php template that provides a comprehensive agent profile page with all requested sections:

- **Hero Section**: Agent photo, name, title, quick stats, and contact actions
- **Bio & Credentials**: Detailed biography, specialties, languages, and credentials
- **Contact Information**: Complete contact details with social media links and contact form
- **Agent Listings**: Display of current property listings from the agent
- **Agent Blog/Content**: Agent's blog posts and content area

## Features

### Hero Section
- Large agent photo with featured badge (if applicable)
- Agent name, title, and office affiliation
- Quick performance statistics (years experience, properties sold, sales volume)
- Prominent contact actions (phone, email)
- Responsive design with background image overlay

### Bio & Credentials Section
- Full agent biography with rich text formatting
- Specialty tags showing areas of expertise
- Languages spoken
- Professional credentials and license information

### Contact Information Sidebar
- Complete contact details (phone, email, address, license)
- Social media links (Facebook, Instagram, LinkedIn, Twitter)
- Integrated contact form with AJAX submission
- Sticky positioning for better UX

### Agent Listings Section
- Query and display agent's current property listings
- Uses existing listing card template
- Shows up to 6 listings with "View All" link
- Graceful handling when no listings are available

### Agent Blog/Content Area
- Display agent's blog posts and content
- Query posts with agent_author meta field
- Post thumbnails, excerpts, and metadata
- Responsive layout with sidebar widgets

### Sidebar Widgets
- Quick contact actions
- Performance statistics
- Office information
- Responsive design

## Technical Implementation

### CSS Framework Integration
- Uses existing CSS framework classes and variables
- Custom single-agent.css for specific styling
- Responsive design with mobile-first approach
- Smooth animations and transitions

### JavaScript Features
- AJAX contact form submission
- Smooth scrolling for anchor links
- Analytics tracking for agent interactions
- Form validation and error handling

### WordPress Integration
- Proper WordPress hooks and filters
- ACF field integration for agent data
- Custom post type support (agent, listing)
- AJAX handlers for form submission

### Performance Optimizations
- Lazy loading for images
- Optimized CSS with PostCSS
- Minified and concatenated assets
- Efficient database queries

## File Structure

```
single-agent.php              # Main template file
src/css/single-agent.css      # Custom styles
dist/css/single-agent.*.css   # Compiled styles
functions.php                 # AJAX handlers and hooks
```

## ACF Fields Required

The template expects these ACF fields on the agent post type:

```
- first_name (text)
- last_name (text)
- title (text)
- email (email)
- phone (text)
- bio (textarea/wysiwyg)
- years_experience (number)
- specialties (select/checkbox multiple)
- languages (select/checkbox multiple)
- license_number (text)
- total_sales_volume (number)
- total_listings_sold (number)
- featured (true/false)
- office (post_object - office post type)
- facebook (url)
- instagram (url)
- linkedin (url)
- twitter (url)
```

## Listing Association

Listings should have an ACF field:
```
- agent (post_object - agent post type)
```

## Blog Post Association

Blog posts can have an optional ACF field:
```
- agent_author (post_object - agent post type)
```

## Usage

The template automatically loads when viewing a single agent post (URL: `/agent/agent-name/`).

### Customization Options

1. **Styling**: Modify `src/css/single-agent.css` and rebuild assets
2. **Layout**: Adjust template structure in `single-agent.php`
3. **Functionality**: Add custom JavaScript or PHP functions
4. **Fields**: Add or modify ACF fields as needed

### Contact Form

The integrated contact form uses AJAX to submit messages to the agent's email address. The form includes:
- Name (required)
- Email (required)
- Phone (optional)
- Message (required)

Messages are sent via WordPress wp_mail() function with proper sanitization and validation.

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile responsive design
- Progressive enhancement for JavaScript features
- Graceful degradation for older browsers

## Accessibility

- Semantic HTML structure
- Proper heading hierarchy
- Alt text for images
- Keyboard navigation support
- ARIA labels where appropriate
- Color contrast compliance

## Performance

- Optimized CSS delivery
- Minimal JavaScript footprint
- Efficient database queries
- Image optimization ready
- Caching-friendly structure

## Future Enhancements

Potential improvements for future versions:
- Agent testimonials section
- Property sold history
- Calendar integration for appointments
- Live chat integration
- Agent comparison features
- Advanced analytics and reporting
