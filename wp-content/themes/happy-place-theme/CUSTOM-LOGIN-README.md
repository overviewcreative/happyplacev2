# Custom Login & Registration Pages

## Overview
Custom-designed login and registration pages built using the HPH Framework variables and utilities, providing a seamless branded experience for your real estate website.

## Features

### Login Page (`page-login.php`)
- **Modern Design**: Clean, professional interface with gradient hero section
- **Responsive Layout**: Mobile-first design that works on all devices
- **Framework Integration**: Uses HPH CSS variables and utility classes
- **Enhanced UX**: Password toggle, loading states, form validation
- **Accessibility**: WCAG compliant with focus states and screen reader support
- **Security**: WordPress nonce protection and proper sanitization

### Registration Page (`page-registration.php`)
- **User Types**: Support for buyers, sellers, agents, and investors
- **Validation**: Client-side and server-side validation
- **Terms Agreement**: Checkbox for terms and privacy policy acceptance
- **Auto-login**: Optional automatic login after registration

## Setup Instructions

### 1. Create Pages in WordPress Admin
1. Go to **Pages > Add New**
2. Create a page titled "Login"
3. In the Page Attributes metabox, select "Custom Login" template
4. Publish the page

Repeat for Registration:
1. Create a page titled "Register" or "Sign Up"
2. Select "Custom Registration" template
3. Publish the page

### 2. Update Navigation (Optional)
Update your theme's navigation menus to link to these custom pages instead of the default WordPress login.

### 3. Redirect Default Login (Optional)
Add this code to redirect the default WordPress login to your custom page:

```php
// Redirect default login to custom page
function redirect_to_custom_login() {
    if (!is_admin()) {
        wp_redirect(home_url('/login'));
        exit();
    }
}
add_action('login_init', 'redirect_to_custom_login');
```

## Customization

### Colors
The pages automatically use your theme's color variables:
- `--hph-primary`: Primary brand color
- `--hph-primary-dark`: Darker variant for hover states
- `--hph-gray-*`: Grayscale colors for text and backgrounds

### Logo
The pages will automatically use:
1. Custom brand logo (if set via `hph_get_brand_logo()`)
2. WordPress custom logo
3. Site name as fallback

### Social Login
To add social login buttons, integrate with plugins like:
- NextendSocialLogin
- Super Socializer
- Social Login

The `hph_social_login_buttons()` function is ready for integration.

## Styling Details

### CSS Architecture
- Uses HPH Framework variables for consistency
- Utility classes for spacing, typography, and layout
- Component-based styling for reusability
- Responsive breakpoints at 768px and 480px

### Key Utility Classes Used
- `hph-container`: Max-width container with responsive padding
- `hph-py-*`, `hph-px-*`: Padding utilities
- `hph-mb-*`, `hph-mt-*`: Margin utilities
- `hph-text-*`: Typography utilities
- `hph-bg-*`: Background color utilities
- `hph-rounded-*`: Border radius utilities
- `hph-shadow-*`: Box shadow utilities

### Form Components
- `hph-form-group`: Form field container
- `hph-form-label`: Styled labels with required indicators
- `hph-form-input`: Enhanced input fields with focus states
- `hph-form-input-with-icon-left`: Input with left icon
- `hph-btn-primary`: Primary button styling with hover effects

## Browser Support
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile browsers (iOS Safari, Chrome Mobile)
- Progressive enhancement for older browsers

## Accessibility Features
- Proper ARIA labels and roles
- Keyboard navigation support
- Focus indicators
- Screen reader friendly
- High contrast mode support
- Reduced motion preferences respected

## Security Features
- WordPress nonce verification
- Data sanitization and validation
- SQL injection prevention
- XSS protection
- CSRF protection

## File Structure
```
/theme-root/
├── page-login.php              # Login page template
├── page-registration.php       # Registration page template
├── src/css/login.css          # Login/registration styles
└── functions.php              # Enqueue functions (added)
```

## Troubleshooting

### Styles Not Loading
1. Check that the CSS file path is correct in functions.php
2. Verify the page template is properly selected
3. Clear any caching plugins

### Form Not Submitting
1. Check that WordPress registration is enabled (Settings > General)
2. Verify nonce fields are present
3. Check server error logs for PHP errors

### Template Not Found
1. Ensure page template slug matches the filename
2. Check file permissions
3. Refresh permalinks (Settings > Permalinks > Save)

## Future Enhancements
- Two-factor authentication integration
- Email verification workflow
- Password strength meter
- CAPTCHA integration
- Social media profile linking
- Custom user role assignments
