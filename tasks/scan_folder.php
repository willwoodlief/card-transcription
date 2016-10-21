<?php
$isRunningFromBrowser = !isset($GLOBALS['argv']);
if ($isRunningFromBrowser) {
    die('Cannot run this particular script from the web');
}
$localroot =   realpath( dirname( __FILE__ ) );
require_once $localroot.'/../users/private_init.php';
require_once $localroot.'/../users/init.cli.php';

require_once $localroot.'/../users/classes/User.php';
require_once $localroot.'/../lib/file_watching.php';
require_once $localroot.'/../pages/helpers/pages_helper.php';
require_once $localroot.'/../lib/watching_configs.php';





$db = DB::getInstance();
$settingsQ = $db->query("Select * FROM settings");
$settings = $settingsQ->first();
$folder_to_watch = $settings->folder_watch;
$filter_rule = $settings->folder_watch_filter_rgx;
$group_rules = $settings->folder_watch_group_rgx;

$side_a = $settings->folder_watch_side_a_match;


$wconfigs = new WatchingConfigs($settings->user_profile_config);
$configs = $wconfigs->get_configs();
$watcher = new FileWatching($folder_to_watch,$filter_rule,$group_rules,$side_a);

$watcher->iterate_pairs('pass_along_pairs');

function pass_along_pairs($side_a,$side_b) {
    global $configs;
    try {
        $tmp_file_path = realpath(__DIR__ . '/../tmp/local_uploads');
        #print $tmp_file_path . PHP_EOL;
        $a_extension = strtolower(pathinfo($side_a, PATHINFO_EXTENSION));
        $b_extension = strtolower(pathinfo($side_b, PATHINFO_EXTENSION));
        $user = new User('admin');
        $nid = add_waiting($configs->user, $configs->profile, $side_a, $side_b, $a_extension, $b_extension, $user, $tmp_file_path);
        $b_did_upload = upload_local_storage($nid);
        print "Found two images:\nSide a: $side_a\nSide b: $side_b\nUser $configs->user \nProfile $configs->profile\n";
        if ($b_did_upload) {
            print '...Uploaded...';
        }
        return ['ok'=>true,'nid'=>$nid,'uploaded_immediately'=>$b_did_upload];
    } catch (Exception $e) {
        print "Exception, trace is in db ->". $e->getMessage();
        return ['ok'=>false,'message'=>$e->getMessage(),'trace'=>$e->getTrace()];
    }
}






