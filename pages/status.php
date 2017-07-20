<?php
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/header.php';
require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';
require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
?>

<?php if (!securePage($_SERVER['PHP_SELF'])){die();}
if ($settings->site_offline==1){die("The site is currently offline.");}


?>

<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class="col-sm-12">
				<h1 class="page-header">
					Status Page
				</h1>
				<!-- Content goes here -->
                <div id="upload-section"  >
                    <ul class="nav nav-tabs " id="report-tabs">
                        <li class="nav active"><a href="#completed-jobs" data-toggle="tab">Completed Jobs</a></li>
                        <li class="nav"><a href="#waiting-for-checks" data-toggle="tab">Waiting Checks</a></li>
                        <li class="nav"><a href="#waiting-for-trans" data-toggle="tab">Waiting Transcription</a></li>
                        <li class="nav"><a href="#waiting-for-dupes" data-toggle="tab">Marked Duplicates</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="completed-jobs" >
                            <iframe src="completed_grid.php" name="completed-grid-table" id="completed-grid-table" height="550px" width="950px" style="border:0 none;"></iframe>

                        </div>

                        <div class="tab-pane fade" id="waiting-for-checks" data-src="waiting_checks_grid.php" >
                            <iframe src="" id="waiting-checks-grid-table" height="550px" width="850px" style="border:0 none;"></iframe>
                        </div>

                        <div class="tab-pane fade" id="waiting-for-trans" data-src="waiting_trans_grid.php">
                            <iframe src="" id="waiting-for-trans-grid-table" height="550px" width="720px" style="border:0 none;"></iframe>
                        </div>

                        <div class="tab-pane fade" id="waiting-for-dupes" data-src="duplicate_grid.php">
                            <iframe src="" id="waiting-for-duplicate-grid-table" height="550px" width="950px" style="border:0 none;"></iframe>
                        </div>
                    </div>
                </div>




				<!-- Content Ends Here -->
			</div> <!-- /.col -->
		</div> <!-- /.row -->
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->



               

<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script src="js/lazy_load_tabs.js"></script>



<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
