<?php
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'lib/aws/aws-autoloader.php';
require_once $abs_us_root.$us_url_root.'users/helpers/helpers.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';

$db = DB::getInstance();
$settingsQ = $db->query("Select * FROM settings");
$settings = $settingsQ->first();
if ($settings->site_offline==1){
    printErrorJSONAndDie("The site is currently offline.");
}
if (!securePage($_SERVER['PHP_SELF'])){printErrorJSONAndDie('need to login'); }

$imgid = intval(Input::get('imgid'));
if (!$imgid) {
    printErrorJSONAndDie('Need valid image id');
}

$base64 = Input::get('base64');
if (!$base64) {
    printErrorJSONAndDie('need base 64 stuff');
}

$img_info = $db->query("Select * FROM ht_images where id = ?",[$imgid]);
if (empty($img_info)) {
    printErrorJSONAndDie('cannot find image');
}
$img = $img_info->first();
print "the first image is {$img->id}\n";

if ($img->is_edited == 0) {

//logic, if we are sent an original image, see if there is an edited image for the same side, if so
// this will be our new image
    $img_info2 = $db->query("SELECT * FROM ht_images WHERE ht_job_id = ? AND side = ? AND is_edited = ?",
        [$img->ht_job_id, $img->side, 1]);

    if ($db->count() > 0) {
        //replace it
        $img = $img_info2->first();

    } else {

    }

}
print "the second image is {$img->id}\n";

$the_job = get_jobs($img->ht_job_id);


if (empty($the_job)) {
    printErrorJSONAndDie('Need valid job id, this number id not correspond to a job');
}
$the_job = json_decode(json_encode($the_job))[0];




// Create an SDK class used to share configuration across clients.
// api key and secret are in environmental variables
$sharedConfig = [
    'region'  => getenv('AWS_REGION'),
    'version' => 'latest'
];

$sdk = new Aws\Sdk($sharedConfig);

// Use an Aws\Sdk class to create the S3Client object.
$s3Client = $sdk->createS3();


//logic, if we are passed the original image then we create a new image and save it to s3
// if we are passed the edited image, then we replace the image in s3 under same name
if ($img->is_edited == 1) {
    // delete old image from aws
    $bucket_name = $img->bucket_name;
    $key_name = $img->key_name;


    //delete object

    try {
        $result = $s3Client->deleteObject(array(
            'Bucket' => $img->bucket_name,
            'Key'    => $img->key_name
        ));

    } catch (S3Exception $e) {
        printErrorJSONAndDie('could not delete older edited  image from bucket: '. $e->getMessage());
    }


}
$side_letter = 'a';
if ($img->side == 1) {
    $side_letter = 'b';
}

$date_string = date('Ymd');

$key =  "e_img{$img->ht_job_id}{$side_letter}_id{$the_job->job->client_id}_p{$the_job->job->profile_id}_{$date_string}.png";

//write the image to a temp file, get the image dimentions, and then use it in the bucket
$fSetup = tmpfile();
fwrite($fSetup,base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64)));
fseek($fSetup,0);
$metaDatas = stream_get_meta_data($fSetup);
$tmpFilename = $metaDatas['uri'];
list($front_width, $front_height) = getimagesize($tmpFilename);
//add object
try {
    $s3Client->putObject(array(
        'Bucket' => $settings->s3_bucket_name,
        'Key' => $key,
        'SourceFile'   => $tmpFilename,
        'ContentType' => 'image/png',
        'StorageClass' => 'STANDARD'
    ));
} catch (S3Exception $e) {
    printErrorJSONAndDie('could not add  image to bucket: '. $e->getMessage());
}

//get the image url, since we want to be flexable in key schemes, always get the new image url
try {
    $edit_url = @$s3Client->getObjectUrl($settings->s3_bucket_name, $key);
} catch (S3Exception $e) {
    $db->update('ht_jobs', $jobid, ['error_message' => $e->getMessage()]);
    printErrorJSONAndDie('could not get  image url: '. $e->getMessage());
}


//add image to the database, or update it
if ($img->is_edited == 1) {
    //update the img with the modified at
    print "updating image is {$img->id} = {$imgid}\n";
    $db->update('ht_images', $img->id, ['modified_at'=> time(),'image_height' => $front_height,'image_width'=>$front_width,'image_type'=>'png']);
    $new_img_id = $img->id;
}else {
    //create new img record


    $fields=array(
        'ht_job_id' => $img->ht_job_id,
        'side' => $img->side,
        'is_edited'=>1,
        'image_type' => 'png',
        'bucket_name' => $settings->s3_bucket_name,
        'key_name' => $key,
        'image_url' => $edit_url,
        'image_height' => $front_height,
        'image_width' => $front_width,
        'created_at' => time(),
        'modified_at' => time()

    );
    //var_dump($fields);
    $what = $db->insert('ht_images',$fields);
    if (!$what) {
        printErrorJSONAndDie($db->error_info());
    }

    $new_img_id = $db->lastId();

}

printOkJSONAndDie(['imgid'=>$new_img_id,'side'=>$img->side]);

