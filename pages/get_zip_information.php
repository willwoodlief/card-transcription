<?php

#returns jobs based on what filters are set in the get statement, this is a protected page that all roles can use
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'/users/includes/header_json.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';

if (!securePage($_SERVER['PHP_SELF'])){die();}
if ($settings->site_offline==1){die("The site is currently offline.");}


$zip = Input::get('zip');
if (!$zip) {
    printErrorJSONAndDie('Need zip');
}

$zip = trim($zip);

$db = DB::getInstance();

//do not convert to integer as some queries might have - , but use escaping to protect, its a prepared statement
// might enter a 9 digit code but database only does five digits
$query = $db->query( "select place_name,state_name,lat,lng from zip_codes_usa where postal_code = LEFT(?, 5)",[$zip]);

if ($query->count() == 0) {
    printErrorJSONAndDie('Zip Not found :'.$zip);
}

$rec = $query->first();
$ret = ['city'=>$rec->place_name,'state'=>$rec->state_name,'lat'=>$rec->lat,'lng'=>$rec->lng];

printOkJSONAndDie($ret);






