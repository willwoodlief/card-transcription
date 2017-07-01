<?php

require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/header.php';
require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
require_once $abs_us_root.$us_url_root.'lib/aws/aws-autoloader.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/mime_type.php';
require_once $abs_us_root.$us_url_root.'lib/SimpleImage/src/abeautifulsite/SimpleImage.php';
?>

<?php if (!securePage($_SERVER['PHP_SELF'])){die();}
if ($settings->site_offline==1){die("The site is currently offline.");}?>

<?php

$validation = new Validate();
$error_count = 0;
$nid = false;
$servMsg = '';



if(!empty($_POST['create_entry'])) {


    $token = Input::get('csrf');
    if (!Token::check($token)) {
        // die('Token doesn\'t match!');
        //do not include in the page, as it will be idle for long periods and that expires the token, its already protected by login
    }
    if (!isset($_POST['json_in'])) {
        $validation->addError('Need Json');
        $error_count ++;
    } else {
        try {
            $json_in = $_POST['json_in'];
            $json_in = trim($json_in);
            $jsonObj = json_decode($json_in,true);
            if (!$jsonObj) {
                $validation->addError(get_json_last_err_string());
                $error_count ++;

            } else {
                $params = ['Message'=> json_encode($jsonObj)];
               // $url_to_use = $settings->website_url . '/pages/upload_from_bucket.php';
                $url_to_use ='http://localhost/ht/pages/upload_from_bucket.php';
                $servMsg = rest_helper($url_to_use, $params,  'POST',  'json',false);
            }

        } catch (Exception $e) {
            $validation->addError($e->getMessage());
            $error_count++;
        }
    }



}

?>

<style type="text/css" media="screen">
    #editor {
        position: relative;
        margin: 10px;
        height: 400px;
    }
</style>

<div id="page-wrapper">
    <div class="row">

        <?php if($error_count == 0 && $servMsg) {?>
            <div class=" col-sm-offset-2 col-sm-10 alert alert-success" >
                <pre>
                    <?php print_nice($servMsg); ?>
                </pre>
            </div>
        <?php } ?>
        <div id="form-errors" class="col-sm-offset-2 col-sm-4">
            <?=$validation->display_errors();?>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="row">
            <div class=" col-sm-offset-2 col-sm-6">
                <h1 class="page-header">
                    Test the Input Json
                </h1>
                Edit the json below, and then press the button.<br>
                If it works then a new transcription job will be made in the website
                <br>The json below is filled with some values that work on my aws account, but you will
                want to change the bucket and image keys to an account that is used by this app
            </div>
        </div>
                <!-- Content goes here -->
                <form class="" enctype="multipart/form-data"  action="test_json_in.php" name="uploads" method="post"  onSubmit="return getEditorData()">


<input type="hidden" name="json_in" id="json_in" value="">


                    <!-- List group -->
                    <div id="editor" class=" col-sm-offset-2 col-sm-10">
{
    "client_id": "will",
    "profile_id": "w2",
    "uploader_email": "willwoodlief@gmail.com",
    "bucket": "enrich-scanner",
    "side_a_key": "img1a_id1_p2_20161004.jpg",
    "side_b_key": "img2a_id1_p1_20161005.jpg",
    "notes": "this is an example json used to send in the body of the sns, here is it filled with some data",
    "tags":[{"tag_name":"Place Uploaded","tag_value":"Scansville,NJ"},{"tag_name":"Test Data","tag_value":null},{"tag_name":"When-Uploaded","tag_value": "June 24,2017"}]
}
                    </div>

                    <!-- User id, this is called client_id to distinguish it from a user  -->


                    <div class="form-group col-sm-offset-2 col-sm-4 input-job-group">
                        <label for="create_entry" class="input-job-label"></label>
                        <input class='btn btn-primary input-job-box' type='submit' name="create_entry" value='Create New Entry with Json'/>
                    </div>

                </form>



                <!-- Content Ends Here -->
            </div> <!-- /.col -->
        </div> <!-- /.row -->
    </div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script src="../users/js/plugins/ace/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
<script src="js/test_json_functions.js"></script>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
