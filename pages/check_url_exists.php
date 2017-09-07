<?php

#returns jobs based on what filters are set in the get statement, this is a protected page that all roles can use
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'/users/includes/header_json.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';

if (!securePage($_SERVER['PHP_SELF'])){die();}
if ($settings->site_offline==1){die("The site is currently offline.");}


$url = Input::get('url');
if (!$url) {
    printErrorJSONAndDie('Need URL');
}

$theURL = trim($url);


$what = test_site_connection($theURL);
$ret = ['exists'=>$what, 'url_checked'=>$theURL];


printOkJSONAndDie($ret);






