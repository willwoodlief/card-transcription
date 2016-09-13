<?php
//die(var_dump($_REQUEST));
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/header_not_closed.php';
?>

<link rel="stylesheet" href="../users/js/plugins/darkroomjs/build/darkroom.css">

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


if ($user && $user->roles()  && in_array("Administrator", $user->roles())) {$b_is_checker = true;}
elseif ($user && $user->roles()  && in_array("Checker", $user->roles())) {$b_is_checker = true;}


if(!empty($_POST['approve'])) {
    if (!$job->translater->id) { die("Cannot approve something that was not done first");}

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


    $token = $_POST['csrf'];
    if (!Token::check($token)) {
         die('Token doesn\'t match!');
    }
    $fields_to_check = [
        'fname','mname','lname','suffix',
        'designations','address','city','state','zip',
        'email','website','phone','cell_phone','fax','skype'];

    $fields = [];

    foreach($fields_to_check as $key=>$field) {
        $val = Input::get($field);
        echo "get {$key} => {$field} == {$val}<br>";
        if (Input::get($field)) {
            $fields[$field] = Input::get($field);
        }
    }


    print_nice($fields);
    print_nice($_REQUEST);
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
                        <form class="" action="job.php" name="job" method="post">
                            <h3>Approve This Transcription</h3>
                            <input type="hidden" name="csrf" value="<?=Token::generate();?>" />
                            <input type="hidden" name="jobid" value="<?=$job->job->id ?>" />

                            <p><input class='btn btn-primary' type='submit' name="approve" value='Approve Without Changing' /></p>
                        </form>
                        <hr>

                    <?php } ?>

                    <form class="" action="job.php" name="job" method="post">
                        <h3>Edit Transcription</h3>
                        <input type="hidden" name="jobid" value="<?=$job->job->id ?>" >

                        <div class="form-group">
                            <label for="fname">First Name</label>
                            <input type="text" class="form-control" name="fname" id="fname" value="<?=$job->transcribe->fname ?>">
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
                                        Side A (edited version or orginal if never edited)
                                    </div>

                                    <div class="col-xs-6 col-md-3">
                                        <button id = "revert-edit-a" type="button" class="btn btn-warning" onclick="reload_a();">Revert to Original</button>
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
                                <iframe src="edit_job_image.php?jobid=<?= $job->job->id; ?>&side=0&force_original=0" name="side-a-edit" id="side-a-edit" height="<?= $max + 50 ?>px" width="<?= $max + 70 ?>px" style="border:0 none;"></iframe>
                            </div>
                        </div>


                        <div class="panel panel-default img-outer-holder">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-6 col-md-6">
                                        Side B (edited version or orginal if never edited)
                                    </div>

                                    <div class="col-xs-6 col-md-3">
                                        <button id = "revert-edit-b" type="button" onclick="reload_b();" class="btn btn-warning">Revert to Original</button>
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
<script src="js/auto_zip.js"></script>
<script>
    function reload_a() {
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
</script>


<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
