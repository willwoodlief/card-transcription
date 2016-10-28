<?php
# returns json status of ok or error with message
# anyone can post to this, but unless the images are in the bucket,which can only be added to by authorized,
# the post is ignored

#ideally, this message would be passed by a private messaging system like aws sqs, but
# have to write this assuming no way to set up a cron job or independent task that runs in background

require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'/users/includes/header_json.php';
require_once $abs_us_root.$us_url_root.'lib/aws/aws-autoloader.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';

$ret = $_POST;
/* post contains
 *  $msg = [
         'client_id' => $client_id,
         'profile_id' => $profile_id,
         'front' => $front_key_name,
         'back' => $back_key_name,
         'timestamp' => time(),
         'bucket' => $to_bucket_name,
         'front_width'  => $row->front_width,
         'front_height'  => $row->front_height,
         'back_width'  => $row->back_width,
         'back_height'  => $row->back_height,
         'front_type' => $row->front_file_type,
         'back_type' => $row->back_file_type,
         'uploader_email' => $row->uploader_email,
         'uploader_lname' => $row->uploader_lname,
         'uploader_fname' => $row->uploader_fname,
         'uploaded_at'  => $row->created_at
        ];
 */



$db = DB::getInstance();
$fields=array(
     'client_id' => Input::get('client_id'),
     'profile_id' => Input::get('profile_id'),
     'uploaded_at' => Input::get('uploaded_at'),
     'created_at' => time(),
     'modified_at' => time(),
     'uploader_email' => Input::get('uploader_email'),
     'uploader_lname' => Input::get('uploader_lname'),
     'uploader_fname' => Input::get('uploader_fname'),
     'notes' => Input::get('notes')

);
$what = $db->insert('ht_jobs',$fields);
if (!$what) {
    printErrorJSONAndDie('could not create job: '. $db->error());
}
$jobid = $db->lastId();

// Create an SDK class used to share configuration across clients.
// api key and secret are in environmental variables
$sharedConfig = [
    'region'  => getenv('AWS_REGION'),
    'version' => 'latest'
];

$sdk = new Aws\Sdk($sharedConfig);

// Use an Aws\Sdk class to create the S3Client object.
$s3Client = $sdk->createS3();

# get our bucket for this server (maybe same or different)
$our_bucket = $settings->s3_bucket_name;
$their_bucket = Input::get('bucket');

$front_key_name = Input::get('front');
$front_type = Input::get('front_type');
$front_width = Input::get('front_width');
$front_height = Input::get('front_height');

$back_key_name = Input::get('back');
$back_type = Input::get('back_type');
$back_width = Input::get('back_width');
$back_height = Input::get('back_height');


$efront_key_name = Input::get('efront');
$efront_type = Input::get('efront_type');
$efront_width = Input::get('efront_width');
$efront_height = Input::get('efront_height');

$eback_key_name = Input::get('eback');
$eback_type = Input::get('eback_type');
$eback_width = Input::get('eback_width');
$eback_height = Input::get('eback_height');

$updatetime =  Input::get('uploaded_at');
$uploaded_date_string = date('Ymd',$updatetime);
$clientID = Input::get('client_id');
$profileID = Input::get('profile_id');
//img1234567a_id0268_p02_YYYYMMDD.jpg
$new_front_key_name = "img{$jobid}a_id{$clientID}_p{$profileID}_{$uploaded_date_string}.{$front_type}";
$new_back_key_name = "img{$jobid}b_id{$clientID}_p{$profileID}_{$uploaded_date_string}.{$back_type}";

$enew_front_key_name = "e_img{$jobid}a_id{$clientID}_p{$profileID}_{$uploaded_date_string}.{$efront_type}";
$enew_back_key_name = "e_img{$jobid}b_id{$clientID}_p{$profileID}_{$uploaded_date_string}.{$eback_type}";

try {
    @$s3Client->copyObject(array(
        'Bucket'     => $our_bucket,
        'Key'        => $new_front_key_name,
        'CopySource' => "{$their_bucket}/{$front_key_name}",
    ));
} catch (S3Exception $e) {
    $db->update('ht_jobs', $jobid, ['error_message' => $e->getMessage()]);
    publish_to_sns('could not move front image in bucket: ','page died at post_upload because
     it could not move the image from the bucket. Error message was '.  $e->getMessage());
    printErrorJSONAndDie('could not move front image in bucket: '. $e->getMessage());
}

$front_url = '';
try {
    $front_url = @$s3Client->getObjectUrl($our_bucket, $new_front_key_name);
} catch (S3Exception $e) {
    $db->update('ht_jobs', $jobid, ['error_message' => $e->getMessage()]);
    publish_to_sns('could not get front image url from bucket: ','page died at post_upload because
     it could not get information from the  image from the bucket. Error message was '.  $e->getMessage());
    printErrorJSONAndDie('could not get front image url: '. $e->getMessage());
}

try {
    @$s3Client->copyObject(array(
        'Bucket'     => $our_bucket,
        'Key'        => $new_back_key_name,
        'CopySource' => "{$their_bucket}/{$back_key_name}",
    ));
} catch (S3Exception $e) {
    $db->update('ht_jobs', $jobid, ['error_message' => $e->getMessage()]);
    publish_to_sns('could not move back image in bucket: ','page died at post_upload because
     it could not move the image from the bucket. Error message was '.  $e->getMessage());
    printErrorJSONAndDie('could not move back image in bucket: '. $e->getMessage());
}

$back_url = '';
try {
    $back_url = @$s3Client->getObjectUrl($our_bucket, $new_back_key_name);
} catch (S3Exception $e) {
    $db->update('ht_jobs', $jobid, ['error_message' => $e->getMessage()]);
    publish_to_sns('could not get back image url from bucket: ','page died at post_upload because
     it could not get information from the  image from the bucket. Error message was '.  $e->getMessage());

    printErrorJSONAndDie('could not get back image url: '. $e->getMessage());
}

///////////////////////////////////////////////////////////////////////////////
/////////////////////////// adding edited images to server bucket
///////////////////////////////////////////////////////////////////////////////




try {
    @$s3Client->copyObject(array(
        'Bucket'     => $our_bucket,
        'Key'        => $enew_front_key_name,
        'CopySource' => "{$their_bucket}/{$efront_key_name}",
    ));
} catch (S3Exception $e) {
    $db->update('ht_jobs', $jobid, ['error_message' => $e->getMessage()]);
    publish_to_sns('could not move front image in bucket: ','page died at post_upload because
     it could not move the image from the bucket. Error message was '.  $e->getMessage());
    printErrorJSONAndDie('could not move front image in bucket: '. $e->getMessage());
}

$efront_url = '';
try {
    $efront_url = @$s3Client->getObjectUrl($our_bucket, $enew_front_key_name);
} catch (S3Exception $e) {
    $db->update('ht_jobs', $jobid, ['error_message' => $e->getMessage()]);
    publish_to_sns('could not get front image url from bucket: ','page died at post_upload because
     it could not get information from the  image from the bucket. Error message was '.  $e->getMessage());
    printErrorJSONAndDie('could not get front image url: '. $e->getMessage());
}

try {
    @$s3Client->copyObject(array(
        'Bucket'     => $our_bucket,
        'Key'        => $enew_back_key_name,
        'CopySource' => "{$their_bucket}/{$eback_key_name}",
    ));
} catch (S3Exception $e) {
    $db->update('ht_jobs', $jobid, ['error_message' => $e->getMessage()]);
    publish_to_sns('could not move back image in bucket: ','page died at post_upload because
     it could not move the image from the bucket. Error message was '.  $e->getMessage());
    printErrorJSONAndDie('could not move back image in bucket: '. $e->getMessage());
}

$eback_url = '';
try {
    $eback_url = @$s3Client->getObjectUrl($our_bucket, $enew_back_key_name);
} catch (S3Exception $e) {
    $db->update('ht_jobs', $jobid, ['error_message' => $e->getMessage()]);
    publish_to_sns('could not get back image url from bucket: ','page died at post_upload because
     it could not get information from the  image from the bucket. Error message was '.  $e->getMessage());

    printErrorJSONAndDie('could not get back image url: '. $e->getMessage());
}

//////////////////////////////////////////////////////////



#move each image to have proper name, and make ht_image

$fields=array(
    'ht_job_id' => $jobid,
    'side' => 0,
    'image_type' => $front_type,
    'bucket_name' => $our_bucket,
    'key_name' => $new_front_key_name,
    'image_url' => $front_url,
    'image_height' => $front_height,
    'image_width' => $front_width,
    'created_at' => time(),
    'modified_at' => time()

);
$what = $db->insert('ht_images',$fields);
if (!$what) {
    $db->update('ht_jobs', $jobid, ['error_message' => $db->error()]);
    printErrorJSONAndDie('could not create front image roww: '. $db->error());
}

$fields=array(
    'ht_job_id' => $jobid,
    'side' => 1,
    'image_type' => $back_type,
    'bucket_name' => $our_bucket,
    'key_name' => $new_back_key_name,
    'image_url' => $back_url,
    'image_height' => $back_height,
    'image_width' => $back_width,
    'created_at' => time(),
    'modified_at' => time()

);
$what = $db->insert('ht_images',$fields);
if (!$what) {
    $db->update('ht_jobs', $jobid, ['error_message' => $db->error(),'modified_at'=>time()]);
    printErrorJSONAndDie('could not create back image row : '. $db->error());
}

///////////////////////////////////////////////////
//////////////////// Add edited images to image rows
///////////////////////////////////////////////////
$fields=array(
    'ht_job_id' => $jobid,
    'side' => 0,
    'is_edited'=>1,
    'image_type' => $efront_type,
    'bucket_name' => $our_bucket,
    'key_name' => $enew_front_key_name,
    'image_url' => $efront_url,
    'image_height' => $efront_height,
    'image_width' => $efront_width,
    'created_at' => time(),
    'modified_at' => time()

);
$what = $db->insert('ht_images',$fields);
if (!$what) {
    $db->update('ht_jobs', $jobid, ['error_message' => $db->error()]);
    printErrorJSONAndDie('could not create edit front image row: '. $db->error());
}

$fields=array(
    'ht_job_id' => $jobid,
    'side' => 1,
    'is_edited'=>1,
    'image_type' => $eback_type,
    'bucket_name' => $our_bucket,
    'key_name' => $enew_back_key_name,
    'image_url' => $eback_url,
    'image_height' => $eback_height,
    'image_width' => $eback_width,
    'created_at' => time(),
    'modified_at' => time()

);
$what = $db->insert('ht_images',$fields);
if (!$what) {
    $db->update('ht_jobs', $jobid, ['error_message' => $db->error(),'modified_at'=>time()]);
    printErrorJSONAndDie('could not create edit back image row: '. $db->error());
}
///////////////////////////////////////////////////////////

//notifications can happen when they are logged in so this is all this call does
// if got here then signal this in the job
$what = $db->update('ht_jobs', $jobid, ['is_initialized' => 1]);
if (!$what) {
    $db->update('ht_jobs', $jobid, ['error_message' => $db->error(),'modified_at'=>time()]);
    printErrorJSONAndDie('could not toggle initialized flag for jobs: '. $db->error());
}

//add in tags

$ret['message']= "started job {$jobid}";
$tags = json_decode(Input::get('tags'),true);
add_tags_to_job($tags);
printOkJSONAndDie($ret);