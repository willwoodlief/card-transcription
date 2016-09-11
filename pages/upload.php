<?php

require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/header.php';
require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
require_once $abs_us_root.$us_url_root.'lib/aws/aws-autoloader.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/mime_type.php';
?>

<?php if (!securePage($_SERVER['PHP_SELF'])){die();}
if ($settings->site_offline==1){die("The site is currently offline.");}?>

<?php
$b_show_disconect_message = false;
$validation = new Validate();
$error_count = 0;
$nid = false;




if(!empty($_POST['uploads'])) {


    $token = $_POST['csrf'];
    if (!Token::check($token)) {
        die('Token doesn\'t match!');
    }
    $client_id = Input::get('client_id');
    if (!$client_id) {
        $validation->addError('Need Client ID');
        $error_count ++;
    }
    $profile_id = Input::get('profile_id');
    if (!$profile_id) {
        $validation->addError('Need Profile ID');
        $error_count++;
    }
    $front_image_file = $_FILES['front_of_card']['tmp_name'];
    $front_extension = strtolower(pathinfo($_FILES['front_of_card']['name'], PATHINFO_EXTENSION));
    if (!$front_image_file) {
        $validation->addError('Need Front of  Card');
        $error_count++;
    }

    $back_image_file = $_FILES['back_of_card']['tmp_name'];
    $back_extension = strtolower(pathinfo($_FILES['back_of_card']['name'], PATHINFO_EXTENSION));
    if (!$back_image_file) {
        $validation->addError('Need Back of  Card');
        $error_count++;
    }

    if ($error_count > 0) {
        $b_show_disconect_message = false;

    } else {
        $tmp_file_path = $abs_us_root.$us_url_root.'tmp/local_uploads';
        $nid = add_waiting($client_id,$profile_id,$front_image_file,$back_image_file,$front_extension,$back_extension,$user,$tmp_file_path);
        $b_show_disconect_message = !upload_local_storage($nid) ;
        $n_waiting_to_be_uploaded = $db->query( "select * from ht_waiting p where p.is_uploaded = 0 and upload_result is null order by p.created_at;",[])->count();

    }


}
?>

<div id="page-wrapper">
    <div class="row">
        <?php if ($b_show_disconect_message) { ?>

            <div class="col-sm-offset-2 col-sm-4 alert alert-info" >
                <strong><i class="fa fa-fw fa-cloud-upload"></i> <?= $n_waiting_to_be_uploaded ?></strong> This will be uploaded to the server later
            </div>



        <?php } elseif($error_count == 0 && $nid) {?>
            <div class=" col-sm-offset-2 col-sm-4 alert alert-success" >
                <strong><i class="fa fa-fw fa-check"></i> Uploaded to be Processed</strong> This has been sent to be transcribed
            </div>
        <?php } ?>
        <div id="form-errors" class="col-sm-offset-2 col-sm-4">
            <?=$validation->display_errors();?>
        </div>
    </div>

	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class=" col-sm-offset-2 col-sm-4">
				<h1 class="page-header">
                    Upload Card
				</h1>
				<!-- Content goes here -->
                <form class="" enctype="multipart/form-data"  action="upload.php" name="uploads" method="post">





                    <!-- List group -->

                    <!-- User id, this is called client_id to distinguish it from a user  -->
                    <div class="form-group">
                        <label for="site_name">User ID</label>
                        <input type="text" class="form-control" name="client_id" id="client_id" value="">
                    </div>

                    <div class="form-group">
                        <label for="site_name">Profile ID</label>
                        <input type="text" class="form-control" name="profile_id" id="profile_id" value="">
                    </div>

                    <div class="form-group">
                        <label for="front_of_card">Front of Card</label>
                        <input type="file" class="form-control" name="front_of_card" id="front_of_card" value="">
                    </div>

                    <div class="form-group">
                        <label for="back_of_card">Back of Card</label>
                        <input type="file" class="form-control" name="back_of_card" id="back_of_card" value="">
                    </div>

                    <input type="hidden" name="csrf" value="<?=Token::generate();?>" />

                    <p><input class='btn btn-primary' type='submit' name="uploads" value='Upload New Card' /></p>
                </form>



				<!-- Content Ends Here -->
			</div> <!-- /.col -->
		</div> <!-- /.row -->
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->
               

<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
