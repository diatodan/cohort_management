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
 * This file contains the code for the plugin integration.
 *
 * @package   local_cohort_management
 * @copyright 2025, Daniel Lawson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_login(); // Ensure the user is logged in

global $DB;

// Get the submitted user ID
$userid = required_param('userid', PARAM_INT);

// Check if the user has permission to unenroll users
if (!is_siteadmin() && !has_capability('moodle/cohort:manage', context_system::instance())) {
    throw new moodle_exception('accessdenied', 'admin');
}

// Get all cohorts the user is enrolled in
$cohorts = $DB->get_records('cohort_members', ['userid' => $userid]);

if ($cohorts) {
    require_once($CFG->dirroot . '/cohort/lib.php'); // Include cohort functions

    foreach ($cohorts as $cohort) {
        cohort_remove_member($cohort->cohortid, $userid); // Unenroll user
    }

    // Redirect back to the profile page with a success message
    redirect(new moodle_url('/user/profile.php', ['id' => $userid]), get_string('unenrolled_success', 'local_cohort_management'), null, \core\output\notification::NOTIFY_SUCCESS);
} else {
    // Redirect with an error message if no cohorts found
    redirect(new moodle_url('/user/profile.php', ['id' => $userid]), get_string('unenrolled_nocohorts', 'local_cohort_management'), null, \core\output\notification::NOTIFY_ERROR);
}