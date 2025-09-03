# CSV Import/Export Tool - Production Documentation

## Overview

The Happy Place plugin now includes a comprehensive CSV import/export system that allows users to:

- Import listings, leads, and agents from CSV files with interactive field mapping
- Export data in multiple formats (CSV, JSON, XML) with filtering options
- Use pre-built templates for common MLS and real estate platforms
- Track import progress with real-time feedback and error handling

## Features Implemented

### 1. Import Service (Enhanced)
- **File**: `includes/services/class-import-service.php`
- **Functionality**: Robust CSV processing with validation, field mapping, and batch operations
- **Features**:
  - Auto-detection of field mappings
  - Progress tracking with session management
  - Error handling and validation
  - Support for listings, leads, and agents

### 2. Export Service (New)
- **File**: `includes/services/class-export-service.php`
- **Functionality**: Unified export system for all data types
- **Features**:
  - Multiple format support (CSV, JSON, XML)
  - Filtering by status, type, date range
  - Batch processing for large datasets
  - Template-based exports

### 3. Frontend Import Interface (New)
- **File**: `assets/js/admin/csv-import.js`
- **Functionality**: Complete JavaScript interface for CSV imports
- **Features**:
  - Drag-and-drop file upload
  - Step-by-step wizard interface
  - Interactive field mapping with templates
  - Real-time progress tracking
  - Error display and handling

### 4. Styling (New)
- **File**: `assets/css/admin/import-export.css`
- **Functionality**: Professional styling for import/export interfaces
- **Features**:
  - Responsive design
  - Step indicators
  - Progress bars and animations
  - Mobile-friendly layout

### 5. Enhanced Admin Pages
- **Files**: Updated `includes/admin/class-admin-menu.php`
- **Functionality**: Modern admin interfaces for import/export
- **Features**:
  - Improved import page with wizard interface
  - Enhanced export page with filtering options
  - Quick export links for common reports

### 6. Sample Templates (New)
- **Files**: `assets/templates/*.csv`
- **Templates Included**:
  - MLS Standard format
  - Zillow import format
  - Realtor.com format

## Usage Instructions

### Importing Data

1. **Navigate to Import Page**
   - Go to Happy Place → Import in WordPress admin
   - The new wizard interface will load

2. **Upload CSV File**
   - Click "Choose File" or drag-and-drop a CSV file
   - System will analyze the file and show preview

3. **Map Fields**
   - Review auto-mapped fields
   - Manually map unmapped columns
   - Use templates for common formats
   - Save custom mappings as templates

4. **Import Data**
   - Review settings and start import
   - Monitor real-time progress
   - View results and error summary

### Exporting Data

1. **Navigate to Export Page**
   - Go to Happy Place → Export in WordPress admin

2. **Configure Export**
   - Select format (CSV, JSON, XML)
   - Apply filters (status, type, date range)
   - Choose additional options

3. **Download**
   - Click export to generate file
   - File will download automatically
   - Use quick export links for common reports

## Technical Integration

### AJAX Endpoints

The system uses these AJAX actions:

- `hp_upload_csv` - Handle file upload and analysis
- `hp_import_csv` - Process CSV import with progress tracking
- `hp_cancel_import` - Cancel ongoing import
- `hp_export_listings` - Export listings data
- `hp_export_leads` - Export leads data
- `hp_export_agents` - Export agents data

### Hooks and Filters

Available for customization:

- `hp_import_field_mapping` - Modify field mapping options
- `hp_export_data_filters` - Customize export filters
- `hp_import_validate_row` - Custom row validation
- `hp_export_format_data` - Modify export data format

### Field Mapping

The system includes intelligent field mapping for:

- **Listings**: Address, price, bedrooms, bathrooms, etc.
- **Leads**: Name, email, phone, source, etc.
- **Agents**: Name, email, license, office, etc.

### Error Handling

Comprehensive error handling includes:

- File validation (size, format, structure)
- Data validation (required fields, data types)
- Import errors (duplicate detection, constraint violations)
- User-friendly error messages

## Configuration

### File Limits

- Maximum file size: 10MB (configurable via `hp_max_import_file_size` filter)
- Batch size: 100 rows (configurable via `hp_import_batch_size` filter)
- Memory limit: Optimized for large files

### Templates

Templates are stored in `assets/templates/` and can be:

- Downloaded by users as examples
- Used for auto-mapping field configurations
- Customized for specific MLS systems

## Security

The system includes:

- Nonce verification for all AJAX requests
- Capability checks (manage_options required)
- File type validation
- Sanitization of all user inputs
- Secure file handling

## Performance

Optimizations include:

- Batch processing for large imports
- Progress tracking to prevent timeouts
- Memory-efficient file reading
- Background processing support

## Browser Support

The interface supports:

- Chrome 60+
- Firefox 55+
- Safari 11+
- Edge 79+

## Troubleshooting

### Common Issues

1. **File Upload Fails**
   - Check file size (max 10MB)
   - Verify CSV format
   - Ensure proper file permissions

2. **Import Stalls**
   - Check server memory limits
   - Verify timeout settings
   - Review error logs

3. **Field Mapping Issues**
   - Use auto-mapping first
   - Check column headers in CSV
   - Save working mappings as templates

4. **Export Errors**
   - Check available disk space
   - Verify export permissions
   - Try smaller date ranges

### Support

For issues or questions:

1. Check WordPress error logs
2. Enable WP_DEBUG for detailed errors
3. Review browser console for JavaScript errors
4. Contact plugin support with error details

## Future Enhancements

Planned improvements:

- Scheduled imports/exports
- API integration for real-time sync
- Advanced field transformation rules
- Bulk edit capabilities during import
- Enhanced analytics and reporting
