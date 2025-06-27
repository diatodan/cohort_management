<?php
defined('MOODLE_INTERNAL') || die();

/**
 * LOCAL_COHORT_MANAGEMENT_COHORT_LIMIT - number of cohorts to be derived
 */
define('LOCAL_COHORT_MANAGEMENT_COHORT_LIMIT', 10);

/**
 * Adds cohort-related nodes to the user's profile page.
 *
 * @param core_user\output\myprofile\tree $tree
 * @param stdClass $user
 * @param bool $iscurrentuser
 * @param stdClass $course
 * @return void
 */
function local_cohort_management_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/cohort/lib.php');

    $showallcohorts = optional_param('showallcohorts', 0, PARAM_INT);

    $sql = 'SELECT %s FROM {cohort} c
              JOIN {cohort_members} cm ON c.id = cm.cohortid
             WHERE cm.userid = ?';

    if (!is_siteadmin($user->id)) {
        $sql .= ' AND c.visible = 1';
    }

    if ($showallcohorts) {
        $cohorts = $DB->get_records_sql(sprintf($sql, 'c.*'), [$user->id]);
    } else {
        $cohorts = $DB->get_records_sql(sprintf($sql, 'c.*'), [$user->id], 0, LOCAL_COHORT_MANAGEMENT_COHORT_LIMIT);
    }

    $cohortdetailscategory = new core_user\output\myprofile\category('cohortdetails', get_string('cohorts', 'core_cohort'));
    $tree->add_category($cohortdetailscategory);

    if ($cohorts) {
        foreach ($cohorts as $cohort) {
            $cohorturl = new moodle_url('/cohort/assign.php', ['id' => $cohort->id]);
            $cohortlink = html_writer::link($cohorturl, $cohort->name);
            $cohortnode = new core_user\output\myprofile\node('cohortdetails', 'cohort' . $cohort->id, $cohortlink);
            $tree->add_node($cohortnode);
        }
    }

    // Liste déroulante pour ajouter l'utilisateur à une cohorte
    $cohortslist = $DB->get_records_menu('cohort', ['visible' => 1], 'name', 'id, name');

    if (!empty($cohortslist)) {
    // Title as a separate profile node
    $titleNode = new core_user\output\myprofile\node('cohortdetails', 'cohorttitle', get_string('select_cohort', 'local_cohort_management'));
    $tree->add_node($titleNode);

    // Start the form
    $cohortselectform = html_writer::start_tag('form', [
        'action' => new moodle_url('/local/cohort_management/cohort_adder.php'),
        'method' => 'post'
    ]);
    $cohortselectform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'userid', 'value' => $user->id]);

    // Dropdown list with spacing
    $cohortselectform .= html_writer::start_tag('select', ['name' => 'cohortid', 'class' => 'custom-select mb-3']);
    foreach ($cohortslist as $id => $name) {
        $cohortselectform .= html_writer::tag('option', $name, ['value' => $id]);
    }
    $cohortselectform .= html_writer::end_tag('select');

    // Submit button
    $cohortselectform .= html_writer::tag('button', get_string('add_to_cohort', 'local_cohort_management'), [
        'type' => 'submit',
        'class' => 'btn btn-success'
    ]);
    $cohortselectform .= html_writer::end_tag('form');

    // Add form as a separate node
    $cohortselectnode = new core_user\output\myprofile\node('cohortdetails', 'cohortaddnode', $cohortselectform);
    $tree->add_node($cohortselectnode);
}

    // Bouton pour retirer l'utilisateur de toutes les cohortes
    if ($cohorts) {
        $removeform = html_writer::start_tag('form', [
            'action' => new moodle_url('/local/cohort_management/cohort_cleaner.php'),
            'method' => 'post'
        ]);
        $removeform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'userid', 'value' => $user->id]);
        $removeform .= html_writer::tag('button', get_string('button_unenrollcohorts', 'local_cohort_management'), [
            'type' => 'submit',
            'class' => 'btn btn-danger mt-3' // Ajout d'un espace (margin-top)
        ]);
        $removeform .= html_writer::end_tag('form');

        $removeformnode = new core_user\output\myprofile\node('cohortdetails', 'cohortremove', $removeform);
        $tree->add_node($removeformnode);
    }
}
