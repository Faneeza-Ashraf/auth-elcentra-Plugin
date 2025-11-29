<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for the Course Outline block.
 *
 * @package    block_courseoutline
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Course Outline';
$string['courseoutline'] = 'Course Outline';

// Capabilities
$string['courseoutline:view'] = 'View course outline block';
$string['courseoutline:upload'] = 'Upload course outline';
$string['courseoutline:addinstance'] = 'Add a new course outline block';
$string['courseoutline:myaddinstance'] = 'Add a new course outline block to My Moodle';

// Block content
$string['outline_available'] = 'Course outline is available for download:';
$string['download_outline'] = 'Download Course Outline';
$string['upload_outline'] = 'Upload Course Outline';
$string['no_outline_yet'] = 'No course outline has been uploaded yet.';
$string['no_outline_available'] = 'Course outline is not available.';
$string['outline_uploaded_by'] = 'Uploaded by {$a}';

// Upload form
$string['upload_instructions'] = 'Please select a PDF file containing the course outline. Only PDF files are allowed, and only one file can be uploaded per course.';
$string['file_upload'] = 'Course Outline File';
$string['file_upload_help'] = 'Upload a PDF file containing the complete course outline and important information as specified in the OLARC template section 0.';
$string['upload_success'] = 'Course outline uploaded successfully.';
$string['upload_error'] = 'Error uploading course outline. Please try again.';
$string['file_too_large'] = 'The uploaded file is too large. Maximum file size is {$a}.';
$string['invalid_file_type'] = 'Only PDF files are allowed.';
$string['outline_already_exists'] = 'A course outline has already been uploaded for this course.';

// Errors
$string['error_no_permission'] = 'You do not have permission to upload course outlines.';
$string['error_invalid_course'] = 'Invalid course specified.';
$string['error_file_not_found'] = 'The requested file could not be found.';

// Privacy
$string['privacy:metadata:block_courseoutline'] = 'The Course Outline block stores information about uploaded course outline files.';
$string['privacy:metadata:block_courseoutline:courseid'] = 'The ID of the course the outline belongs to.';
$string['privacy:metadata:block_courseoutline:teacherid'] = 'The ID of the teacher who uploaded the outline.';
$string['privacy:metadata:block_courseoutline:timeuploaded'] = 'The time when the outline was uploaded.';

// Navigation
$string['back_to_course'] = 'Back to course';
$string['upload_new_outline'] = 'Upload new outline';

// File management
$string['replace_outline'] = 'Replace existing outline';
$string['confirm_replace'] = 'Are you sure you want to replace the existing course outline? This action cannot be undone.';
$string['outline_replaced'] = 'Course outline has been replaced successfully.';

// Admin settings
$string['max_file_size'] = 'Maximum file size';
$string['max_file_size_desc'] = 'Maximum size for uploaded course outline files (in bytes).';
$string['allowed_file_types'] = 'Allowed file types';
$string['allowed_file_types_desc'] = 'File types that can be uploaded as course outlines.';

// Block title
$string['block_title'] = 'Course Outline';
$string['block_title_help'] = 'This block allows teachers to upload and students to download the course outline.';

// Status messages
$string['status_no_outline'] = 'No outline uploaded';
$string['status_outline_available'] = 'Outline available';
$string['last_updated'] = 'Last updated: {$a}';
$string['uploaded_by'] = 'Uploaded by: {$a}';

// Accessibility
$string['aria_download_outline'] = 'Download the course outline PDF file';
$string['aria_upload_outline'] = 'Upload a new course outline PDF file';
$string['numeric'] = 'Please enter a valid number.';
$string['total_presentations'] = 'Total Presentations';
$string['total_assignments'] = 'Total Assignments';
$string['total_quizzes'] = 'Total Quizzes';
$string['total_workshops'] = 'Total Workshops';
$string['update_outline'] = 'Update Course Outline';
$string['required'] = 'This field is required.';
$string['update_success'] = 'Course outline updated successfully.';
$string['override'] = 'Can update course outline after deadline';
$string['updateperiodexpired'] = 'The one-week period for updating the course outline has ended. Please contact an administrator if you need to make changes.';
$string['courseoutline:update'] = 'Can update course outline';
$string['courseoutline:override'] = 'Can update course outline after deadline';

$string['onfronpage'] = 'This block displays the outline for a specific course. Please enter a course to view its outline.';
