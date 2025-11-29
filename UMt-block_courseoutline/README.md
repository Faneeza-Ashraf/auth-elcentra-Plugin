# Course Outline Block Plugin for Moodle

## Overview

The Course Outline Block Plugin provides a standardized way for teachers to upload course outlines in Moodle courses. This plugin ensures consistency across all courses by providing a dedicated block that allows teachers to upload PDF course outlines and makes them available to students for download.

## Features

### Core Functionality
- **Single File Upload**: Teachers can upload one PDF file per course containing the course outline
- **Student Access**: Students can download the uploaded course outline
- **One-Time Upload**: Once uploaded, the file cannot be replaced (ensuring consistency)
- **PDF Only**: Only PDF files are accepted for upload
- **Database Tracking**: All uploads are tracked with teacher information and timestamps

### User Interface
- **Modern Design**: Built with responsive Mustache templates
- **Intuitive Interface**: Clear upload and download buttons with status indicators
- **Mobile Friendly**: Responsive design works on all devices
- **Accessibility**: Full ARIA support and keyboard navigation

### Integration
- **Report Plugin Integration**: Works seamlessly with the Custom Course Report plugin
- **Theme Compatibility**: Can be integrated into custom themes for automatic placement
- **Permission System**: Uses Moodle's capability system for access control

## Installation

### Prerequisites
- Moodle 3.9 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher / PostgreSQL 10 or higher

### Installation Steps

1. **Download and Extract**
   ```bash
   # Extract the plugin to your Moodle blocks directory
   cd /path/to/moodle/blocks/
   tar -xzf courseoutline_plugin.tar.gz
   ```

2. **Database Installation**
   - Navigate to Site Administration → Notifications
   - Follow the upgrade prompts to install the database tables

3. **Verify Installation**
   - Go to Site Administration → Plugins → Blocks → Manage blocks
   - Confirm "Course Outline" appears in the list

### Configuration

#### Block Settings
1. Navigate to Site Administration → Plugins → Blocks → Course Outline
2. Configure the following settings:
   - **Maximum File Size**: Set the maximum allowed file size (default: 10MB)
   - **Default Block Position**: Choose where the block appears by default

#### Permissions
The plugin uses the following capabilities:
- `block/courseoutline:addinstance` - Add the block to a course
- `block/courseoutline:upload` - Upload course outline files
- `block/courseoutline:view` - View and download course outlines

## Usage

### For Teachers

#### Uploading a Course Outline
1. Navigate to your course
2. Locate the "Course Outline" block
3. Click "Upload Course Outline" button
4. Select your PDF file (must be under the size limit)
5. Click "Upload Course Outline" to submit

#### Important Notes for Teachers
- Only PDF files are accepted
- Only one file can be uploaded per course
- Once uploaded, the file cannot be replaced
- The upload date and your name will be recorded

### For Students

#### Downloading a Course Outline
1. Navigate to the course
2. Locate the "Course Outline" block
3. Click "Download Course Outline" button
4. The PDF will download to your device

### For Administrators

#### Managing Course Outlines
Administrators can view course outline statistics and manage uploads through:
- The Custom Course Report plugin (if installed)
- Direct database queries for bulk operations
- File system access for backup purposes

## Database Schema

### Table: `mdl_block_courseoutline`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| courseid | BIGINT | Course ID (foreign key to mdl_course) |
| teacherid | BIGINT | User ID of teacher who uploaded (foreign key to mdl_user) |
| fileid | BIGINT | File ID (foreign key to mdl_files) |
| timeuploaded | BIGINT | Unix timestamp of upload |

### Indexes
- Primary key on `id`
- Unique index on `courseid`
- Index on `teacherid`
- Index on `timeuploaded`

## API Reference

### Manager Class Methods

#### `block_courseoutline\manager::get_course_outline($courseid)`
Returns the course outline record for a specific course.

**Parameters:**
- `$courseid` (int): The course ID

**Returns:**
- `stdClass|false`: Course outline record or false if not found

#### `block_courseoutline\manager::has_course_outline($courseid)`
Checks if a course has an outline uploaded.

**Parameters:**
- `$courseid` (int): The course ID

**Returns:**
- `bool`: True if outline exists, false otherwise

#### `block_courseoutline\manager::get_download_url($courseid)`
Gets the download URL for a course outline.

**Parameters:**
- `$courseid` (int): The course ID

**Returns:**
- `moodle_url|false`: Download URL or false if no outline exists

### Renderer Methods

#### `render_block_content($data)`
Renders the main block content using Mustache templates.

#### `render_upload_form($data)`
Renders the upload form interface.

#### `render_success_message($message)`
Renders success notifications.

#### `render_error_message($message)`
Renders error notifications.

## Mustache Templates

### Template Structure
```
templates/
├── view.mustache              # Main block view
├── upload_form.mustache       # Upload form interface
├── upload_page.mustache       # Full upload page
├── message.mustache           # Success/error messages
└── file_info.mustache         # File information display
```

### Template Context Variables

#### view.mustache
- `hasoutline` (bool): Whether course has outline
- `canupload` (bool): Whether user can upload
- `downloadurl` (string): Download URL
- `uploadurl` (string): Upload URL
- `timeuploaded` (string): Formatted upload date
- `teachername` (string): Name of uploading teacher

## Integration with Report Plugin

This block plugin is designed to work seamlessly with the Custom Course Report plugin. When both plugins are installed:

1. The report plugin automatically detects course outlines uploaded through this block
2. Course outline status is displayed in the report with accurate upload dates
3. Fallback mechanisms ensure compatibility even if one plugin is missing

## License

This plugin is licensed under the GNU General Public License v3.0.

## Credits

Developed by Manus AI for standardized course outline management in Moodle environments.

