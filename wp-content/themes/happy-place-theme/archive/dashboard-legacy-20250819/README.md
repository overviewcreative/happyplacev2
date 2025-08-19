# Dashboard Legacy Files Archive

**Archive Date:** August 19, 2025
**Reason:** Consolidating dashboard functionality to use only essential files

## Archived Files

### Templates
- `page-admin-dashboard.php` - Legacy admin dashboard page template
- `template-parts/dashboard/` - Entire legacy dashboard template parts directory
  - `section-overview.php`
  - `section-profile.php`
  - `section-profile-redesigned.php`
  - `section-listings.php`
  - `section-listings-redesigned.php`
  - `section-agents-redesigned.php`
  - `listing-card-grid.php`
  - `listing-card-list.php`
  - `listing-card-map.php`
  - `listings/add-listing.php`
  - `listings/edit-listing.php`
  - `listings/list-listings.php`
  - `listings/view-listing.php`

### Assets
- `assets/js/dashboard-admin.js` - Legacy admin dashboard JavaScript
- `assets/css/pages/dashboard.css` - Legacy page-specific dashboard CSS
- `assets/css/components/dashboard-forms.css` - Legacy dashboard forms CSS
- `assets/css/framework/base/dashboard-variables.css` - Legacy dashboard variables

## Remaining Active Dashboard Files

The following files remain active and are the only dashboard files in use:

1. **Main Template:** `/templates/dashboard/dashboard-main.php`
2. **AJAX Handler:** `/inc/ajax/dashboard-ajax.php`
3. **Main CSS:** `/assets/css/dashboard/dashboard.css`
4. **Main JavaScript:** `/assets/js/dashboard/dashboard-main.js`
5. **Bridge Functions:** `/inc/bridge/dashboard-bridge.php`

## Restoration

To restore any archived files, copy them back to their original locations within the theme directory structure.

## Notes

- All references to archived files should be removed from functions.php and other theme files
- The new dashboard system uses URL routing via `/agent-dashboard/` endpoint
- Asset enqueuing has been updated to work with the new simplified structure