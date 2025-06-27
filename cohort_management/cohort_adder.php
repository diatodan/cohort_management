<?php
require('../../config.php');
require_login();
require_capability('moodle/cohort:assign', context_system::instance()); // Vérifie que l'utilisateur a la permission

global $DB;

// Récupération des paramètres
$userid = required_param('userid', PARAM_INT);
$cohortid = required_param('cohortid', PARAM_INT);

// Vérifie si la cohorte existe
if (!$DB->record_exists('cohort', ['id' => $cohortid])) {
    throw new moodle_exception('invalidcohortid', 'local_cohort_management');
}

// Vérifie si l'utilisateur est déjà dans la cohorte
if ($DB->record_exists('cohort_members', ['cohortid' => $cohortid, 'userid' => $userid])) {
    redirect(new moodle_url('/user/profile.php', ['id' => $userid]),
        get_string('already_in_cohort', 'local_cohort_management'), null, \core\output\notification::NOTIFY_WARNING);
}

// Ajoute l'utilisateur à la cohorte
require_once($CFG->dirroot . '/cohort/lib.php');
cohort_add_member($cohortid, $userid);

// Redirige avec un message de succès
redirect(new moodle_url('/user/profile.php', ['id' => $userid]),
    get_string('added_to_cohort_success', 'local_cohort_management'), null, \core\output\notification::NOTIFY_SUCCESS);
