<?php
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'lib/aws/aws-autoloader.php';
require_once $abs_us_root.$us_url_root.'lib/SimpleImage/src/abeautifulsite/SimpleImage.php';
require_once $abs_us_root.$us_url_root.'users/helpers/helpers.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/mime_type.php';

$db = DB::getInstance();
$settingsQ = $db->query("Select * FROM settings");
$settings = $settingsQ->first();
if ($settings->site_offline==1){
    die("The site is currently offline.");
}
if (!securePage($_SERVER['PHP_SELF'])){die(); }

$jobid = intval(Input::get('jobid'));
if (!$jobid) {
    die('Need valid job id');
}

if (false === Input::get('side')) {
    die('Need valid side (1, or 0)');
}
$side_int = intval(Input::get('side'));
$the_job = get_jobs($jobid);

$force_origonal = false;
if (intval(Input::get('force_original')) ) {
    $force_origonal = true;
}


if (empty($the_job)) {
    die('Need valid job id, this number id not correspond to a job');
}
$the_job = json_decode(json_encode($the_job))[0];

//print_nice($the_job);
if ($side_int) {
    $image_url = $the_job->images->edit_side_b->url;
    $image_id = $the_job->images->edit_side_b->id;
    $image_width =  $the_job->images->edit_side_b->width;
    $image_height =  $the_job->images->edit_side_b->height;

    if ( $force_origonal) {
        $ret = restart_edit($jobid,$side_int);
        $image_width =  $ret['image_width'];
        $image_height =  $ret['image_height'];
    }

} else {
    $image_url = $the_job->images->edit_side_a->url;
    $image_id = $the_job->images->edit_side_a->id;
    $image_width =  $the_job->images->edit_side_a->width;
    $image_height =  $the_job->images->edit_side_a->height;

    if ( $force_origonal) {
        $ret = restart_edit($jobid,$side_int);
        $image_width =  $ret['image_width'];
        $image_height =  $ret['image_height'];
    }
}

#get the bucket and key for the image
$db = DB::getInstance();
$imagefo = $db->query("Select bucket_name,key_name FROM ht_images where id = ?",[$image_id]);
$imagefof = $imagefo->first();
$bucket = $imagefof->bucket_name;
$keyname = $imagefof->key_name;

// Create an SDK class used to share configuration across clients.
// api key and secret are in environmental variables
$sharedConfig = [
    'region'  => getenv('AWS_REGION'),
    'version' => 'latest'
];

$sdk = new Aws\Sdk($sharedConfig);

// Use an Aws\Sdk class to create the S3Client object.
$s3Client = $sdk->createS3();

try {
    $result = $s3Client->getObject(array(
        'Bucket' => $bucket,
        'Key'    => $keyname
    ));
} catch (S3Exception $e) {
    publish_to_sns('could not get  image from bucket','page died at edit_job_image because
     it could not get the image from the bucket. Error message was '.  $e->getMessage());
    die('could not get  image from bucket: '. $e->getMessage());
}

$mime_type = $result['ContentType'];
$body = $result['Body'];

//download the image using aws to a temp file,$tmp_path
$tmp_path = '';
// http://stackoverflow.com/questions/3967515/how-to-convert-image-to-base64-encoding
function getDataURI($body,$mimetype) {
    // $finfo = new finfo(FILEINFO_MIME_TYPE);
   // $type = $finfo->file($tmp_img_path);
    return 'data:'.$mimetype.';base64,'.base64_encode($body);
}

$base64 = getDataURI($body,$mime_type);

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
    <link rel="stylesheet" href="../users/js/plugins/darkroomjs/build/darkroom.css">

    <title>Edit Image</title>
    <style>
      #the-image {

      }
    </style>
</head>
<body style="background-color: floralwhite">
<div style="height:45px;width: 100% "></div>
<img id="the-image" height="<?= $image_height ?>" width="<?= $image_width ?>"
        src="<?= $base64 ?>" data-job-id="<?= $image_id ?>">


<script src="../users/js/fabric.min.js"></script>
<script src="../users/js/plugins/darkroomjs/build/darkroom.js"></script>
<!-- jQuery -->
<script src="<?=$us_url_root?>users/js/jquery.js"></script>

<script>
    new Darkroom('#the-image', {


        // Plugins options
        plugins: {
            crop: {
                ratio: 7.0/4.0
            },
            save:{
                callback: function(){
                    //this.darkroom.selfDestroy();
                    editorSaveCallback(this.darkroom.sourceCanvas.toDataURL());
                }
            }
        },

        // Post initialization method
        initialize: function() {
            // Active crop selection
           // this.plugins['crop'].requireFocus();

            // Add custom listener
            this.addEventListener('core:transformation', function() { /* ... */ });
        }

    });

    function editorSaveCallback(base64_data) {
        //console.log(base64_data);
        $.post( "save_image.php", { imgid: <?= $image_id ?>, base64: base64_data },null,"json" )
            .done(
                function( data ) {
                    console.log(data.status);
                    if (data.status != 'ok') {
                        console.log(data.message);
                    }
                }
            );
    }

    $(document).click(function() {
        window.parent.get_iframe_clicks(window.frameElement.id);
    });
</script>


</body>
</html>
