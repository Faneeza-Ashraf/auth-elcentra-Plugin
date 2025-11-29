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
 * Defines the scheduled task for checking user subscriptions.
 *
 * @package    auth
 * @subpackage emailadmin
 *@copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

/**
 * Callback function to add extra information to a user's profile page.
 */
function auth_emailadmin_user_profile_callback($user, $course) {
    global $DB, $USER, $OUTPUT;

    $usercontext = context_user::instance($user->id);
    if (!has_capability('moodle/user:update', $usercontext, $USER)) {
        return null;
    }

    $signupdata = $DB->get_record('local_signup_data', ['userid' => $user->id]);
    $fs = get_file_storage();
    $files = $fs->get_area_files($usercontext->id, 'auth_emailadmin', 'user_documents', 0, 'sortorder', false);

    $html = '';
    $hascontent = false;
    if ($signupdata) {
        $datahtml = '';
        $fieldmap = [
            'dateofbirth'        => ['label' => 'dateofbirth', 'plugin' => 'local_signup'],
            'homeaddress'        => ['label' => 'homeaddress', 'plugin' => 'local_signup'],
            'parentname'         => ['label' => 'parentname', 'plugin' => 'local_signup'],
            'parentemail'        => ['label' => 'parentemail', 'plugin' => 'local_signup'],
            'phone2'             => ['label' => 'phone2', 'plugin' => 'local_signup'],
            'emergencycontactname' => ['label' => 'emergencycontactname', 'plugin' => 'local_signup'],
            'emergencyphone'     => ['label' => 'emergencyphone', 'plugin' => 'local_signup'],
            'healthinfo'         => ['label' => 'healthinfo_details', 'plugin' => 'local_signup'],
            'specialneeds'       => ['label' => 'specialneeds_details', 'plugin' => 'local_signup'],
            'desiredgrade'       => ['label' => 'desiredgrade', 'plugin' => 'local_signup'],
        ];

        foreach ($fieldmap as $field => $details) {
            if (!empty($signupdata->{$field})) {
                $value = $signupdata->{$field};
                $label = get_string($details['label'], $details['plugin']);
                if ($field === 'dateofbirth' && is_numeric($value)) {
                    $value = userdate($value, get_string('strftimedate', 'langconfig'));
                } else if ($field === 'healthinfo' || $field === 'specialneeds') {
                    $datahtml .= '<li><strong>' . s($label) . ':</strong><br>' . format_text($value, FORMAT_MOODLE, ['context' => $usercontext]) . '</li>';
                    continue;
                }
                $datahtml .= '<li><strong>' . s($label) . ':</strong> ' . s($value) . '</li>';
            }
        }

        if (!empty($datahtml)) {
            $hascontent = true;
            $html .= '<h3>' . get_string('applicationdetails', 'auth_emailadmin') . '</h3><div class="profile_tree"><ul>' . $datahtml . '</ul></div>';
        }
    }

    $filelisthtml = '';
    foreach ($files as $file) {
        if ($file->is_directory() || $file->get_filename() === '.') {
            continue;
        }
        $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
        $filelisthtml .= '<li>' . html_writer::link($url, $file->get_filename(), ['target' => '_blank']) . '</li>';
    }

    if (!empty($filelisthtml)) {
        $hascontent = true;
        $html .= '<h3>' . get_string('uploaddocuments', 'auth_emailadmin') . '</h3><div class="profile_tree"><ul>' . $filelisthtml . '</ul></div>';
    }

    return $hascontent ? $html : null;
}

/**
 * Callback for file API permissions.
 */
function auth_emailadmin_can_manage_documents($context, $user) {
    if ($context->contextlevel == CONTEXT_USER && $context->instanceid == $user->id) {
        return true;
    }
    if (has_capability('moodle/user:update', $context, $user)) {
        return true;
    }
    return false;
}