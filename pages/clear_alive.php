<?php

#returns jobs based on what filters are set in the get statement, this is a protected page that all roles can use
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'/users/includes/header_json.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';

if (!securePage($_SERVER['PHP_SELF'])){die();}
if ($settings->site_offline==1){die("The site is currently offline.");}


$job_id = Input::get('jobid');
if (!$job_id) {
    printErrorJSONAndDie('Need Job ID');
}

$job_id = intval(trim($job_id));
$what_happened = clearJobViewStamp($job_id);
if ($what_happened) {
    printOkJSONAndDie('time cleared for user id '. $user->data()->id);
} else {
    $db = DB::getInstance();
    printErrorJSONAndDie('Could not clear view time :'.$db->error_info());
}







