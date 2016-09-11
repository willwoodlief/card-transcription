<?php

#returns jobs based on what filters are set in the get statement, this is a protected page that all roles can use
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'/users/includes/header_json.php';
require_once $abs_us_root.$us_url_root.'lib/aws/aws-autoloader.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';

if (!securePage($_SERVER['PHP_SELF'])){die();}
if ($settings->site_offline==1){die("The site is currently offline.");}


$status = Input::get('status');
switch($status) {
    case false: {
        printErrorJSONAndDie('did not get status in params ');
        break;
    }

    case 'not_started': {
        $b_is_transcribed = false;
        $b_is_checked = false;
        break;
    }

    case 'transcribed_not_checked': {
        $b_is_transcribed = true;
        $b_is_checked = false;
        break;
    }

    case 'checked': {
        $b_is_transcribed = true;
        $b_is_checked = true;
        break;
    }

    default: {
        printErrorJSONAndDie('did not recognize status (not_started |transcribed_not_checked| checked) ');
    }

}

$transcriber_id = Input::get('transcriber_id');
if ($transcriber_id) {
    $transcriber_id = intval($transcriber_id);
}

$checker_id = Input::get('transcriber_id');
if ($checker_id) {
    $checker_id = intval($checker_id);
}

$info = get_jobs($b_is_transcribed,$b_is_checked,$transcriber_id,$checker_id);
printOkJSONAndDie(['jobs'=>$info]);




