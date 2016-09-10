<?php
require_once 'users/init.php';


if ($user && $user->roles()  && in_array("Administrator", $user->roles())) { Redirect::to($us_url_root."pages/status.php"); }
elseif ($user && $user->roles()  && in_array("Checker", $user->roles())) { Redirect::to($us_url_root."pages/check.php"); }
elseif ($user && $user->roles()  && in_array("Uploader", $user->roles())) { Redirect::to($us_url_root."pages/upload.php"); }
elseif ($user && $user->roles()  && in_array("Transcriber", $user->roles())) { Redirect::to($us_url_root."pages/transcribe.php"); }
else {
    require_once $abs_us_root.$us_url_root.'users/includes/header.php';
    if ($settings->site_offline==1){die("The site is currently offline.");}
    require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
    ?>

    <div id="page-wrapper">
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="row">
                <div class="col-sm-12">
                    <h1 class="page-header">
                        Welcome to Card Transcription
                    </h1>
                    <!-- Content goes here -->

                    Log in to use this application


                    <!-- Content Ends Here -->
                </div> <!-- /.col -->
            </div> <!-- /.row -->
        </div> <!-- /.container -->
    </div> <!-- /.wrapper -->


    <?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

    <!-- Place any per-page javascript here -->

    <?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>


<?php } ?>







               
