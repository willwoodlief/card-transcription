<?php
//die(var_dump($_REQUEST));
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'users/helpers/helpers.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';

$db = DB::getInstance();
$settingsQ = $db->query("Select * FROM settings");
$settings = $settingsQ->first();
if ($settings->site_offline==1){
    die("The site is currently offline.");
}
if (!securePage($_SERVER['PHP_SELF'])){die(); }

#get the job id, if not job id here then die
$job_id_string =  Input::get('jobid');
if (!$job_id_string) {
    die('Need job id');
}

$jobid =  intval(Input::get('jobid'));
$info_hash = get_jobs($jobid);
if (empty($info_hash)) {
    die('Cannot find Job ID');
}



?>

<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="<?=$us_url_root ?>favicon.ico" />

    <!-- Bootstrap Core CSS -->
    <!-- AKA Primary CSS -->
    <link href="<?=$us_url_root?><?=str_replace('../','',$settings->us_css1);?>" rel="stylesheet">

    <!-- Template CSS -->
    <!-- AKA Secondary CSS -->
    <link href="<?=$us_url_root?><?=str_replace('../','',$settings->us_css2);?>" rel="stylesheet">

    <!-- Your Custom CSS Goes Here!-->
    <link href="<?=$us_url_root?><?=str_replace('../','',$settings->us_css3);?>" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="<?=$us_url_root?>users/fonts/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <style>
        body {
            margin-top: 0;
        }
    </style>