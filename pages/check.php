<?php
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/header.php';
require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';
?>

<?php if (!securePage($_SERVER['PHP_SELF'])){die();}
if ($settings->site_offline==1){die("The site is currently offline.");}?>

<div id="page-wrapper">
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="row">
            <div class="col-xs-12 col-sm-10 col-sm-offset-2 col-md-offset-1 col-md-9 col-lg-offset-1 col-lg-8 " >
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Transcriptions Done</h3>
                    </div>
                    <div class="panel-body jobs-container">
                        <ul id="job-list">
                            <li id="default-job" class="normal"> Job</li>
                            <li id="" class="urgant" > Job</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
        <!-- Content goes here -->




        <!-- Content Ends Here -->
    </div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script src="js/load_job_panel.js"></script>
<script src="js/check_job_feed.js"></script>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
