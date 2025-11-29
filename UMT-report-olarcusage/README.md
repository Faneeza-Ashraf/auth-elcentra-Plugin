# Custom Course Report Plugin for Moodle

## Overview

The Custom Course Report Plugin provides comprehensive reporting capabilities for Moodle courses, extracting detailed information about course structure, activities, faculty, and student engagement. This plugin generates reports in multiple formats (CSV, Excel, PDF) and integrates seamlessly with the Course Outline Block plugin for complete course oversight.

## Features

### Comprehensive Data Collection
The plugin extracts 19 different data fields for each course:

1. **Semester**: Top parent category of the course
2. **School**: Second parent category of the course
3. **Program**: Third parent category of the course
4. **Course Code**: First part of course name (before first hyphen)
5. **Course Title**: Second part of course name (between first and second hyphen)
6. **Faculty**: Names of all teachers in the course (comma-separated)
7. **Email**: Email addresses of all teachers (comma-separated)
8. **Section**: Combination of second-last and last parts of course name
9. **Assignments Given**: Count of assignment activities
10. **Quizzes Taken**: Count of quiz activities
11. **Chats**: Count of chat and forum activities
12. **Learning Material Uploaded**: Count of file and URL activities
13. **Course Outline Added**: Date when outline was added or "no"
14. **Students on LMS**: Total enrolled students
15. **LMS Usage Status**: Activity usage level (1-3 scale)
16. **Recording Link**: Count of Zoom, YouTube, and Google Drive activities
17. **Course Link**: Direct URL to the course
18. **Template Status**: Compliance with OLARC template ("Yes"/"No")
19. **% of Usage Activity**: Percentage of activity types used (0-100%)

### Modern User Interface
- **Mustache Templates**: Complete separation of logic and presentation
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices
- **Interactive Tables**: Sortable columns, search functionality, and filtering
- **Export Controls**: Intuitive buttons for CSV, Excel, and PDF export
- **Real-time Statistics**: Dynamic summary statistics and visualizations

### Multiple Export Formats
- **CSV Export**: For data analysis and spreadsheet applications
- **Excel Export**: Native Excel format with formatting and formulas
- **PDF Export**: Professional reports suitable for printing and sharing

### Integration Capabilities
- **Course Outline Block Integration**: Automatic detection of uploaded course outlines
- **Report Builder API**: Built on Moodle's modern Report Builder framework
- **Fallback Mechanisms**: Graceful degradation when integrated plugins are unavailable

## Installation

### Prerequisites
- Moodle 3.9 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher / PostgreSQL 10 or higher
- Course Outline Block Plugin (recommended for full functionality)

### Installation Steps

1. **Download and Extract**
   ```bash
   # Extract the plugin to your Moodle report directory
   cd /path/to/moodle/report/
   tar -xzf olarcusage_plugin.tar.gz
   ```

2. **Database Installation**
   - Navigate to Site Administration → Notifications
   - Follow the upgrade prompts to install any required database changes

3. **Verify Installation**
   - Go to Site Administration → Reports
   - Confirm "Custom Course Report" appears in the list

### Configuration

#### Report Settings
1. Navigate to Site Administration → Plugins → Reports → Custom Course Report
2. Configure the following settings:
   - **Template Course Name**: Name of the template course for comparison
   - **Export Limits**: Maximum number of records per export
   - **Cache Duration**: How long to cache report data

#### Permissions
The plugin uses the following capabilities:
- `report/olarcusage:view` - View the report
- `report/olarcusage:viewall` - View reports for all courses
- `moodle/site:viewreports` - Access to reports section

## Usage

### Accessing the Report

#### Site-wide Report
1. Navigate to Site Administration → Reports → Custom Course Report
2. The report will display data for all courses
3. Use filters and search to find specific courses

#### Course-specific Report
1. Navigate to any course
2. Go to Course Administration → Reports → Custom Course Report
3. The report will display data only for that course

### Using the Interface

#### Search and Filtering
- Use the search box to find courses by name, code, or faculty
- Click column headers to sort data
- Use the reset button to clear all filters

#### Exporting Data
1. Click the desired export format button (CSV, Excel, or PDF)
2. The file will be generated and downloaded automatically
3. Large reports may take a few moments to generate

#### Understanding the Data

##### LMS Usage Status
- **1**: No activities in the course
- **2**: Some topics contain no activities
- **3**: Every topic contains at least one activity

##### Usage Activity Percentage
Calculated based on five activity types:
- Assignments
- Quizzes
- Chats & Forums
- Files & URLs
- Zoom & Google Drive links

100% means all five activity types are used in the course.

##### Template Status
Compares the first topic name with the template course "Mandatory OLARC Pattern F2024":
- **Yes**: First topic is named "Read This First!"
- **No**: First topic has a different name

## Technical Architecture

### Mustache Template System

The plugin uses a comprehensive Mustache template system for all UI components:

```
templates/
├── report_page.mustache           # Main report container
├── report_header.mustache         # Report title and metadata
├── export_buttons.mustache        # Export format buttons
├── report_table.mustache          # Main data table
├── table_controls.mustache        # Search and filter controls
├── usage_status_badge.mustache    # LMS usage status display
├── course_link.mustache           # Course link formatting
├── template_status_badge.mustache # Template compliance status
├── usage_percentage.mustache      # Activity usage progress bar
├── no_data_message.mustache       # Empty state display
└── report_statistics.mustache     # Summary statistics
```

### Data Processing Pipeline

1. **Course Discovery**: Identifies all relevant courses based on context
2. **Category Analysis**: Extracts semester, school, and program from category hierarchy
3. **Course Parsing**: Parses course names to extract code, title, and section
4. **Faculty Extraction**: Identifies all teachers and their contact information
5. **Activity Counting**: Analyzes course modules to count different activity types
6. **Outline Detection**: Checks for course outline uploads (via block plugin or fallback)
7. **Usage Analysis**: Calculates usage statistics and compliance metrics
8. **Template Rendering**: Formats data using Mustache templates

### Database Queries

The plugin uses optimized database queries to minimize performance impact:

- **Course Data**: Single query to get all course information
- **Category Hierarchy**: Recursive query to build category paths
- **User Enrollment**: Efficient joins to get faculty and student counts
- **Activity Analysis**: Bulk queries grouped by module type
- **Outline Status**: Integration with block plugin database or fallback queries

### Caching Strategy

- **Template Caching**: Mustache templates are compiled and cached
- **Data Caching**: Report data is cached for configurable periods
- **Query Optimization**: Database queries use appropriate indexes and joins

## Course Outline Integration

### Block Plugin Integration

When the Course Outline Block plugin is installed:

1. **Primary Detection**: Checks `mdl_block_courseoutline` table for uploaded outlines
2. **Accurate Timestamps**: Uses actual upload dates from the block plugin
3. **Teacher Attribution**: Shows which teacher uploaded the outline

### Fallback Mechanism

When the block plugin is not available:

1. **Activity Search**: Looks for page or resource activities with "outline" or "syllabus" in the name
2. **Date Estimation**: Uses activity creation/modification dates
3. **Graceful Degradation**: Continues to function with reduced accuracy

### Benefits of Integration

- **Standardized Process**: Ensures consistent outline upload process
- **Accurate Reporting**: Provides precise upload dates and teacher information
- **Administrative Oversight**: Enables tracking of outline completion across all courses

## Customization

### Template Customization

Templates can be overridden in your theme:

```
theme/yourtheme/templates/report_olarcusage/
├── report_page.mustache           # Override main layout
├── report_table.mustache          # Customize table appearance
└── export_buttons.mustache        # Modify export options
```

### Styling Customization

The plugin includes comprehensive CSS that can be customized:

```css
/* Report container */
.olarcusage-page {
    /* Your custom styles */
}

/* Export buttons */
.export-btn {
    /* Your custom styles */
}

/* Data table */
.report-table {
    /* Your custom styles */
}
```

### Language Customization

Add custom language strings:

```php
// In your theme's lang/en/theme_yourtheme.php
$string['olarcusage:reporttitle'] = 'Your Custom Title';
$string['olarcusage:exportdescription'] = 'Your custom description';
```

### Data Field Customization

The plugin is designed to be extensible. Additional data fields can be added by:

1. Modifying the data extraction functions in `lib.php`
2. Adding new template variables
3. Updating export functions to include new fields
4. Adding appropriate language strings

## Performance Optimization

### Database Performance
- Optimized queries with appropriate indexes
- Bulk operations to minimize database round trips
- Efficient joins to reduce query complexity
- Caching of frequently accessed data

### Template Performance
- Compiled Mustache templates for faster rendering
- Minimal template logic for better performance
- Efficient data preparation to reduce template complexity

### Export Performance
- Streaming exports for large datasets
- Memory-efficient processing
- Progress indicators for long-running exports

## API Reference

### Main Functions

#### `report_olarcusage_get_course_data($courseid)`
Extracts all report data for a specific course.

**Parameters:**
- `$courseid` (int): The course ID

**Returns:**
- `array`: Associative array with all report fields

#### `report_olarcusage_get_all_courses_data()`
Extracts report data for all accessible courses.

**Returns:**
- `array`: Array of course data arrays

#### `report_olarcusage_export_csv($data, $filename)`
Exports data to CSV format.

**Parameters:**
- `$data` (array): Report data
- `$filename` (string): Output filename

#### `report_olarcusage_export_excel($data, $filename)`
Exports data to Excel format.

**Parameters:**
- `$data` (array): Report data
- `$filename` (string): Output filename

#### `report_olarcusage_export_pdf($data, $filename)`
Exports data to PDF format.

**Parameters:**
- `$data` (array): Report data
- `$filename` (string): Output filename

### Utility Functions

#### `report_olarcusage_parse_course_name($coursename)`
Parses course name to extract code, title, and section.

#### `report_olarcusage_get_course_faculty($courseid)`
Gets all faculty members for a course.

#### `report_olarcusage_count_activities($courseid)`
Counts different types of activities in a course.

#### `report_olarcusage_get_usage_status($courseid)`
Determines LMS usage status (1-3 scale).

## License

This plugin is licensed under the GNU General Public License v3.0. See the LICENSE file for details.

## Changelog

### Version 2.0.0 (2025-07-15)
- **Major Update**: Complete refactor to use Mustache templates
- **New Feature**: Integration with Course Outline Block plugin
- **Enhancement**: Modern responsive user interface
- **Enhancement**: Improved export functionality
- **Enhancement**: Better performance and caching
- **Enhancement**: Comprehensive documentation

### Version 1.0.0 (2025-07-15)
- Initial release
- Core reporting functionality
- CSV, Excel, and PDF export
- Basic HTML interface

## Credits

Developed by Manus AI for comprehensive course reporting and analysis in Moodle environments.

