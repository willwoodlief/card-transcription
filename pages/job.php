<?php
//die(var_dump($_REQUEST));
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/header_not_closed.php';
?>

<link rel="stylesheet" href="../users/js/plugins/darkroomjs/build/darkroom.css">

<style>
    .enlarge {
        transform: scale(3);
        position:absolute;
        top:275px;
        left:100px;
        z-index: 999;
    }
</style>

</head>
<body>
<?php
require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';



if (!securePage($_SERVER['PHP_SELF'])){die();}
if ($settings->site_offline==1){die("The site is currently offline.");}

#get the job id, if not job id here then die
$job_id_string =  Input::get('jobid');
if (!$job_id_string) {
    die('Need job id');
}

$jobid =  intval(Input::get('jobid'));
$info_hash = get_jobs($jobid);
if (empty($info_hash)) {
    die('Cannot find Job ID');
}

$job = json_decode(json_encode($info_hash))[0];

//print_nice($job);
$validation = new Validate();
$error_count = 0;

// get which this user is, he is either a transcriber or some kind of checker (admin or checker)
$b_is_checker = false;
$redirect_timeout_url = 'transcribe.php';

if ($user && $user->roles()  && in_array("Administrator", $user->roles())) {
    $b_is_checker = true;
    $redirect_timeout_url = 'check.php';
}
elseif ($user && $user->roles()  && in_array("Checker", $user->roles())) {
    $b_is_checker = true;
    $redirect_timeout_url = 'check.php';

}


if(!empty($_POST['approve'])) {

    if (!$job->translater->id) { die("Cannot approve something that was not done first");}
    clearJobViewStamp($jobid);
    if ($user && $user->roles()  && in_array("Administrator", $user->roles())) {
        $what = $db->update('ht_jobs', $jobid, ['checker_user_id'=>$user->data()->id,'checked_at'=> time()]);
        if (!$what) {
            $validation->addError($db->error());
            $error_count ++;
        } else {
            Redirect::to($us_url_root."pages/status.php");
        }

    }
    elseif ($user && $user->roles()  && in_array("Checker", $user->roles())) {
        $what = $db->update('ht_jobs', $jobid, ['checker_user_id'=>$user->data()->id,'checked_at'=> time()]);
        if (!$what) {
            $validation->addError($db->error());
            $error_count ++;
        } else {
            Redirect::to($us_url_root."pages/check.php");
        }


    } else {
        die('Role that is not checker or admin has submitted wrong form');
    }
}

//print_nice($job);
if(!empty($_POST['transcribe'])) {

    clearJobViewStamp($jobid);
    $token = $_POST['csrf'];
    if (!Token::check($token)) {
         die('Token doesn\'t match!');
    }
    $fields_to_check = [
        'fname','mname','lname','suffix',
        'designations','address','city','state','zip',
        'email','website','phone','cell_phone','fax','skype','other_category','other_value'];

    $fields = [];

    foreach($fields_to_check as $key=>$field) {
        $val = to_utf8(Input::get($field));
       // echo "get {$key} => {$field} == {$val}<br>";
        if (Input::get($field)) {
            $fields[$field] = Input::get($field);
        }
    }


   // print_nice($fields);
   // print_nice($_REQUEST);
    $fields['modified_at'] = time();
    $what = $db->update('ht_jobs', $jobid, $fields);
    if (!$what) {

        $validation->addError($db->error());
        $error_count ++;
    }

    if ($error_count == 0) {
        //get the role of the user, and update based on user role
        if ($user && $user->roles()  && in_array("Administrator", $user->roles())) {
            if ($job->translater->id) {
                $what = $db->update('ht_jobs', $jobid, ['checker_user_id'=>$user->data()->id,'checked_at'=> time()]);
            } else {
                $what = $db->update('ht_jobs', $jobid, ['transcriber_user_id'=>$user->data()->id,'transcribed_at'=> time()]);
            }

            if (!$what) {
                $validation->addError($db->error());
                $error_count ++;
            } else {
                Redirect::to($us_url_root."pages/status.php");
            }

        }
        elseif ($user && $user->roles()  && in_array("Checker", $user->roles())) {
            if ($job->translater->id) {
                $what = $db->update('ht_jobs', $jobid, ['checker_user_id'=>$user->data()->id,'checked_at'=> time()]);
            } else {
                $what = $db->update('ht_jobs', $jobid, ['transcriber_user_id'=>$user->data()->id,'transcribed_at'=> time()]);
            }

            if (!$what) {
                $validation->addError($db->error());
                $error_count ++;
            } else {
                Redirect::to($us_url_root."pages/check.php");
            }


        } else {
            $what = $db->update('ht_jobs', $jobid, ['transcriber_user_id'=>$user->data()->id,'transcribed_at'=> time()]);
            if (!$what) {
                $validation->addError($db->error());
                $error_count ++;
            } else {
                Redirect::to($us_url_root."pages/transcribe.php");
            }
        }


    }
}

//add a timestamp to the page so that we can filter it out of the ready to do lists
$can_edit_this_job = canEditJob($user,$jobid);
if ($can_edit_this_job) {
    addJobViewStamp($user,$jobid);
} else {
    #redirect
    Redirect::to($redirect_timeout_url);
}

?>

<div id="page-wrapper" style="">
    <div class="row">
        <div id="form-errors" class="col-sm-offset-2 col-sm-4">
            <?=$validation->display_errors();?>
        </div>
    </div>
	<div class="container-fluid">
		<!-- Page Heading -->

				<!-- Content goes here -->
        <div class="row">

                <div class="col-xs-6 col-sm-3 col-md-3 col-lg-3" style="/*height:600px;overflow-y: scroll;*/">

                    <?php if ($b_is_checker && $job->translater->id) { ?>
                        <form class="a-job-form" action="job.php" name="job" id="approve-form" method="post">
                            <h3>Approve This Transcription</h3>
                            <input type="hidden" name="csrf" value="<?=Token::generate();?>" />
                            <input type="hidden" name="jobid" value="<?=$job->job->id ?>" />

                            <p><input class='btn btn-primary' type='submit' name="approve" value='Approve Without Changing' /></p>
                        </form>
                        <hr>

                    <?php } ?>

                    <form class="a-job-form" action="job.php" name="job" id="job-form" method="post">
                        <h3>Edit Transcription</h3>
                        <input type="hidden" name="jobid" value="<?=$job->job->id ?>" >

                        <div class="form-group">
                            <label for="fname">First Name</label>
                            <input type="text" class="a-job-form form-control" name="fname" id="fname" value="<?=$job->transcribe->fname ?>">
                        </div>

                        <div class="form-group">
                            <label for="mname">Middle Initial</label>
                            <input type="text" class="form-control" name="mname" id="mname" value="<?=$job->transcribe->mname ?>">
                        </div>

                        <div class="form-group">
                            <label for="lname">Last Name</label>
                            <input type="text" class="form-control" name="lname" id="lname" value="<?=$job->transcribe->lname ?>">
                        </div>



                        <div class="form-group">
                            <label for="suffix">Suffix</label>
                            <input type="text" class="form-control" name="suffix" id="suffix" value="<?=$job->transcribe->suffix ?>">
                        </div>

                        <div class="form-group">
                            <label for="designations">Designations</label>
                            <input type="text" class="form-control" name="designations" id="designations" value="<?=$job->transcribe->designations ?>">
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" name="address" id="address" value="<?=$job->transcribe->address ?>">
                        </div>

                        <div class="form-group">
                            <label for="zip">Zip <span style="font-size: smaller">(auto fills in city and state)<span> </label>
                            <input type="text" class="form-control" name="zip" id="zip" value="<?=$job->transcribe->zip ?>">
                        </div>

                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" class="form-control" name="city" id="city" value="<?=$job->transcribe->city ?>">
                        </div>

                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" class="form-control" name="state" id="state" value="<?=$job->transcribe->state ?>">
                        </div>



                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" class="form-control" name="email" id="email" value="<?=$job->transcribe->email ?>">
                        </div>

                        <div class="form-group">
                            <label for="website">Website</label>
                            <input type="text" class="form-control" name="website" id="website" value="<?=$job->transcribe->website ?>">
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control" name="phone" id="phone" value="<?=$job->transcribe->phone ?>">
                        </div>

                        <div class="form-group">
                            <label for="cell_phone">Cell Phone</label>
                            <input type="text" class="form-control" name="cell_phone" id="cell_phone" value="<?=$job->transcribe->cell_phone ?>">
                        </div>

                        <div class="form-group">
                            <label for="fax">Fax</label>
                            <input type="text" class="form-control" name="fax" id="fax" value="<?=$job->transcribe->fax ?>">
                        </div>

                        <div class="form-group">
                            <label for="skype">Skype</label>
                            <input type="text" class="form-control" name="skype" id="skype" value="<?=$job->transcribe->skype ?>">
                        </div>

                        <div class="form-group">
                            <label for="other_category">Other Category</label>
                            <input type="text" class="form-control" name="other_category" id="other_category" value="<?=$job->transcribe->other_category ?>">
                        </div>

                        <div class="form-group">
                            <label for="other_value">Other Value</label>
                            <input type="text" class="form-control" name="other_value" id="other_value" value="<?=$job->transcribe->other_value ?>">
                        </div>

                        <input type="hidden" name="csrf" value="<?=Token::generate();?>" />

                        <p><input class='btn btn-primary' type='submit' name="transcribe" value='Save Transcription' /></p>
                    </form>


                </div>
                <div class="col-sm-9  col-md-9  col-lg-9 "  style="background-color: floralwhite;">
                    <div class="panel-group">
                        <div class="panel panel-default img-outer-holder">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-6 col-md-6">
                                        Side A (edited version or orginal if never edited) <br>
                                        When the image is expanded any click on it will make it normal sized again
                                    </div>

                                    <div class="col-xs-6 col-md-6">

                                        <div class="btn-group btn-group-justified">
                                            <div class="btn-group">
                                                <button id = "revert-edit-a" type="button" class="btn btn-warning" onclick="reload_a();">Revert to Original</button>
                                            </div>
                                            <div class="btn-group">
                                                <button id = "expand-edit-a" type="button" onclick="expand_a();" class="btn btn-default ">Expand</button>
                                            </div>
                                        </div>
                                    </div>
                                 </div>


                            </div>
                            <div class="panel-body">
                                <?php
                                    if ($job->images->edit_side_a->id) {
                                        $width = $job->images->edit_side_a->width;
                                        $height = $job->images->edit_side_a->height;

                                    } else {
                                        $width = $job->images->org_side_a->width;
                                        $height = $job->images->org_side_a->height;
                                    }

                                    $max = $width;
                                    if ($height > $max) {$max = $height;}
                                    if ($max < 400) { $max = 400;}
                                ?>
                                <iframe src="edit_job_image.php?jobid=<?= $job->job->id; ?>&side=0&force_original=0" name="side-a-edit" id="side-a-edit" height="<?= $max + 50 ?>px" width="<?= $max + 70 ?>px" style=";border:0 none;"></iframe>
                            </div>
                        </div>


                        <div class="panel panel-default img-outer-holder">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-6 col-md-6">
                                        Side B (edited version or orginal if never edited)<br>
                                        When the image is expanded any click on it will make it normal sized again
                                    </div>

                                    <div class="col-xs-6 col-md-6">
                                        <div class="btn-group btn-group-justified">
                                            <div class="btn-group">
                                                <button id = "revert-edit-b" type="button" onclick="reload_b();" class="btn btn-warning">Revert to Original</button>
                                            </div>
                                            <div class="btn-group">
                                                <button id = "expand-edit-b" type="button" onclick="expand_b();" class="btn btn-default ">Expand</button>
                                            </div>
                                        </div>


                                    </div>
                                </div>

                            </div>
                            <div class="panel-body">
                                <?php
                                if ($job->images->edit_side_b->id) {
                                    $width = $job->images->edit_side_b->width;
                                    $height = $job->images->edit_side_b->height;

                                } else {
                                    $width = $job->images->org_side_b->width;
                                    $height = $job->images->org_side_b->height;
                                }

                                $max = $width;
                                if ($height > $max) {$max = $height;}
                                if ($max < 400) { $max = 400;}
                                ?>
                                <iframe src="edit_job_image.php?jobid=<?= $job->job->id; ?>&side=1&force_original=0" name="side-b-edit" id="side-b-edit" height="<?= $max + 50 ?>px" width="<?= $max + 70 ?>px" style="border:0 none;"></iframe>
                            </div>
                            </div>
                        </div>


                        <div class="panel panel-default img-outer-holder">
                            <div class="panel-heading">Side A (Original Version)</div>
                            <div class="panel-body">
                                <img src="<?=$job->images->org_side_a->url?>"
                                     width="<?=$job->images->org_side_a->width?>"
                                     height="<?=$job->images->org_side_a->height?>"
                                     name = "side-a-origonal"
                                />
                            </div>
                        </div>
                        <div class="panel panel-default img-outer-holder">
                            <div class="panel-heading">Side B (Original Version)</div>
                            <div class="panel-body">
                                <img src="<?=$job->images->org_side_b->url?>"
                                     width="<?=$job->images->org_side_b->width?>"
                                     height="<?=$job->images->org_side_b->height?>"
                                     name = "side-b-origonal"
                                />
                            </div>
                        </div>
                    </div>

                </div>

        </div>



				<!-- Content Ends Here -->

</div> <!-- /.wrapper -->
               

<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<script>
    var jobid = <?= $jobid;?>;
    var start_view_time = Math.floor(Date.now()/1000);
    var timeout_in_seconds= <?= $settings->view_timeout_seconds ;?>;
    var redirect_timeout_url = '<?= $redirect_timeout_url ;?>';
</script>

<script src="js/auto_zip.js"></script>
<script src="js/jquery.phoenix.js"></script>
<script src="js/jobform.js"></script>
<script src="../users/js/jquery.noty.packaged.min.js"></script>

<script>
    function reload_a() {
        if (confirm("This will replace the edited image with the original image, but changes will not take affect until you save it again.") == true) {

        } else {
           return;
        }
        var new_height = $('#side-a-origonal').attr('height');
        var new_width = $('#side-a-origonal').attr('width');
        var max = new_height;
        if (max < new_width) {
            max = new_width;
        }
        if (max < 400) { max = 400;}
        new_height = max + 50;
        new_width = max + 70;
        $('#side-a-edit').attr( 'src', "edit_job_image.php?jobid=<?= $job->job->id; ?>&side=0&force_original=1").
            width(new_width).height(new_height);
    }

    function reload_b() {
        if (confirm("This will replace the edited image with the original image, but changes will not take affect until you save it again.") == true) {

        } else {
            return;
        }
        var new_height = $('#side-b-origonal').attr('height');
        var new_width = $('#side-b-origonal').attr('width');
        var max = new_height;
        if (max < new_width) {
            max = new_width;
        }
        if (max < 400) { max = 400;}
        new_height = max + 50;
        new_width = max + 70;
        $('#side-b-edit').attr( 'src', "edit_job_image.php?jobid=<?= $job->job->id; ?>&side=1&force_original=1").
            width(new_width).height(new_height);
    }


    function expand_b() {
        var iframe = $('#side-b-edit');
        iframe.addClass('enlarge');
    }

    function expand_a() {
        var iframe = $('#side-a-edit');
        iframe.addClass('enlarge');
    }

    function get_iframe_clicks(frame) {
         var iframe = $('#'+frame);
         // alert(iframe.attr( 'src'));
          iframe.removeClass('enlarge');
    }


</script>


<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
