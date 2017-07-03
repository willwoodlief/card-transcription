<?php
//will upload all unless is set
$real =   realpath( dirname( __FILE__ ) );
require_once $real.'/../../lib/ForceUTF8/Encoding.php';
require_once $real.'/../../lib/aws/aws-autoloader.php';
require_once $real.'/../../pages/helpers/mime_type.php';
require_once $real.'/../../lib/SimpleImage/src/abeautifulsite/SimpleImage.php';


function publish_to_sns($title,$message) {
    global  $settings;

    # get settings if not set already
    if (! isset($settings)) {
        $db = DB::getInstance();
        $settingsQ = $db->query("Select * FROM settings");
        $settings = $settingsQ->first();
    }

    if (!$settings->sns_arn || empty($settings->sns_arn) ) {
        return;
    }


    $sharedConfig = [
        'region'  => getenv('AWS_REGION'),
        'version' => 'latest'
    ];

    // Create an SDK class used to share configuration across clients.
    $sdk = new Aws\Sdk($sharedConfig);

    $client = $sdk->createSns();

    $message_to_send =  to_utf8($message);
    if (is_array($message)) {
        $message_to_send = json_encode($message_to_send);
    }

    $payload = array(
        'TopicArn' => $settings->sns_arn,
        'Message' => $message_to_send,
        'Subject' => to_utf8($title),
        'MessageStructure' => 'string',
    );

    try {
        $client->publish( $payload );
    } catch ( Exception $e ) {
        $email = Config::get('contact/email');
        email($email,"could not publish: $title" ,$message);
    }

}

function to_utf8($what) {
    return ForceUTF8\Encoding::toUTF8($what);
}

function upload_local_storage($idOnly=null,$b_print=true) {

    $ret = [];
    $db = DB::getInstance();
    $settingsQ = $db->query("Select * FROM settings");
    $settings = $settingsQ->first();
    if (!test_site_connection($settings->website_url)) {
        return false;
    }

    $sharedConfig = [
        'region'  => getenv('AWS_REGION'),
        'version' => 'latest'
    ];

// Create an SDK class used to share configuration across clients.
    $sdk = new Aws\Sdk($sharedConfig);

// Use an Aws\Sdk class to create the S3Client object.
    $s3Client = $sdk->createS3();

    if ($idOnly) {
        $query = $db->query( "select * from ht_waiting p where p.id = ? and p.is_uploaded = 0;",[$idOnly]);

        if ($query->count() >0) {

            $rec = $query->results()[0];
            upload_from_waiting_row($rec,$settings->s3_bucket_name,$s3Client,$settings->website_url);
        }

    } else {
        $query = $db->query( "select * from ht_waiting p where  p.is_uploaded = 0 and upload_result is null order by p.created_at;",[]);
        $results = $query->results();
        foreach ($results as $rec) {
            if (!test_site_connection($settings->website_url)) {return;}

            if ($b_print) {
                print "Uploading record id " . $rec->id . ' [Client ID ' . $rec->client_id . '] ' . ' [Profile ID ' . $rec->profile_id . '] ' . "\n";
            }

            $whatans = upload_from_waiting_row($rec,$settings->s3_bucket_name,$s3Client,$settings->website_url);
            if ($b_print) {
                if ($whatans !== true) {
                    print '[Error] ' . $whatans . "\n";
                } else {
                    print '[OK]' . "\n";
                }
            }

            if ($whatans !== true) {
                $node = ['status'=>'error','message'=>to_utf8($whatans)] ;
            } else {
                $node = ['status'=>'ok','message'=>to_utf8('ok')] ;
            }
            array_push($ret,$node);
        }

    }

    return $ret;

}



//row is ht_waiting
function upload_from_waiting_row($row,$to_bucket_name,$s3Client,$website_url) {

    $db = DB::getInstance();
    $status_to_post = '';
 try {
     #we are going to send the two pictures to the holding folder in the s3 bucket, after adding in a guid to prevent collisions between diferent machines
     $guid = getGUID();

     $back_card_path = $row->back_path;
     $back_file_ext = $row->back_file_type;
     $front_card_path = $row->front_path;
     $front_file_ext = $row->front_file_type;
     $waiting_id = $row->id;
     $client_id = $row->client_id;
     $profile_id = $row->profile_id;
     $date_string = date("Ymd_H:i");


     $eback_card_path = $row->eback_path;
     $eback_file_ext = $row->eback_file_type;
     $efront_card_path = $row->efront_path;
     $efront_file_ext = $row->efront_file_type;


     #test to make sure the file paths are there
     if (!is_readable($front_card_path)) {
         $status_to_post.= "cannot get {$front_card_path}";
         return $status_to_post;
     }

     if (!is_readable($back_card_path)) {
         $status_to_post.= "cannot get {$back_card_path}";
         return  $status_to_post;
     }

     //img1234567a_id0268_p02_YYYYMMDD.jpg is example of final name,but these are temporary and from different comptuters
     $front_key_name = "preprocessing/preprocess-{$waiting_id}a-id{$client_id}-p{$profile_id}-{$date_string}-guid={$guid}.{$front_file_ext}";
     $front_mime_type = mime_type($front_card_path);

     $back_key_name = "preprocessing/preprocess-{$waiting_id}b-id{$client_id}-p{$profile_id}-{$date_string}-guid={$guid}.{$back_file_ext}";
     $back_mime_type = mime_type($back_card_path);


     $efront_key_name = "preprocessing/preprocess-e-{$waiting_id}a-id{$client_id}-p{$profile_id}-{$date_string}-guid={$guid}.{$efront_file_ext}";
     $efront_mime_type  = mime_type($efront_card_path);


     $eback_key_name = "preprocessing/preprocess-e-{$waiting_id}b-id{$client_id}-p{$profile_id}-{$date_string}-guid={$guid}.{$eback_file_ext}";

     $eback_mime_type = mime_type($eback_card_path);

     $status_to_post = '';
     try {
         $result = @$s3Client->putObject(array(
             'Bucket' => $to_bucket_name,
             'Key' => $front_key_name,
             'SourceFile' => $front_card_path,
             'ContentType' => $front_mime_type,
             'ACL' => 'public-read',
         //    'StorageClass' => 'REDUCED_REDUNDANCY',
             'Metadata' => array(
                 'client_id' => $client_id,
                 'profile_id' => $profile_id
             )
         ));

         // Print the URL to the object.
         $status_to_post .= ', '.$result['ObjectURL'] ;
     } catch (S3Exception $e) {
         publish_to_sns('could not add image to  bucket: ','page died at upload_from_waiting_row because
     it could not put image to bucket. Error message was '.  $e->getMessage());
         $status_to_post .= ', '.$e->getMessage();
         return  $status_to_post; //writes status to row in finally block
     }


     //echo $result['ObjectURL'];

     try {
         $result = @$s3Client->putObject(array(
             'Bucket' => $to_bucket_name,
             'Key' => $back_key_name,
             'SourceFile' => $back_card_path,
             'ContentType' => $back_mime_type,
             'ACL' => 'public-read',
             //'StorageClass' => 'REDUCED_REDUNDANCY',
             'Metadata' => array(
                 'client_id' => $client_id,
                 'profile_id' => $profile_id
             )
         ));
         // Print the URL to the object.
         $status_to_post .= ', '.$result['ObjectURL'] ;
     } catch (S3Exception $e) {
         publish_to_sns('could not add image to  bucket: ','page died at upload_from_waiting_row because
     it could not put image to bucket. Error message was '.  $e->getMessage());
         $status_to_post .= ', '.$e->getMessage();
         return  $status_to_post; //writes status to row in finally block
     }

     // add in the edit versions of the images

     try {
         $result = @$s3Client->putObject(array(
             'Bucket' => $to_bucket_name,
             'Key' => $eback_key_name,
             'SourceFile' => $eback_card_path,
             'ContentType' => $eback_mime_type,
             'ACL' => 'public-read',
             //'StorageClass' => 'REDUCED_REDUNDANCY',
             'Metadata' => array(
                 'client_id' => $client_id,
                 'profile_id' => $profile_id
             )
         ));
         // Print the URL to the object.
         $status_to_post .= ', '.$result['ObjectURL'] ;
     } catch (S3Exception $e) {
         publish_to_sns('could not add edited back image to  bucket: ','page died at upload_from_waiting_row because
     it could not put image to bucket. Error message was '.  $e->getMessage());
         $status_to_post .= ', '.$e->getMessage();
         return  $status_to_post; //writes status to row in finally block
     }


     try {
         $result = @$s3Client->putObject(array(
             'Bucket' => $to_bucket_name,
             'Key' => $efront_key_name,
             'SourceFile' => $efront_card_path,
             'ContentType' => $efront_mime_type,
             'ACL' => 'public-read',
             //    'StorageClass' => 'REDUCED_REDUNDANCY',
             'Metadata' => array(
                 'client_id' => $client_id,
                 'profile_id' => $profile_id
             )
         ));

         // Print the URL to the object.
         $status_to_post .= ', '.$result['ObjectURL'] ;
     } catch (S3Exception $e) {
         publish_to_sns('could not add fronted edited image to  bucket: ','page died at upload_from_waiting_row because
     it could not put image to bucket. Error message was '.  $e->getMessage());
         $status_to_post .= ', '.$e->getMessage();
         return  $status_to_post; //writes status to row in finally block
     }


     //echo $result['ObjectURL'];.

     #now send message to website
     $url_to_use = $website_url . '/pages/post_upload.php';
     $msg = [
         'client_id' => $client_id,
         'profile_id' => $profile_id,


         'front' => $front_key_name,
         'front_type' => $row->front_file_type,
         'front_width'  => $row->front_width,
         'front_height'  => $row->front_height,

         'back' => $back_key_name,
         'back_type' => $row->back_file_type,
         'back_width'  => $row->back_width,
         'back_height'  => $row->back_height,


         'efront' => $efront_key_name,
         'efront_type' => $row->efront_file_type,
         'efront_width'  => $row->efront_width,
         'efront_height'  => $row->efront_height,

         'eback' => $eback_key_name,
         'eback_type' => $row->eback_file_type,
         'eback_width'  => $row->eback_width,
         'eback_height'  => $row->eback_height,



         'timestamp' => time(),
         'bucket' => $to_bucket_name,
         'uploader_email' => $row->uploader_email,
         'uploader_lname' => $row->uploader_lname,
         'uploader_fname' => $row->uploader_fname,
         'uploaded_at'  => $row->created_at,
         'notes' => $row->notes,
         'tags' =>json_decode($row->tags_json,true)
        ];
     $what = rest_helper($url_to_use, $params = $msg, $verb = 'POST', $format = 'json');
     if ($what->status == 'ok') {
         //update the $row
         $db->update('ht_waiting', $row->id, ['is_uploaded' => 1, 'uploaded_at' => time()]);
         $status_to_post .= ', '.$what->message;
         return true;
     } else {
         $status_to_post .= ', ERROR From Server:'.$what->message;
         return  $status_to_post;
     }
 }
 finally {
     $db->update('ht_waiting', $row->id, ['upload_result' => $status_to_post, 'modified_at' => time()]);
 }



}

function restart_edit($jobid,$side) {
    //get the original image bucket and key for that side, resise it and overwrite the edited image

    $sideT = 0;
    $side_letter = 'a';
    if ($side == 1 || (strcasecmp($side, 'b') == 0) ) {
        $sideT = 1;
        $side_letter = 'b';
    }

    $db = DB::getInstance();

    $query = $db->query("select id,client_id,profile_id
                          from ht_jobs where id = ? ",[$jobid] );

    if ($db->count() == 0) {
        throw  new Exception("Could not find job in database to repleace job for job of $jobid and side of $sideT [$side] and not edited");
    }
    $jobQ = $query->first();
    $client_id = $jobQ->client_id;
    $profile_id = $jobQ->profile_id;




    $query = $db->query("select bucket_name,key_name,image_height,image_width ,image_type
                          from ht_images where ht_job_id = ? AND is_edited =0 AND side = ?",[$jobid,$sideT] );

    if ($db->count() == 0) {
        throw  new Exception("Could not find image in database for job of $jobid and side of $sideT [$side] and not edited");
    }
    $img = $query->first();
    $bucket_from = $img->bucket_name;
    $key_from = $img->key_name;


    $query = $db->query("select id, bucket_name,key_name
                          from ht_images where ht_job_id = ? AND is_edited =1 AND side = ?",[$jobid,$sideT] );

    if ($db->count() == 0) {
        throw  new Exception("Could not find image in database for job of $jobid and side of $sideT [$side] and edited");
    }
    $img = $query->first();
    $ht_image_id = $img->id;
    $bucket_to = $img->bucket_name;
    $key_to = $img->key_name;

    #download the original image to here so we can resize it

    $sharedConfig = [
        'region'  => getenv('AWS_REGION'),
        'version' => 'latest'
    ];

// Create an SDK class used to share configuration across clients.
    $sdk = new Aws\Sdk($sharedConfig);

// Use an Aws\Sdk class to create the S3Client object.
    $s3Client = $sdk->createS3();

    // Save object to a temp file.
    $file = tmpfile();
    $path = stream_get_meta_data($file)['uri']; // eg: /tmp/phpFx0513a

    try {
         $s3Client->getObject(array(
            'Bucket' => $bucket_from,
            'Key'    => $key_from,
            'SaveAs' => $path
        ));

    } catch (S3Exception $e) {
        publish_to_sns('could not download image from  bucket: ','in restart_edit an exception was thrown because it could not download an image.
         Error message was '.  $e->getMessage());
        throw $e;
    }

    #now change the image size of the original downloaded copy

    try {
        $imgD = new abeautifulsite\SimpleImage($path);
        $imgD->fit_to_width(600)->save();
        $info = getimagesize($path);
        $ewidth = $info[0];
        $eheight = $info[1];
        $mime_type = $info['mime'];
        $eType =  preg_replace('/^image\//', '', $info['mime']);


        /*
         * array(
//      width => 320,
//      height => 200,
//      orientation => ['portrait', 'landscape', 'square'],
//      exif => array(...),
//      mime => ['image/jpeg', 'image/gif', 'image/png'],
//      format => ['jpeg', 'gif', 'png']
//  )
         * */

    } catch(Exception $e) {

        publish_to_sns('could not resize image',
            "the image could not be resized after it was downloaded to the server, in restart edit ". $e->getMessage());
        throw new Exception ('Error resizing image when replacing edited image: ' . $e->getMessage());
    }


    #delete older key



    try {
        @$s3Client->deleteObject(array(
            'Bucket' => $bucket_to,
            'Key'    => $key_to
        ));

    } catch (S3Exception $e) {
        publish_to_sns('could not deleted older edit image from  bucket: ','could not replace edited image because
     it could not delete the older image from bucket. Error message was '.  $e->getMessage());

        throw new Exception('Error replacing edited image on bucket: ' . $e->getMessage());
    }


    #recaluclate key, and get the new url

    $uploaded_date_string = date('Ymd',time());
    $new_key_name = "e_img{$jobid}{$side_letter}_id{$client_id}_p{$profile_id}_{$uploaded_date_string}.{$eType}";

    #upload to bucket to replace other edited image


    try {
         @$s3Client->putObject(array(
            'Bucket' => $bucket_to,
            'Key' => $new_key_name,
            'SourceFile' => $path,
            'ContentType' => $mime_type,
            'ACL' => 'public-read',
            //    'StorageClass' => 'REDUCED_REDUNDANCY',
            'Metadata' => array(
                'job_id' => $jobid,
                'client_id' => $client_id,
                'profile_id' => $profile_id
            )
        ));

    } catch (S3Exception $e) {
        publish_to_sns('could not add image to  bucket: ','could not replace edited image because
     it could not put image to bucket. Error message was '.  $e->getMessage());

        throw new Exception('Error replacing edited image on bucket: ' . $e->getMessage());
    }

    $edit_url = null;
//get the image url, since we want to be flexable in key schemes, always get the new image url
    try {
        $edit_url = @$s3Client->getObjectUrl($bucket_to, $new_key_name);
    } catch (S3Exception $e) {
        $db->update('ht_jobs', $jobid, ['error_message' => $e->getMessage()]);
        publish_to_sns('could not get image url from bucket: ','page died at save_image because
     it could get image url from bucket. Error message was '.  $e->getMessage());
        printErrorJSONAndDie('could not get  image url: '. $e->getMessage());

    }

    #update the edited image with the new width and height
    $updated_dimentions = [
        'image_width' =>$ewidth,
        'image_height' =>$eheight,
        'modified_at'=>time(),
        'image_url'=>$edit_url,
        'image_type'=>$eType,
        'key_name' => $new_key_name,
    ];



    $db->update('ht_images',$ht_image_id,$updated_dimentions);


    return $updated_dimentions;


}

function add_waiting_from_bucket($client_id,$profile_id,$key_image_front,$key_image_back,
                                 $front_img_type,$back_img_type,$user,$bucket,
                                 $uploader_string,$tags=[],$notes='') {
    global $abs_us_root,$us_url_root;
    //do download to temp file for each side then pass to add_waiting
    //  'SaveAs' => $filepath
    try {

    // Create an SDK class used to share configuration across clients.
    // api key and secret are in environmental variables
        $sharedConfig = [
            'region' => getenv('AWS_REGION'),
            'version' => 'latest'
        ];

        $sdk = new Aws\Sdk($sharedConfig);

    // Use an Aws\Sdk class to create the S3Client object.
        $s3Client = $sdk->createS3();

        try {
            $tmpfname_a = tempnam("/tmp", "side_a");
            $result = $s3Client->getObject(array(
                'Bucket' => $bucket,
                'Key' => $key_image_front,
                'SaveAs' => $tmpfname_a
            ));
        } catch (S3Exception $e) {
            publish_to_sns('could not get A image from bucket', 'page died at add_waiting_from_bucket because
         it could not get the image from the bucket. Error message was ' . $e->getMessage());
            die('could not get  image from bucket: ' . $e->getMessage());
        } finally {

        }


        try {
            $tmpfname_b = tempnam("/tmp", "side_b");
            $result = $s3Client->getObject(array(
                'Bucket' => $bucket,
                'Key' => $key_image_back,
                'SaveAs' => $tmpfname_b
            ));
        } catch (S3Exception $e) {
            publish_to_sns('could not get B image from bucket', 'page died at add_waiting_from_bucket because
         it could not get the image from the bucket. Error message was ' . $e->getMessage());
            die('could not get  image from bucket: ' . $e->getMessage());
        } finally {

        }

        $tmp_file_path = $abs_us_root  .$us_url_root . 'tmp/local_uploads';
        //print "!the uploaded file folder is at $tmp_file_path";

        return add_waiting($client_id,$profile_id,$tmpfname_a,$tmpfname_b,
            $front_img_type,$back_img_type,$user,$tmp_file_path,
            $uploader_string,$tags,$notes);

    }  finally {
        if (isset($tmpfname_a)) unlink($tmpfname_a);
        if (isset($tmpfname_b)) unlink($tmpfname_b);

    }
}

function add_waiting($client_id,$profile_id,$tmppath_image_front,$tmppath_image_back,
                     $front_img_type,$back_img_type,$user,$upload_folder,
                     $uploader_string=null,$tags=[],$notes='') {

    // $extension = strtolower(pathinfo('/home/will/Desktop/img0.png', PATHINFO_EXTENSION));

   // insert new record
    $db = DB::getInstance();
    $fields=array(
        'user_id' => $user->data()->id,
        'uploader_email' => $uploader_string || $user->data()->email,
        'uploader_lname' => $user->data()->fname,
        'uploader_fname' => $user->data()->fname,
        'client_id'=> $client_id,
        'profile_id'=> $profile_id,
        'tags_json' => json_encode($tags),
        'notes' => $notes,
        'created_at'=> time()
    );
    $db->insert('ht_waiting',$fields);
    $theNewId=$db->lastId();




    $folder_part = get_string_filepath_from_id($theNewId);
    $local_folder = $upload_folder.$folder_part;
    $front_name = $client_id.'_'.$theNewId.'_'.'A'.'.'.$front_img_type;
    $back_name = $client_id.'_'.$theNewId.'_'.'B'.'.'.$back_img_type;

    $front_path = $local_folder . '/' . $front_name;
    $back_path = $local_folder . '/' . $back_name;

    $efront_path = $local_folder . '/e_' . $front_name;
    $eback_path = $local_folder . '/e_' . $back_name;



    mkdir_r($local_folder,0777);
    copy($tmppath_image_front,$front_path);
    chmod($front_path,0666);
    copy($tmppath_image_back,$back_path);
    chmod($back_path,0666);

    copy($tmppath_image_front,$efront_path);
    chmod($efront_path,0666);
    copy($tmppath_image_back,$eback_path);
    chmod($eback_path,0666);

    //get dimentions for the two originals
    list($front_width, $front_height) = getimagesize($front_path);
    list($back_width, $back_height) = getimagesize($back_path);

    //shift e images to have a width of 600 px

    try {
        $img = new abeautifulsite\SimpleImage($efront_path);
        $img->fit_to_width(600)->save();
        list($efront_width, $efront_height) = getimagesize($efront_path);
    } catch(Exception $e) {
        throw new Exception ('Error resizing front side: ' . $e->getMessage());
    }

    try {
        $img = new abeautifulsite\SimpleImage($eback_path);
        $img->fit_to_width(600)->save();
        list($eback_width, $eback_height) = getimagesize($eback_path);
    } catch(Exception $e) {
        throw new Exception( 'Error resizing back side: ' . $e->getMessage());
    }

    #update record to waiting
    $fields=array(
        'front_path'=> $front_path,
        'front_file_type'  => $front_img_type,
        'front_width'  => $front_width,
        'front_height'  => $front_height,
        'back_path'=> $back_path,
        'back_file_type'  => $back_img_type,
        'back_width'  => $back_width,
        'back_height'  => $back_height,

        'efront_path'=> $efront_path,
        'efront_file_type'  => $front_img_type, //same image type
        'efront_width'  => $efront_width,
        'efront_height'  => $efront_height,
        'eback_path'=> $eback_path,
        'eback_file_type'  => $back_img_type,
        'eback_width'  => $eback_width,
        'eback_height'  => $eback_height,
    );
    $db->update('ht_waiting',$theNewId,$fields);


    return $theNewId;


}

#takes the string value, pads it to the left with 0 and makes 3 wide sections
function get_string_filepath_from_id($i) {
    $number_folders = 4;
    $t = str_pad($i,$number_folders * 3,'0',STR_PAD_LEFT);
    $ret = '';
    for($p = 0; $p < $number_folders ; $p++) {
        $ret =  $ret.'/'.substr($t,$p * 3,3);
    }
    return $ret;

}


#returns true or false depending on if it can connect to url
function is_connected($url_to_check)
{
    //http://stackoverflow.com/questions/4860365/determine-in-php-script-if-connected-to-internet
    $connected = fsockopen($url_to_check, 80);
    $y = var_dump($connected);

    //website, port  (try 80 or 443)
    if ($connected){
        $is_conn = true; //action when connected
        fclose($connected);
    }else{
        $is_conn = false; //action in connection failure
    }
    return $is_conn;

}



function base64_to_image($base64_string, $output_file) {
    //http://stackoverflow.com/questions/15153776/convert-base64-string-to-an-image-file
    $ifp = fopen($output_file, "wb");

    $data = explode(',', $base64_string);

    fwrite($ifp, base64_decode($data[1]));
    fclose($ifp);

    return $output_file;
}


function getGUID(){
    if (function_exists('com_create_guid')){
        return trim(com_create_guid(),'{}');
    }else{
        $charid = strtoupper(md5(uniqid( mt_rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = ''
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);

        return $uuid;
    }
}

function get_json_last_err_string() {
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return ' - No errors';
            break;
        case JSON_ERROR_DEPTH:
            return ' - Maximum stack depth exceeded';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            return ' - Underflow or the modes mismatch';
            break;
        case JSON_ERROR_CTRL_CHAR:
            return ' - Unexpected control character found';
            break;
        case JSON_ERROR_SYNTAX:
            return ' - Syntax error, malformed JSON';
            break;
        case JSON_ERROR_UTF8:
            return ' - Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
        default:
            return ' - Unknown error';
            break;
    }
}

//usual curl wrapper, returns the http code, if the system does not have curl set up to work use the rest helper below
function get_curl_resp_code($url) {

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
    curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_TIMEOUT,10);
    curl_setopt($ch, CURLOPT_VERBOSE, true);

    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    if (_pages_isLocalHost()) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    curl_exec($ch);
   // $verboseLog = stream_get_contents($verbose);

   // echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
  //  exit;
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpcode;
}

//this allows us to post stuff without relying on curl, which some php environments do not have configured
function rest_helper($url, $params = null, $verb = 'GET', $format = 'json',$build_query = true,$gdebug=false)
{
    $ch = curl_init();
    $verbose = null;
    if ($gdebug) {
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
    }

    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POST, 1);
    if ($build_query) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    } else {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    }


    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    if (_pages_isLocalHost()) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    // receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec ($ch);

    $httpcode = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
    if ($httpcode == 0 || $httpcode >= 400) {
        throw new Exception("Could not send data, response was ".$httpcode);
    }

    if ($gdebug) {
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
    }
    curl_close ($ch);

    switch ($format) {
        case 'json':
            $r = json_decode($server_output);
            if ($r === null) {
                throw new Exception("failed to decode $server_output as json");
            }
            return $r;

        case 'xml':
            $r = simplexml_load_string($server_output);
            if ($r === null) {
                throw new Exception("failed to decode $server_output as xml");
            }
            return $r;
        default: {
            $r = $server_output;
        }
    }
    return $r;


}


function printOkJSONAndDie($phpArray=[]) {
    if (!is_array($phpArray)) {
        $r=[];
        $r['message'] = $phpArray;
        $phpArray = $r;
    }
    header('Content-Type: application/json');
    $phpArray['status'] = 'ok';
    $phpArray['valid'] = true;
    $out = json_encode($phpArray);
    if ($out) {
        print $out;
    } else {
        printErrorJSONAndDie( json_last_error_msg());
    }
    exit;
}

function printErrorJSONAndDie($message,$phpArray=[]) {
    header('Content-Type: application/json');
    $phpArray['status'] = 'error';
    $phpArray['valid'] = false;
    $phpArray['message'] = $message;
    $out = json_encode($phpArray);
    if ($out) {
        print $out;
    } else {
        print json_last_error_msg();
    }

    exit;
}



//for debugging
//for debugging
function print_nice($elem,$max_level=15,$print_nice_stack=array()){
    //if (is_object($elem)) {$elem = object_to_array($elem);}
    if(is_array($elem) || is_object($elem)){
        if(in_array($elem,$print_nice_stack,true)){
            echo "<span style='color:red'>RECURSION</span>";
            return;
        }
        $print_nice_stack[]=&$elem;
        if($max_level<1){
            echo "<span style='color:red'>reached maximum level</span>";
            return;
        }
        $max_level--;
        echo "<table border=1 cellspacing=0 cellpadding=3 width=100%>";
        if(is_array($elem)){
            echo '<tr><td colspan=2 style="background-color:#333333;"><strong><span style="color:white">ARRAY</span></strong></td></tr>';
        }else{
            echo '<tr><td colspan=2 style="background-color:#333333;"><strong>';
            echo '<span style="color:white">OBJECT Type: '.get_class($elem).'</span></strong></td></tr>';
        }
        $color=0;
        foreach($elem as $k => $v){
            if($max_level%2){
                $rgb=($color++%2)?"#888888":"#44BBBB";
            }else{
                $rgb=($color++%2)?"#777777":"#22BBFF";
            }
            echo '<tr><td valign="top" style="width:40px;background-color:'.$rgb.';">';
            echo '<strong style="color:black">'.$k."</strong></td><td style='background-color:white;color:black'>";
            print_nice($v,$max_level,$print_nice_stack);
            echo "</td></tr>";
        }
        echo "</table>";
        return;
    }
    if($elem === null){
        echo "<span style='color:green'>NULL</span>";
    }elseif($elem === 0){
        echo "0";
    }elseif($elem === true){
        echo "<span style='color:green'>TRUE</span>";
    }elseif($elem === false){
        echo "<span style='color:green'>FALSE</span>";
    }elseif($elem === ""){
        echo "<span style='color:green'>EMPTY STRING</span>";
    }else{
        echo str_replace("\n","<strong><span style='color:green'>*</span></strong><br>\n",$elem);
    }
}

function TO($object){ //Test Object
    if(!is_object($object)){
        throw new Exception("This is not a Object");
    }
    if(class_exists(get_class($object), true)) echo "<pre>CLASS NAME = ".get_class($object);
    $reflection = new ReflectionClass(get_class($object));
    echo "<br />";
    echo $reflection->getDocComment();

    echo "<br />";

    $metody = $reflection->getMethods();
    foreach($metody as $key => $value){
        echo "<br />". $value;
    }

    echo "<br />";

    $vars = $reflection->getProperties();
    foreach($vars as $key => $value){
        echo "<br />". $value;
    }
    echo "</pre>";
}


# this protects from having a umask set in a shared environment
function mkdir_r($dirName, $rights=0777){
    $dirs = explode('/', $dirName);
    $dir='';
    foreach ($dirs as $part) {
        $dir.=$part.'/';
        if (!is_dir($dir) && strlen($dir)>0) {
            mkdir($dir);
            chmod($dir, $rights);
        }

    }
}

function test_site_connection($theURL) {
    $resp = intval(get_curl_resp_code($theURL));
    if($resp >=200 && $resp < 400){
        return true;
    }

    return false;
}

function get_http_response_code($theURL) {
    $headers = get_headers($theURL);
    return substr($headers[0], 9, 3);
}

function get_jobs($jobid,$b_is_transcribed=false,$b_is_checked=false,
                  $transcribed_id = null,$checked_id=null,$b_only_free=false){
    global $settings,$user;
    $db = DB::getInstance();

    if ($b_is_transcribed) {
        $transcribed_op = 'IS NOT';
    } else {
        $transcribed_op = 'IS';
    }

    if ($b_is_checked) {
        $checked_op = 'IS NOT';
    } else {
        $checked_op = 'IS';
    }

    if ($b_only_free) {
        $time_limit = $settings->view_timeout_seconds;
        $userid = $user->data()->id;

        $free_check = " AND ( ($time_limit <=  UNIX_TIMESTAMP() - j.viewing_user_at) ||  (j.viewing_user_at is NULL) || (viewing_user_id = $userid) ) ";
    } else {
        $free_check = '';
    }



    $where_ids = [];
    if ($transcribed_id) {
        $transcribed_id = intval($transcribed_id);
        array_push($where_ids,"j.transcriber_user_id = {$transcribed_id}");
    }

    if ($checked_id) {
        $checked_id = intval($checked_id);
        array_push($where_ids,"j.transcriber_user_id = {$checked_id}");
    }

    $where_users = '';
    if (!empty($where_ids)) {
        $where_users = implode(' and ',$where_ids);
        $where_users = "AND {$where_users}";
    }

    $where_stuff = "j.transcriber_user_id {$transcribed_op} null AND
                        j.checker_user_id {$checked_op} null {$where_users} $free_check";

    if ($jobid) {
        $jobid = intval($jobid);
        $where_stuff = " j.id = {$jobid} ";
    }



    $query = $db->query( "
        select 
          j.id, j.client_id, j.profile_id, j.is_initialized,
          j.transcriber_user_id, j.checker_user_id,

          j.created_at as created_timestamp,
          j.modified_at as modified_timestamp,
          j.uploaded_at as uploaded_timestamp,
          j.transcribed_at as transcribed_timestamp,
          j.checked_at as checked_timestamp,

          j.uploader_email, j.uploader_lname,
          j.uploader_fname, 
          
          j.fname, j.mname, j.lname, j.suffix, j.title,
          j.suit,j.address, j.city, j.state, j.zip, j.email, j.website, j.work_phone,
          j.cell_phone, j.fax, j.skype, j.company,j.work_phone_extension,
          j.country,j.twitter,j.home_phone,j.other_phone,j.notes,
          utrans.id as utrans_id,utrans.email as utrans_email, utrans.fname as utrans_fname, utrans.lname as utrans_lname,
          uchecks.id as uchecks_id,uchecks.email as uchecks_email, uchecks.fname as uchecks_fname, uchecks.lname as uchecks_lname,

          org_side_a.id as org_side_a_id,org_side_a.image_url as org_side_a_url , org_side_a.image_height  as org_side_a_height, org_side_a.image_width as org_side_a_width,

          org_side_b.id as org_side_b_id,org_side_b.image_url as org_side_b_url , org_side_b.image_height  as org_side_b_height, org_side_b.image_width as org_side_b_width,

          edit_side_a.id as edit_side_a_id,edit_side_a.image_url as edit_side_a_url , edit_side_a.image_height  as edit_side_a_height, edit_side_a.image_width as edit_side_a_width,

          edit_side_b.id as edit_side_b_id,edit_side_b.image_url as edit_side_b_url , edit_side_b.image_height  as edit_side_b_height, edit_side_b.image_width as edit_side_b_width
          

        from ht_jobs j
          left join users utrans ON utrans.id = j.transcriber_user_id
          left join users uchecks ON uchecks.id = j.checker_user_id
          left join ht_images org_side_a ON org_side_a.ht_job_id = j.id AND org_side_a.side = 0 AND org_side_a.is_edited=0
          left join ht_images org_side_b ON org_side_b.ht_job_id = j.id AND org_side_b.side = 1 AND org_side_b.is_edited=0
          left join ht_images edit_side_a ON edit_side_a.ht_job_id = j.id AND edit_side_a.side = 0 AND edit_side_a.is_edited=1
          left join ht_images edit_side_b ON edit_side_b.ht_job_id = j.id AND edit_side_b.side = 1 AND edit_side_b.is_edited=1

        where {$where_stuff} AND j.is_initialized = 1
    ",[]);

    $results = $query->results();
    $ret = [];
    foreach ($results as $rec) {
        $job = [
                'id' => $rec->id,
                'client_id' => $rec->client_id,
                'profile_id' => $rec->profile_id,
                'is_initialized' => $rec->is_initialized,
                'transcriber_user_id' =>  $rec->transcriber_user_id,
                'checker_user_id' => $rec->checker_user_id,
                'created_timestamp' =>  $rec->created_timestamp,
                'uploaded_timestamp' => $rec->uploaded_timestamp,
                'modified_timestamp' =>  $rec->modified_timestamp,
                'transcribed_timestamp' => $rec->transcribed_timestamp,  
				'checked_timestamp' => $rec->checked_timestamp,
                'uploader_email' => $rec->uploader_email,
                'uploader_lname' =>  $rec->uploader_lname,
                'uploader_fname' => $rec->uploader_fname
        ];
        
        $transcribe = [
            'fname' => $rec->fname, 'mname' => $rec->mname, 'lname' => $rec->lname, 'suffix' => $rec->suffix, 'title' => $rec->title,
            'suit' => $rec->suit,
            'address' => $rec->address, 'city' => $rec->city, 'state' => $rec->state, 'zip' => $rec->zip, 'email' => $rec->email,
            'website' => $rec->website, 'work_phone' => $rec->work_phone,'work_phone_extension'=>$rec->work_phone_extension,
            'cell_phone' => $rec->cell_phone, 'fax' => $rec->fax, 'skype' => $rec->skype,
            'company'=>$rec->company,
            'country'=>$rec->country,'twitter'=>$rec->twitter,'home_phone'=>$rec->home_phone,'other_phone'=>$rec->other_phone,
            'notes' => $rec->notes
        ];
        
        $translater = [
            'id'=>$rec->utrans_id,'email' => $rec->utrans_email, 'fname' => $rec->utrans_fname, 'lname' => $rec->utrans_lname
        ];

        $checker = [
            'id'=>$rec->uchecks_id,'email' => $rec->uchecks_email, 'fname' => $rec->uchecks_fname, 'lname' => $rec->uchecks_lname
        ];
        
        $org_side_a = [
            'id' => $rec->org_side_a_id,'url' => $rec->org_side_a_url , 'height' => $rec->org_side_a_height, 'width' => $rec->org_side_a_width,
        ];

        $org_side_b = [
            'id' => $rec->org_side_b_id,'url' => $rec->org_side_b_url , 'height' => $rec->org_side_b_height, 'width' => $rec->org_side_b_width,
        ];

        $edit_side_a = [
            'id' => $rec->edit_side_a_id,'url' => $rec->edit_side_a_url , 'height' => $rec->edit_side_a_height, 'width' => $rec->edit_side_a_width,
        ];

        $edit_side_b = [
            'id' => $rec->edit_side_b_id,'url' => $rec->edit_side_b_url , 'height' => $rec->edit_side_b_height, 'width' => $rec->edit_side_b_width,
        ];

        $images = [
          'org_side_a' =>  $org_side_a, 'org_side_b' =>  $org_side_b,'edit_side_a' =>  $edit_side_a, 'edit_side_b' =>  $edit_side_b,
        ];



        $node = [
            'job' => $job,
            'transcribe' =>  $transcribe,
            'translater' => $translater,
            'checker' => $checker,
            'images' => $images
        ];

        array_push($ret,$node);

        
    }

    # don't want the complciations of nested queries, so add tag information now
    $job_id_array = [];
    for($i=0; $i < sizeof($ret); $i++) {
        $node = $ret[$i];
        $id = $node['job']['id'];
        array_push($job_id_array, $id);

    }
    $tags = get_tags_for_jobs($job_id_array);
    for($i=0; $i < sizeof($ret); $i++) {
        $node = $ret[$i];
        $id = $node['job']['id'];
        if (array_key_exists($id,$tags)) {
            $ret[$i]['tags'] = $tags[$id];
            $ret[$i]['transcribe']['tag_string'] = generate_tag_string_from_job($ret[$i]);
        } else {
            $ret[$i]['tags'] = array();
            $ret[$i]['transcribe']['tag_string'] = '';
        }

    }


    return $ret;


}

function save_tag_string($job_id,$tag_string) {
    #split tag string by commas
    if (!$tag_string) {return false;}
    $tags = explode(',',$tag_string);
    if (!$tags) {return false;}

    #delete existing tags for this job
    $db = DB::getInstance();

    $db->query("DELETE FROM ht_tag_jobs WHERE ht_job_id = $job_id");
    for($i=0; $i < sizeof($tags); $i++) {
        $parts = explode('=',$tags[$i]);
        $tag_value = null;
        if (sizeof($parts) > 1) {
            $tag_value = trim($parts[1]);
        }
        $tag_name = trim($parts[0]);
        if ($tag_name) {
            insert_tag_to_job($job_id,$tag_name,$tag_value);
        }

    }



    return false;


}

function  add_tag_name($tag_name) {
        // adds tag name if does not exist
    $db = DB::getInstance();
    $query = $db->query( "select id from ht_tags where tag_name = ?;",[$tag_name]);
    if ($query->count() > 0) {return $query->first()->id;}
    $db->query( "INSERT INTO ht_tags(tag_name,created_at_ts) VALUES (?,UNIX_TIMESTAMP())",[$tag_name]);
    return $db->lastId();
}

function insert_tag_to_job($job_id,$tag_name,$tag_value) {
    #gets the tag id and then inserts into ht_tag_jobs

    #get the tag id
    $db = DB::getInstance();
    $tag_id = add_tag_name($tag_name);
    $db->query( "INSERT INTO ht_tag_jobs(ht_tag_id,ht_job_id,tag_value) VALUES (?,?,?)",[$tag_id,$job_id,$tag_value]);

}

#tags is array of just names, or is array of objects (tag_id,tag_name,tag_value)
#this converts these to a tag string and then calls save_tag_string($job_id,$tag_string)
function add_tags_to_job($jobid,$tags) {
    if ($tags == null) {return;}
    //this is array of tags now, but don't know if the tags have values or not
    $tag_string = generate_tag_string($tags);
    save_tag_string($jobid,$tag_string);
}

function generate_tag_string_from_job($job) {
    $tags = $job['tags'];
    return generate_tag_string($tags);
}

function generate_tag_string($tag_array) {
    $tags = $tag_array;
    if (!$tags) {return '';}
    $string_array = [];
    for($i=0; $i < sizeof($tags); $i++) {
        $string = tag_string_from_node_or_name($tags[$i]);
        array_push($string_array,$string);
    }
    return implode(',',$string_array);
}



function tag_string_from_node_or_name($node) {
    //if is only string return the string
    if (is_string($node)) { return trim($node);}
    $tag_name = trim($node['tag_name']);
    $tag_value = isset($node['tag_value']) && $node['tag_value'] ? trim($node['tag_value']): '';
    if ($tag_value) {
        $string = "$tag_name=$tag_value";
    } else {
        $string = "$tag_name";
    }
    return $string;
}

function get_tags_for_jobs($job_id_array) {
    if (sizeof($job_id_array) == 0) return array();
    $db = DB::getInstance();
    $id_string = implode(',',$job_id_array);
    $query = $db->query( "
        select ht_tag_jobs.ht_job_id as job_id,ht_tags.id as tag_id,tag_name,tag_value
            from ht_tag_jobs
              INNER JOIN ht_tags ON ht_tags.id = ht_tag_jobs.ht_tag_id
            where ht_tag_jobs.ht_job_id in ($id_string)
            ORDER BY ht_tag_jobs.ht_job_id,ht_tags.id
    ");
    $results = $query->results();
    $ret = [];
    foreach ($results as $rec) {
       if (!array_key_exists($rec->job_id,$ret)) {
           $ret[$rec->job_id] = [];
       }
        $node = [
          'tag_id' => $rec->tag_id,
          'tag_name' => $rec->tag_name,
          'tag_value' => $rec->tag_value
        ];
        array_push($ret[$rec->job_id],$node);
    }

    return $ret;

}

function addJobViewStamp($user,$jobid) {
    $db = DB::getInstance();
    return $db->update('ht_jobs', $jobid, ['viewing_user_id' => $user->data()->id, 'viewing_user_at' => time()]);
}

function clearJobViewStamp($jobid) {
    $db = DB::getInstance();
    return $db->update('ht_jobs', $jobid, ['viewing_user_id' => null, 'viewing_user_at' =>null]);
}

function canEditJob($user,$jobid) {

    global  $settings;
    $time_limit = $settings->view_timeout_seconds;

   // can edit this job if the time locked is null or exceeds the cutoff
    // or the user is the same if viewing_user_id set
    $userid = $user->data()->id;
    $db = DB::getInstance();
    $where_string = "(id = ? ) AND ( (viewing_user_id = $userid ) OR (viewing_user_id is NULL) OR ($time_limit <=  UNIX_TIMESTAMP() - viewing_user_at) ||  (viewing_user_at is NULL) ) ";
    $db->query("Select id FROM ht_jobs where $where_string ;",[$jobid]);
    if ( ( !$db->count() ) || ($db->count() <= 0)) {
        return false;
    } else {
        return true;
    }
}
// Set API's in users/private_init.php
function call_api($job,$website_url) {
   $base_url = Config::get('api/on_check');
    $query = [];
	if (!empty($job->transcribe->email) )  {
       array_push($query , 'email='. urlencode($job->transcribe->email));
   }
   	//Duplicate Website - Labeled as 'url' for enRICH Data and 'website' for everything else
    if (!empty(trim($job->transcribe->website)) )  {
        array_push($query , 'url='. urlencode($job->transcribe->website));
    }
	if (!empty(trim($job->transcribe->website)) )  {
        array_push($query , 'website='. urlencode($job->transcribe->website));
    }
	if (!empty($job->transcribe->fname) )  {
       array_push($query , 'fname='. urlencode($job->transcribe->fname));
   }
    if (!empty($job->transcribe->mname) )  {
       array_push($query , 'mname='. urlencode($job->transcribe->mname));
   }
	if (!empty($job->transcribe->lname) )  {
       array_push($query , 'lname='. urlencode($job->transcribe->lname));
   }
	if (!empty($job->transcribe->suffix) )  {
       array_push($query , 'suffix='. urlencode($job->transcribe->suffix));
   }
	if (!empty($job->transcribe->title) )  {
       array_push($query , 'title='. urlencode($job->transcribe->title));
   }

    if (!empty($job->transcribe->suit) )  {
        array_push($query , 'suit='. urlencode($job->transcribe->suit));
    }

	if (!empty($job->transcribe->address) )  {
       array_push($query , 'address='. urlencode($job->transcribe->address));
    }



	if (!empty($job->transcribe->city) )  {
       array_push($query , 'city='. urlencode($job->transcribe->city));
   }
	if (!empty($job->transcribe->state) )  {
       array_push($query , 'state='. urlencode($job->transcribe->state));
   }
	if (!empty($job->transcribe->zip) )  {
       array_push($query , 'zip='. urlencode($job->transcribe->zip));
   }
	if (!empty($job->transcribe->work_phone) )  {
       array_push($query , 'work_phone='. urlencode($job->transcribe->work_phone));
   }
	if (!empty($job->transcribe->work_phone_extension) )  {
       array_push($query , 'work_phone_extension='. urlencode($job->transcribe->work_phone_extension));
   }
	if (!empty($job->transcribe->cell_phone) )  {
       array_push($query , 'cell_phone='. urlencode($job->transcribe->cell_phone));
   }
	if (!empty($job->transcribe->fax) )  {
       array_push($query , 'fax='. urlencode($job->transcribe->fax));
   }
	if (!empty($job->transcribe->skype) )  {
       array_push($query , 'skype='. urlencode($job->transcribe->skype));
   }
	if (!empty($job->transcribe->company) )  {
       array_push($query , 'company='. urlencode($job->transcribe->company));
   }
	if (!empty($job->transcribe->country) )  {
       array_push($query , 'country='. urlencode($job->transcribe->country));
   }
	if (!empty($job->transcribe->twitter) )  {
       array_push($query , 'twitter='. urlencode($job->transcribe->twitter));
   }
	if (!empty($job->transcribe->home_phone) )  {
       array_push($query , 'home_phone='. urlencode($job->transcribe->home_phone));
   }
	if (!empty($job->transcribe->other_phone) )  {
       array_push($query , 'other_phone='. urlencode($job->transcribe->other_phone));
   }
	if (!empty($job->transcribe->notes) )  {
       array_push($query , 'notes='. urlencode($job->transcribe->notes));
   }
	if (!empty($job->transcribe->tag_string) )  {
       array_push($query , 'tag_string='. urlencode($job->transcribe->tag_string));
   }
   // User id, Transcription id, Profile id
	if (!empty($job->job->id) )  {
       array_push($query , 'transcription_id='. urlencode($job->job->id));
   }
   	if (!empty($job->job->client_id) )  {
       array_push($query , 'user_id='. urlencode($job->job->client_id));
   }
   	if (!empty($job->job->profile_id) )  {
       array_push($query , 'profile_id='. urlencode($job->job->profile_id));
   }
   // Timestamps
   	if (!empty($job->job->uploaded_timestamp) )  {
       array_push($query , 'uploaded_timestamp='. urlencode($job->job->uploaded_timestamp));
   }
   if (!empty($job->job->transcribed_timestamp) )  {
       array_push($query , 'transcribed_timestamp='. urlencode($job->job->transcribed_timestamp));
   }
   // Images   
	if (!empty($job->images->org_side_a->url) )  {
       array_push($query , 'org_side_a='. urlencode($job->images->org_side_a->url));
   }
   	if (!empty($job->images->org_side_b->url) )  {
       array_push($query , 'org_side_b='. urlencode($job->images->org_side_b->url));
   }
   	if (!empty($job->images->edit_side_a->url) )  {
       array_push($query , 'edit_side_a='. urlencode($job->images->edit_side_a->url));
   }
   	if (!empty($job->images->edit_side_b->url) )  {
       array_push($query , 'edit_side_b='. urlencode($job->images->edit_side_b->url));
   }
   
    $q = implode('&',$query);

    if (!empty($q)) {
        $full_url = $base_url . '&' . $q;
        $resp = get_curl_resp_code($full_url);
        if ($resp != 200 && $resp != 404) {
            publish_to_sns("Could not send api information", "While sending, got the response code of : $resp . The url was $full_url");
        }

        $full_url = $website_url .'/processes/main_function.php'. '&' . $q;
        $resp = get_curl_resp_code($full_url);
        if ($resp != 200 && $resp != 404) {
            publish_to_sns("Could not send main function information", "While sending, got the response code of : $resp . The url was $full_url");
        }
    }
}

function get_file_from_url($url) {
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
    if (_pages_isLocalHost()) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    $contents = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception("could not open url: $url because of curl error: ".curl_error($ch) );
    } else {
        curl_close($ch);
    }

    if (!is_string($contents) || !strlen($contents)) {
        throw new Exception("could not get contents from : $url  " );

    }

    return $contents;
}

function _pages_isLocalHost() {
    if (isset($_SERVER['REMOTE_ADDR'])) {
        if( in_array( $_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' ) ) ) {
            return true;
        }
    }


    return false;

}

function runAfterHook($root_path_of_app,$jobid) {
    return;
    $command = "php $root_path_of_app/tasks/after_hook.php $jobid";
    execInBackground($command);
}

//deletes all job information from the local database (warning doing this on production machine will remove all real jobs)
//only call from command line, do not link to gui
function deleteAllJobs() {
    $db = DB::getInstance();
    $db->query("DELETE FROM ht_tag_jobs WHERE 1");
    $db->query("DELETE FROM ht_file_watching WHERE 1");
    $db->query("DELETE FROM ht_images WHERE 1");
    $db->query("DELETE FROM ht_waiting WHERE 1");
    $db->query("DELETE FROM ht_jobs WHERE 1");
}

function recursiveRemove($dir) {
    $structure = glob(rtrim($dir, "/").'/*');
    if (is_array($structure)) {
        foreach($structure as $file) {
            if (is_dir($file)) recursiveRemove($file);
            elseif (is_file($file)) unlink($file);
        }
    }
    rmdir($dir);
}


