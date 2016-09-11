<?php
//will upload all unless is set
function upload_local_storage($idOnly=null) {


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

            print "Uploading record id ".$rec->id . ' [Client ID '.$rec->client_id.'] ' . ' [Profile ID '.$rec->profile_id.'] ' . "\n";
            $whatans = upload_from_waiting_row($rec,$settings->s3_bucket_name,$s3Client,$settings->website_url);
            if ($whatans !== true) {
                print '[Error] '. $whatans  . "\n";
            } else {
                print '[OK]'. "\n";
            }
        }

    }

    return true;

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


     $status_to_post = '';
     try {
         $result = $s3Client->putObject(array(
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
         $status_to_post .= ', '.$e->getMessage();
         return  $status_to_post; //writes status to row in finally block
     }


     //echo $result['ObjectURL'];

     try {
         $result = $s3Client->putObject(array(
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
     $what = rest_helper($url_to_use, $params = $msg, $verb = 'POST', $format = 'json');
     if ($what->status == 'ok') {
         //update the $row
         $db->update('ht_waiting', $row->id, ['is_uploaded' => 1, 'uploaded_at' => date('Y-m-d H:i:s')]);
         $status_to_post .= ', '.$what->message;
         return true;
     } else {
         $status_to_post .= ', ERROR From Server:'.$what->message;
         return  $status_to_post;
     }
 }
 finally {
     $db->update('ht_waiting', $row->id, ['upload_result' => $status_to_post, 'uploaded_at' => date('Y-m-d H:i:s')]);
 }



}

function add_waiting($client_id,$profile_id,$tmppath_image_front,$tmppath_image_back,
                     $front_img_type,$back_img_type,$user,$upload_folder) {

    // $extension = strtolower(pathinfo('/home/will/Desktop/img0.png', PATHINFO_EXTENSION));

   // insert new record
    $db = DB::getInstance();
    $fields=array(
        'user_id' => $user->data()->id,
        'uploader_email' => $user->data()->email,
        'uploader_lname' => $user->data()->fname,
        'uploader_fname' => $user->data()->fname,
        'client_id'=> $client_id,
        'profile_id'=> $profile_id
    );
    $db->insert('ht_waiting',$fields);
    $theNewId=$db->lastId();




    $folder_part = get_string_filepath_from_id($theNewId);
    $local_folder = $upload_folder.$folder_part;
    $front_name = $client_id.'_'.$theNewId.'_'.'A'.'.'.$front_img_type;
    $back_name = $client_id.'_'.$theNewId.'_'.'B'.'.'.$back_img_type;

    $front_path = $local_folder . '/' . $front_name;
    $back_path = $local_folder . '/' . $back_name;



    mkdir_r($local_folder,0777);
    copy($tmppath_image_front,$front_path);
    chmod($front_path,0666);
    copy($tmppath_image_back,$back_path);
    chmod($back_path,0666);

    list($front_width, $front_height) = getimagesize($front_path);
    list($back_width, $back_height) = getimagesize($back_path);

    #update record to waiting
    $fields=array(
        'front_path'=> $front_path,
        'back_path'=> $back_path,
        'front_file_type'  => $front_img_type,
        'back_file_type'  => $back_img_type,
        'front_width'  => $front_width,
        'front_height'  => $front_height,
        'back_width'  => $back_width,
        'back_height'  => $back_height
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

//this allows us to post stuff without relying on curl, which some php environments do not have configured
function rest_helper($url, $params = null, $verb = 'GET', $format = 'json')
{
    $cparams = array(
        'http' => array(
            'method' => $verb,
            'ignore_errors' => true,
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
                                        "User-Agent:MyAgent/1.0\r\n"
        )
    );
    if ($params !== null) {
        $params = http_build_query($params);
        if ($verb == 'POST') {
            $cparams['http']['content'] = $params;
        } else {
            $url .= '?' . $params;
        }
    }

    $context = stream_context_create($cparams);
    $fp = @fopen($url, 'rb', false, $context);
    if (!$fp) {
        $res = false;
    } else {
        // If you're trying to troubleshoot problems, try uncommenting the
        // next two lines; it will show you the HTTP response headers across
        // all the redirects:
        // $meta = stream_get_meta_data($fp);
        // var_dump($meta['wrapper_data']);
        $res = @stream_get_contents($fp);
    }

    if ($res === false) {
        throw new Exception("$verb $url failed: $php_errormsg");
    }

    switch ($format) {
        case 'json':
            $r = json_decode($res);
            if ($r === null) {
                throw new Exception("failed to decode $res as json");
            }
            return $r;

        case 'xml':
            $r = simplexml_load_string($res);
            if ($r === null) {
                throw new Exception("failed to decode $res as xml");
            }
            return $r;
    }
    return $res;
}


function printOkJSONAndDie($phpArray) {
    header('Content-Type: application/json');
    $phpArray['status'] = 'ok';
    print json_encode($phpArray);
    exit;
}

function printErrorJSONAndDie($message,$phpArray=[]) {
    header('Content-Type: application/json');
    $phpArray['status'] = 'error';
    $phpArray['message'] = $message;
    print json_encode($phpArray);
    exit;
}

//for debugging
function print_nice($elem,$max_level=15,$print_nice_stack=array()){
    //if (is_object($elem)) {$elem = object_to_array($elem);}
    if(is_array($elem) || is_object($elem)){
        if(in_array($elem,$print_nice_stack,true)){
            echo "<font color=red>RECURSION</font>";
            return;
        }
        $print_nice_stack[]=&$elem;
        if($max_level<1){
            echo "<font color=red>reached maximum level</font>";
            return;
        }
        $max_level--;
        echo "<table border=1 cellspacing=0 cellpadding=3 width=100%>";
        if(is_array($elem)){
            echo '<tr><td colspan=2 style="background-color:#333333;"><strong><font color=white>ARRAY</font></strong></td></tr>';
        }else{
            echo '<tr><td colspan=2 style="background-color:#333333;"><strong>';
            echo '<font color=white>OBJECT Type: '.get_class($elem).'</font></strong></td></tr>';
        }
        $color=0;
        foreach($elem as $k => $v){
            if($max_level%2){
                $rgb=($color++%2)?"#888888":"#BBBBBB";
            }else{
                $rgb=($color++%2)?"#8888BB":"#BBBBFF";
            }
            echo '<tr><td valign="top" style="width:40px;background-color:'.$rgb.';">';
            echo '<strong>'.$k."</strong></td><td>";
            print_nice($v,$max_level,$print_nice_stack);
            echo "</td></tr>";
        }
        echo "</table>";
        return;
    }
    if($elem === null){
        echo "<font color=green>NULL</font>";
    }elseif($elem === 0){
        echo "0";
    }elseif($elem === true){
        echo "<font color=green>TRUE</font>";
    }elseif($elem === false){
        echo "<font color=green>FALSE</font>";
    }elseif($elem === ""){
        echo "<font color=green>EMPTY STRING</font>";
    }else{
        echo str_replace("\n","<strong><font color=red>*</font></strong><br>\n",$elem);
    }
}


function TO($object){ //Test Object
    if(!is_object($object)){
        throw new Exception("This is not a Object");
        return;
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
    if(intval(get_http_response_code($theURL)) < 400){
        return true;
    }

    return false;
}

function get_http_response_code($theURL) {
    $headers = get_headers($theURL);
    return substr($headers[0], 9, 3);
}
?>