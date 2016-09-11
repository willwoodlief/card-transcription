<?php

require_once 'users/private_init.php';

// Set config
$GLOBALS['config'] = array(
    'mysql'      => array(
        'host'         => getenv('DB_HOST'),
        'username'     => getenv('DB_USERNAME'),
        'password'     => getenv('DB_PASSWORD'),
        'db'           =>  getenv('DB_NAME'),
    ),
    'remember'        => array(
        'cookie_name'   => 'pmqesoxiw318374csb',
        'cookie_expiry' => 604800  //One week, feel free to make it longer
    ),
    'session' => array(
        'session_name' => 'user',
        'token_name' => 'token',
    )
);


require_once 'users/classes/Config.php';
require_once 'users/classes/DB.php';
require_once 'lib/aws/aws-autoloader.php';
require_once 'pages/helpers/pages_helper.php';
require_once 'pages/helpers/mime_type.php';

$db = DB::getInstance();
$settingsQ = $db->query("Select * FROM settings");
$settings = $settingsQ->first();

$web_url =  $settings->website_url;
$n_waiting_to_be_uploaded = $db->query( "select * from ht_waiting p where p.is_uploaded = 0 and upload_result is null order by p.created_at;",[])->count();
print "We are trying to connect to {$web_url} to upload {$n_waiting_to_be_uploaded} cards....";
if (!test_site_connection($settings->website_url)) {
    print "  [ERROR] Connection could not be established\n";
} else {
    print "[OK]\n";
}
upload_local_storage();

print "\n\n";