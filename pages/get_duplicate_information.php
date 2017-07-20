<?php

#returns jobs based on what filters are set in the get statement, this is a protected page that all roles can use
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'/users/includes/header_json.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';

if (!securePage($_SERVER['PHP_SELF'])){die();}
if ($settings->site_offline==1){die("The site is currently offline.");}


$client_id = Input::get('client_id');
if (!$client_id) {
    printErrorJSONAndDie('Need user id');
}

$client_id = trim($client_id);


$email = Input::get('email');
if (!$email) {
    printErrorJSONAndDie('Need Email');
}

$email = to_utf8(trim($email));

if (!$email || !$client_id) {
    //a field is empty, return a non duplicate message
    printOkJSONAndDie(['message' => 'no data to check duplicates', 'is_duplicate' => false]);
}

$results = checkForDuplicateEmailsWithUser($email,$client_id);

$number_duplicates_found = count($results);
if (!$results || $number_duplicates_found == 0) {
    printOkJSONAndDie(['message' => 'no duplicates', 'is_duplicate' => false]);
} else {


    $data = [];
    foreach ($results as $rec) {
        $linkName = "Job# " . $rec->id . ' ' . $rec->fname . ' ' . $rec->mname . ' ' . $rec->lname;
        $rec->link =  '<a href="'.$abs_us_web_root.'pages/job.php?jobid=' . $rec->id .
            '" target="_BLANK"> ' .
            $linkName .
            '</a>';
        array_push($data,$rec);
    }
    $imess = '1 duplicate found';
    if ($number_duplicates_found > 1) {
        $imess = ($number_duplicates_found ) . ' Duplicates Found ' . 'with User ID of <b>' . $client_id . '</b>';
    }
    printOkJSONAndDie(['message' => $imess, 'is_duplicate' => true,'duplicates'=>$data]);
}








