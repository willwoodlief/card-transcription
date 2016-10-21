<?php

#returns jobs based on what filters are set in the get statement, this is a protected page that all roles can use
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'/users/includes/header_json.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';

if (!securePage($_SERVER['PHP_SELF'])){die();}
if ($settings->site_offline==1){printErrorJSONAndDie("The site is currently offline.");}


$web_url =  $settings->website_url;
$n_waiting_to_be_uploaded = $db->query( "select * from ht_waiting p where p.is_uploaded = 0 and upload_result is null order by p.created_at;",[])->count();

if (!test_site_connection($settings->website_url)) {
    printErrorJSONAndDie( "  [ERROR] Connection could not be established");
}
$whats = upload_local_storage(null,false);

printOkJSONAndDie(['url'=>$web_url,'total'=>$n_waiting_to_be_uploaded,'results'=>$whats]);




