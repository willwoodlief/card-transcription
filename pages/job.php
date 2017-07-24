<?php
//die(var_dump($_REQUEST));
require_once '../users/init.php';

require_once $abs_us_root.$us_url_root.'users/includes/header_not_closed.php';
?>

<link rel="stylesheet" href="../users/js/plugins/darkroomjs/build/darkroom.css">

<style>

    .popover{
        max-width: 1000px; /* Max Width of the popover , make it so its not clipping */
    }

    .enlarge {
        transform: scale(3);
        position:absolute;
        top:275px;
        left:100px;
        z-index: 999;
    }

    .form-group {
        margin-bottom: 5px;
    }

    .form-control {
        padding: 10px 5px;
    }

    .input-job-box {
        padding: 5px;
        font-size: 12px;
        height: 30px;
    }

    .input-job-label {
        font-size: 12px;
    }

    .input-job-group {
        padding-left: 5px;
        padding-right: 5px;
    }

    .large-address {
        position: absolute;
        width: 30em;
        background-color: floralwhite;
        z-index: 1000;
    }

    .address-wrapper {
        padding: 0;
        margin: 0;
    }

    div.duplicate-control {
        background-color: #eaeaea;
    }

    div.duplicate-control div.message {
        display: inline-block;
        margin-top: 1em;
    }

    div.row.duplicate {
        display: none;
    }

    div.duplicate-control.start-duplicate-hidden {
        display: none;
    }
    div.duplicate-control div.duplicate-button-holder {
        margin-top: 1em;
    }

    div.duplicate-control div.dupe-link-container {
        margin-left: 2em;
    }

    div.is-table-row {
        display: table;
        width: 90%;
        margin: auto;
        margin-bottom: .9em;

    }

    div.is-table-row > [class*="col-"] {
        float: none;
        display: table-cell;
        vertical-align: top;
    }

    input[name='email'].duplicate {
        background-color: mediumpurple;
        color: white;
        border: double red 1px;
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

    $token = $_POST['csrf'];
    if (!Token::check($token)) {
        die('Token doesn\'t match!');
    }
    if (!$job->translater->id) { die("Cannot approve something that was not done first");}

    $query = checkForDuplicateEmailsWithUser($job->transcribe->email,$job->job->client_id,2);
    $fields = ['checker_user_id'=>$user->data()->id,'checked_at'=> time()];
    if ($query) {
        $fields['duplicate'] = 1;
        $validation->addError("this is a duplicate: " . $job->transcribe->email );
        $error_count ++;
    } else {
        $fields['duplicate'] = 0;  //clear any potentially other duplicate
    }
    if ($error_count == 0) {
        clearJobViewStamp($jobid);
        if ($user && $user->roles() && in_array("Administrator", $user->roles())) {
            $what = $db->update('ht_jobs', $jobid, $fields);
            call_api($job, $settings->website_url);
            runAfterHook(rtrim($abs_us_root . $us_url_root, "/"), $job->job->id);
            if (!$what) {
                $validation->addError($db->error());
                $error_count++;
            } else {
                Redirect::to($us_url_root . "pages/check.php");
            }

        } elseif ($user && $user->roles() && in_array("Checker", $user->roles())) {
            $what = $db->update('ht_jobs', $jobid, $fields);
            call_api($job, $settings->website_url);
            runAfterHook(rtrim($abs_us_root . $us_url_root, "/"), $job->job->id);
            if (!$what) {
                $validation->addError($db->error());
                $error_count++;
            } else {
                Redirect::to($us_url_root . "pages/check.php");
            }


        } else {
            die('Role that is not checker or admin has submitted wrong form');
        }
    }
}

if(!empty($_POST['bad_scan'])) {
    clearJobViewStamp($jobid);
    $token = $_POST['csrf'];
    if (!Token::check($token)) {
        die('Token doesn\'t match!');
    }


    $fields['fname'] = 'DELETE';
    $fields['lname'] = 'BAD SCAN';
    $fields['transcriber_user_id'] = $user->data()->id;
    $fields['transcribed_at'] =  time();
    $what = $db->update('ht_jobs', $jobid, $fields);
    if (!$what) {
        $validation->addError($db->error());
        $error_count ++;
    }


    if ($error_count == 0) {
        Redirect::to($us_url_root."pages/transcribe.php");
    }


}

if(!empty($_POST['duplicate'])) {
    clearJobViewStamp($jobid);
    $token = $_POST['csrf'];
    if (!Token::check($token)) {
        die('Token doesn\'t match!');
    }
    $email_for_duplicate = to_utf8(Input::get('email_for_duplicate'));
    if (!$email_for_duplicate) {
        $validation->addError("email not passed to duplicate code in hidden input");
        $error_count ++;
    } else {
        $query = checkForDuplicateEmailsWithUser($email_for_duplicate,$job->job->client_id);
        if (!$query) {
            $validation->addError("this is not a duplicate");
            $error_count ++;
        } else {
            //set duplicate flag
            $fields['duplicate'] = 1;
            $fields['fname'] = 'DELETE';
            $fields['lname'] = 'DUPLICATE CARD';
            $fields['email'] = $email_for_duplicate;
            $what = $db->update('ht_jobs', $jobid, $fields);
            if (!$what) {
                $validation->addError($db->error());
                $error_count ++;
            }
        }

        if ($error_count == 0) {
            Redirect::to($us_url_root."pages/transcribe.php");

        }
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
        'title','suit','address','city','state','zip',
        'email','website','work_phone','work_phone_extension','cell_phone','fax',
        'skype','company','country','twitter','home_phone','other_phone','notes'];

    $fields = [];



    foreach($fields_to_check as $key=>$field) {
        $val = to_utf8(Input::get($field));
       // echo "get {$key} => {$field} == {$val}<br>";
        if (trim(Input::get($field))) {
            $fields[$field] = Input::get($field);
        } else {
            $fields[$field] = null;
        }
    }



    $query = checkForDuplicateEmailsWithUser($fields['email'],$job->job->client_id);
    if ($query) {
        $validation->addError("this is a duplicate: " . $fields['email'] );
        $error_count ++;
    } else {
        $fields['duplicate'] = 0;  //clear any potentially other duplicate
    }

    if ($error_count == 0) {
        // print_nice($fields);
        // print_nice($_REQUEST);
        $fields['modified_at'] = time();
        $what = $db->update('ht_jobs', $jobid, $fields);
        if (!$what) {

            $validation->addError($db->error());
            $error_count ++;
        }
    } else {
        $validation->addError("Could not save record due to previous errors" );
        $error_count ++;
    }




    if ($error_count == 0) {
        $tag_string = to_utf8(Input::get('tag_string'));
        $error = save_tag_string($jobid, $tag_string);
        if ($error) {
            $validation->addError($error);
            $error_count++;
        }
    }

    if ($error_count == 0) {
        //get the role of the user, and update based on user role
        if ($user && $user->roles()  && in_array("Administrator", $user->roles())) {
            if ($job->translater->id) {
                $what = $db->update('ht_jobs', $jobid, ['checker_user_id'=>$user->data()->id,'checked_at'=> time()]);
                call_api($job,$settings->website_url);
                runAfterHook(rtrim($abs_us_root.$us_url_root,"/"),$job->job->id);
            } else {
                $what = $db->update('ht_jobs', $jobid, ['transcriber_user_id'=>$user->data()->id,'transcribed_at'=> time()]);
            }

            if (!$what) {
                $validation->addError($db->error());
                $error_count ++;
            } else {
                Redirect::to($us_url_root."pages/transcribe.php");
            }

        }
        elseif ($user && $user->roles()  && in_array("Checker", $user->roles())) {
            if ($job->translater->id) {
                $what = $db->update('ht_jobs', $jobid, ['checker_user_id'=>$user->data()->id,'checked_at'=> time()]);
                call_api($job,$settings->website_url);
                runAfterHook(rtrim($abs_us_root.$us_url_root,"/"),$job->job->id);
            } else {
                $what = $db->update('ht_jobs', $jobid, ['transcriber_user_id'=>$user->data()->id,'transcribed_at'=> time()]);
            }

            if (!$what) {
                $validation->addError($db->error());
                $error_count ++;
            } else {
                Redirect::to($us_url_root."pages/transcribe.php");
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
    $frame_css = '';
} else {
    #redirect
    #Redirect::to($redirect_timeout_url);
    # set a read only flag for all fields
    $validation->addError("Screen Is Now Read Only for this job");
    $error_count ++;
    $frame_css = 'pointer-events: none;';

}


//get height for both images:
$heightForFrame = $job->images->edit_side_b->height;
if ($heightForFrame < $job->images->edit_side_a->height ) {
    $heightForFrame = $job->images->edit_side_a->height;
}

$csrf = Token::generate();
?>

<style>
    div.not-duplicate {
    <?php if ($job->job->duplicate > 0) { ?>
        display: none;
    <?php } ?>
    }

    div.duplicate {
    <?php if ($job->job->duplicate == 0) { ?>
        display: none;
    <?php } ?>
    }

    div.duplicate-control {
        display: none;
    }
</style>

<div id="page-wrapper" style="">
    <?php if ($error_count> 0) { ?>
    <div class="row">
        <div id="form-errors" class="col-xs-offset-2 col-xs-4">
            <?=$validation->display_errors();?>
        </div>
    </div>
    <?php } ?>

    <?php if ($job->job->duplicate > 0) {
        $maybe_hidden_class = '';
    } else {
        $maybe_hidden_class = 'hide-top-control';
    }
    ?>
    <div class="container-fluid">
        <div class="row not-duplicate">
            <?php if ($b_is_checker && $job->translater->id) { ?>
                <div class="panel panel-default col-xs-8" >
                    <div class="panel-body centerBlock">
                        <div class="row">
                            <div class="col-xs-3" >
                                <h3 style="margin-top: 0">Approve This Transcription</h3>
                            </div>
                            <div class="col-xs-4">
                                <form class="a-job-form" action="job.php" name="job" id="approve-form" method="post">
                                    <input class='btn btn-primary' type='submit' name="approve" value='Approve Without Changing' />
                                    <input type="hidden" name="csrf" value="<?=$csrf?>" />
                                    <input type="hidden" name="jobid" value="<?=$job->job->id ?>" />
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php } ?>
        </div>


		<!-- Page Heading -->
        <div class="row  is-table-row duplicate">
            <div class="panel panel-warning col-xs-3 duplicate-control <?= $maybe_hidden_class ?>" >
                <div class="row duplicate-info">

                    <div class="col-xs-5  message" >
                        Duplicate Detected!
                    </div>
                    <div class="col-xs-7 duplicate-button-holder">
                        <form class="a-job-form" action="job.php" name="job" id="approve-form" method="post">
                            <input class='btn btn-warning' type='submit' name="duplicate" value='Mark As Duplicate' />
                            <input type="hidden" name="csrf" value="<?=$csrf?>" />
                            <input type="hidden" name="jobid" value="<?=$job->job->id ?>" />
                            <input type="hidden" name="email_for_duplicate" id = "email_for_duplicate" value=""" />
                        </form>


                    </div>
                    <div class="row" style="clear: both">
                        <div class="dupe-link-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-xs-1"></div>
            <?php if ($b_is_checker && $job->translater->id) { ?>
            <div class="panel panel-default col-xs-8" >
                <div class="panel-body centerBlock">
                    <div class="row">
                        <div class="col-xs-3" >
                            <h3 style="margin-top: 0">Approve This Transcription</h3>
                        </div>
                        <div class="col-xs-4">
                            <form class="a-job-form" action="job.php" name="job" id="approve-form" method="post">
                                <input class='btn btn-primary' type='submit' name="approve" value='Approve Without Changing' />
                                <input type="hidden" name="csrf" value="<?=$csrf?>" />
                                <input type="hidden" name="jobid" value="<?=$job->job->id ?>" />
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php } ?>
        </div>
        <div class="row " style="">

                    <form class="a-job-form" action="job.php" name="job" id="job-form" method="post" class="">
                        <div class="col-xs-12" style="position:relative">

                            <input type="hidden" name="jobid" value="<?=$job->job->id ?>" >

                            <div class="form-group col-xs-2 input-job-group">
                                <label for="email" class="input-job-label" >Email</label>
                                <input type="text" class="form-control input-job-box" name="email" id="email" value="<?=$job->transcribe->email ?>">
                            </div>

                            <div class="form-group col-xs-1 input-job-group" style="">

                                <label for="fname" class=" control-label input-job-label">First Name</label>
                                <input type="text" class=".input-md a-job-form form-control input-job-box" name="fname" id="fname" value="<?=$job->transcribe->fname ?>">
                            </div>

                            <div class="form-group  col-xs-1 input-job-group">
                                <label for="mname" class="input-job-label">Middle</label>
                                <input type="text" class="form-control input-job-box" name="mname" id="mname" value="<?=$job->transcribe->mname ?>">
                            </div>

                            <div class="form-group  col-xs-1 input-job-group">
                                <label for="lname" class="input-job-label" >Last Name</label>
                                <input type="text" class="form-control input-job-box" name="lname" id="lname" value="<?=$job->transcribe->lname ?>">
                            </div>



                            <div class="form-group  col-xs-1 input-job-group">
                                <label for="suffix" class="input-job-label">Suffix</label>
                                <input type="text" class="form-control input-job-box" name="suffix" id="suffix" value="<?=$job->transcribe->suffix ?>">
                            </div>



                            <div class="form-group col-xs-1 input-job-group">
                                <label for="title" class="input-job-label">Title</label>
                                <input type="text" class="form-control input-job-box" name="title" id="title" value="<?=$job->transcribe->title ?>">
                            </div>

                            <div class="form-group col-xs-2 input-job-group">
                                <label for="company" class="input-job-label" >Company</label>
                                <input type="text" class="form-control input-job-box" name="company" id="company" value="<?=$job->transcribe->company ?>">
                            </div>

                            <div class="form-group col-xs-1 input-job-group">
                                <label for="work_phone" class="input-job-label">Work Phone</label>
                                <input type="text" class="form-control input-job-box" name="work_phone" id="work_phone" value="<?=$job->transcribe->work_phone ?>">
                            </div>

                            <div class="form-group col-xs-1 input-job-group">
                                <label for="work_phone_extension" class="input-job-label">Extension</label>
                                <input type="text" class="form-control input-job-box" name="work_phone_extension" id="work_phone_extension" value="<?=$job->transcribe->work_phone_extension ?>">
                            </div>

                            <div class="form-group col-xs-1 input-job-group">
                                <label for="cell_phone" class="input-job-label">Cell Phone</label>
                                <input type="text" class="form-control input-job-box" name="cell_phone" id="cell_phone" value="<?=$job->transcribe->cell_phone ?>">
                            </div>


                            <div class="form-group  col-xs-1 input-job-group">
                                <label for="suit" class="input-job-label">Suit, Apt</label>
                                <input type="text" class="form-control input-job-box" name="suit" id="suit" value="<?=$job->transcribe->suit ?>">
                            </div>

                            <div class="form-group  col-xs-2 input-job-group">
                                <div class="address-wrapper">
                                    <label for="address" class="input-job-label">Address  <span style="font-size: smaller;white-space: nowrap " class="input-job-label">(street lookup)<span></label>
                                    <input type="text" class="form-control input-job-box" name="address" id="address" value="<?=$job->transcribe->address ?>">
                                </div>

                            </div>

                            <div class="form-group  col-xs-1 input-job-group">
                                <label for="zip"  class="input-job-label" style="white-space: nowrap "  >Zip <span style="font-size: smaller;white-space: nowrap " class="input-job-label">(auto fill)<span> </label>
                                <input type="text" class="form-control input-job-box" name="zip" id="zip" value="<?=$job->transcribe->zip ?>">

                            </div>

                            <div class="form-group col-xs-1 input-job-group">
                                <label for="city" class="input-job-label">City</label>
                                <input type="text" class="form-control input-job-box" name="city" id="city" value="<?=$job->transcribe->city ?>">
                            </div>

                            <div class="form-group col-xs-1 input-job-group">
                                <label for="state" class="input-job-label">State</label>
                                <input type="text" class="form-control input-job-box" name="state" id="state" value="<?=$job->transcribe->state ?>">
                            </div>

                            <div class="form-group col-xs-1 input-job-group">
                                <label for="country" class="input-job-label">Country</label>
                                <input type="text" class="form-control input-job-box" name="country" id="country" value="<?=$job->transcribe->country ?>">
                            </div>

                            <div class="form-group col-xs-1 input-job-group">
                                <label for="home_phone" class="input-job-label">Home Phone</label>
                                <input type="text" class="form-control input-job-box" name="home_phone" id="home_phone" value="<?=$job->transcribe->home_phone ?>">
                            </div>

                            <div class="form-group col-xs-1 input-job-group">
                                <label for="other_phone" class="input-job-label">Other Phone</label>
                                <input type="text" class="form-control input-job-box" name="other_phone" id="other_phone" value="<?=$job->transcribe->other_phone ?>">
                            </div>

                            <div class="form-group col-xs-1 input-job-group">
                                <label for="fax"  class="input-job-label">Fax</label>
                                <input type="text" class="form-control input-job-box" name="fax" id="fax" value="<?=$job->transcribe->fax ?>">
                            </div>

                            <div class="form-group col-xs-1 input-job-group">
                                <label for="skype" class="input-job-label">Skype</label>
                                <input type="text" class="form-control input-job-box" name="skype" id="skype" value="<?=$job->transcribe->skype ?>">
                            </div>

                            <div class="form-group col-xs-1 input-job-group">
                                <label for="twitter" class="input-job-label">Twitter</label>
                                <input type="text" class="form-control input-job-box" name="twitter" id="twitter" value="<?=$job->transcribe->twitter ?>">
                            </div>

                        </div>


                        <div class="col-xs-12 " style="">


                            <div class="form-group col-xs-2 input-job-group">
                                <label for="website"  class="input-job-label">Website</label>
                                <input type="text" class="form-control input-job-box" name="website" id="website" value="<?=$job->transcribe->website ?>">
                            </div>




                            <div class="form-group col-xs-4 input-job-group">
                                <label for="notes" class="input-job-label">Notes</label>
                                <input type="text" class="form-control input-job-box" name="notes" id="notes" value="<?=$job->transcribe->notes ?>" title="<?=$job->transcribe->notes ?>">
                            </div>

                            <div class="form-group col-xs-4 input-job-group">
                                <label for="tag_string" class="input-job-label">Tags (comma seperated)</label>
                                <input type="text" class="form-control input-job-box" name="tag_string" id="tag_string" value="<?=$job->transcribe->tag_string ?>">
                            </div>

                            <div class="form-group col-xs-1 input-job-group">
                                <label for="bad_scan" class="input-job-label"></label>
                                <input class='btn btn-danger input-job-box' type='submit' name="bad_scan" id="bad_scan" value='Bad Scan' />
                            </div>


                            <div class="form-group col-xs-1 input-job-group">
                                <label for="" class="input-job-label"></label>
                                <input class='btn btn-primary input-job-box' type='submit' name="transcribe" value='Save!' />
                            </div>

                        </div> <!-- Third row -->

                        <div class="col-xs-12" style="">

                        </div> <!-- Fourth row -->


                        <input type="hidden" name="csrf" value="<?=$csrf?>" />


                    </form>

                </div>

        <div class="row">
            <div class="col-xs-6  "  style="background-color: floralwhite;">
                    <div class="panel-group">
                        <div class="panel panel-default img-outer-holder">

                            <div class="panel-body">
                                <?php
                                    if ($job->images->edit_side_a->id) {
                                        $width = $job->images->edit_side_a->width;
                                        $height = $job->images->edit_side_a->height;

                                    } else {
                                        $width = $job->images->org_side_a->width;
                                        $height = $job->images->org_side_a->height;
                                    }


                                ?>
                                <iframe src="edit_job_image.php?jobid=<?= $job->job->id; ?>&side=0&force_original=0" name="side-a-edit" id="side-a-edit" height="<?= $heightForFrame + 250?>px" width="<?= $width + 30 ?>px" style=";border:0 none;<?= $frame_css ?>"></iframe>
                            </div>
                            <div class="panel-footer">
                                <div class="row">
                                    <div class="col-xs-6 col-md-6">
                                        Side A <span style="font-size: smaller;padding-left: 10px"> (edited version or orginal if never edited)</span>
                                    </div>

                                    <div class="col-xs-6 col-md-6">

                                        <div class="btn-group btn-group-justified">
                                            <div class="btn-group">
                                                <button id = "revert-edit-a" type="button" class="btn btn-warning" onclick="reload_a();"
                                                        data-toggle="tooltip" title="This will erase all edited changes and recreate the image again for editing">
                                                    Revert to Original
                                                </button>
                                            </div>
                                            <div class="btn-group">
                                                <button id = "expand-edit-a" type="button"
                                                        onclick="expand_a();" class="btn btn-default "
                                                        data-toggle="tooltip" title="When the image is expanded any click on it will make it normal sized again">
                                                    Expand
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>

                    </div> <!-- end of panel group edited A -->
                </div> <!-- END of 1/2 row -->

            <div class="col-xs-6  "  style="background-color: floralwhite;">
                <div class="panel-group">
                    <div class="panel panel-default img-outer-holder">

                            <div class="panel-body">
                                <?php
                                if ($job->images->edit_side_b->id) {
                                    $width = $job->images->edit_side_b->width;
                                    $height = $job->images->edit_side_b->height;

                                } else {
                                    $width = $job->images->org_side_b->width;
                                    $height = $job->images->org_side_b->height;
                                }

                                ?>
                                <iframe src="edit_job_image.php?jobid=<?= $job->job->id; ?>&side=1&force_original=0" name="side-b-edit" id="side-b-edit" height="<?= $heightForFrame + 250 ?>px" width="<?= $width + 30 ?>px" style="border:0 none;<?= $frame_css ?>"></iframe>
                            </div>
                            <div class="panel-footer">
                            <div class="row">
                                <div class="col-xs-6 col-md-6">
                                    Side B <span style="font-size: smaller;padding-left: 10px"> (edited version or orginal if never edited)</span>
                                </div>

                                <div class="col-xs-6 col-md-6">
                                    <div class="btn-group btn-group-justified">
                                        <div class="btn-group">
                                            <button id = "revert-edit-b" type="button" onclick="reload_b();"
                                                    class="btn btn-warning"
                                                    data-toggle="tooltip" title="This will erase all edited changes and recreate the image again for editing">
                                                Revert to Original
                                            </button>
                                        </div>
                                        <div class="btn-group">
                                            <button id = "expand-edit-b" type="button" onclick="expand_b();"
                                                    class="btn btn-default "
                                                    data-toggle="tooltip"
                                                    title="When the image is expanded any click on it will make it normal sized again">
                                                Expand
                                            </button>
                                        </div>
                                    </div>


                                </div>
                            </div>

                        </div>
                            </div>

            </div> <!-- end of panel group edited A -->
            </div> <!-- END of 1/2 row -->
        </div> <!-- end of row -->

        <div class="row">
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
    </div>

        <div class="row">
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




				<!-- Content Ends Here -->

</div> <!-- /.wrapper -->


<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->
<?php
$real_good_dupe_flag = ( checkForDuplicateEmailsWithUser($job->transcribe->email,$job->job->client_id) ||  $job->job->duplicate > 0);
?>
<script>
    var jobid = <?= $jobid;?>;
    var client_id = "<?= $job->job->client_id;?>";
    var duplicate_flag = <?= $real_good_dupe_flag ? 'true' : 'false';?>;
    var start_view_time = Math.floor(Date.now()/1000);
    var timeout_in_seconds= <?= $settings->view_timeout_seconds ;?>;
    var redirect_timeout_url = '<?= $redirect_timeout_url ;?>';
</script>

<script src="js/auto_zip.js"></script>
<script src="js/auto_duplicate.js"></script>
<script src="js/auto_complete.js"></script>
<script src="js/jquery.phoenix.js"></script>
<script src="js/jobform.js"></script>
<script src="../users/js/jquery.noty.packaged.min.js"></script>
<script src="js/jquery.formatter.min.js"></script>
<script src="js/phone_numbers.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= Config::get('keys/google') ?>&libraries=places&callback=initAutocomplete"
        async defer></script>


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

<?php if (!$can_edit_this_job) { ?>
    <script>
        $(function() {
            $("input[type='text']").prop("disabled", true);
        });
    </script>
<?php } ?>


<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
