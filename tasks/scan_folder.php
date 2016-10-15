<?php
$isRunningFromBrowser = !isset($GLOBALS['argv']);
if ($isRunningFromBrowser) {
    die('Cannot run this particular script from the web');
}
$localroot =   realpath( dirname( __FILE__ ) );
require_once $localroot.'/../users/private_init.php';
require_once $localroot.'/../users/init.cli.php';

require_once $localroot.'/../lib/file_watching.php';
require_once $localroot.'/../pages/helpers/pages_helper.php';




$db = DB::getInstance();
$settingsQ = $db->query("Select * FROM settings");
$settings = $settingsQ->first();
$folder_to_watch = $settings->folder_watch;
$filter_rule = $settings->folder_watch_filter_rgx;
$group_rules = $settings->folder_watch_group_rgx;

$side_a = $settings->folder_watch_side_a_match;



$watcher = new FileWatching($folder_to_watch,$filter_rule,$group_rules,$side_a);

$watcher->iterate_pairs('pass_along_pairs');

function pass_along_pairs($side_a,$side_b) {
    print "side a: $side_a\n side b: $side_b";
    return true;
}






