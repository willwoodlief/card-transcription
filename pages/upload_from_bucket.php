<?php
# returns json status of ok or error with message
# anyone can post to this, but unless the images are in the bucket,which can only be added to by authorized,
# the post is ignored

#ideally, this message would be passed by a private messaging system like aws sqs, but
# have to write this assuming no way to set up a cron job or independent task that runs in background

require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'/users/includes/header_json.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';
$screamer = true;

if ($screamer) {
    $post = file_get_contents('php://input');
    publish_to_sns("Post Dump of new->json", $post);
}


if (!Input::get('Message')) {
    if (isset($_POST["Message"])) {
        $message = $_POST['Message'];
    } elseif (isset($_GET["Message"])) {
        $message = $_GET['Message'];
    } else {
        $message = null;
    }
    $debug = array('message'=> 'Did not find the message param', 'get params' => $message);
    if ($screamer) {

        publish_to_sns("Post Dump of new->json", $debug);
    }
    printErrorJSONAndDie($debug);
}

try {


    if (isset($_POST["Message"])) {
        $message = $_POST['Message'];
    } elseif (isset($_GET["Message"])) {
        $message = $_GET['Message'];
    } else {
        $message = null;
    }

    $job = null;
    if ($message) {
        $o_message = to_utf8(trim($message));
        //unescape newlines from \" to "
        $message = str_replace('\"','"',$o_message);
        $job = json_decode($message);
        if (!$job) {
            $what = get_json_last_err_string();
            $debug = array('message'=> 'Could not convert message to json',
                            'json error' => $what,
                             'json_attempted'=>$message   );
            if ($screamer) {

                publish_to_sns("Error Converting Json of new->json", $debug);
            }
            printErrorJSONAndDie($debug);
        }
    } else {
        if ($screamer) {

            publish_to_sns("Logic Error in new->json", '..?...');
        }
        printErrorJSONAndDie('Logic Error');
    }

//we ignore subject

    $client_id = $job->client_id;
    $profile_id = $job->profile_id;
    $bucket = $job->bucket;
    $side_a_key = $job->side_a_key;
    $side_b_key = $job->side_b_key;
    $uploader = $job->uploader_email;
    $notes = $job->notes;
    $tags = $job->tags;
    $ext_a = substr(strrchr($side_a_key, '.'), 1);
    $ext_b = substr(strrchr($side_b_key, '.'), 1);
    $this_user = new User('admin');

    $nid = add_waiting_from_bucket($client_id, $profile_id, $side_a_key, $side_b_key,
        $ext_a, $ext_b, $this_user, $bucket, $uploader, $tags, $notes);

    upload_local_storage($nid);


    printOkJSONAndDie('images put into queue; nid is ' . $nid);
}
catch(Exception $e) {
    if ($screamer) {
        $debug = array('message'=> $e->getMessage(), 'trace' => $e->getTrace());
        publish_to_sns("Error in new->json", $debug);
    }
    printErrorJSONAndDie('could not insert job: '. $e->getMessage() . "\n" . $e->getTraceAsString());
}


