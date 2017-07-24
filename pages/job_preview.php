<?php
//die(var_dump($_REQUEST));
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/header_not_closed_no_frame.php';

$job = json_decode(json_encode($info_hash))[0];

?>
    <title>Job <?= $job->job->id ?> Preview </title>
    <style>
        div.job-map span.header {
            font-weight: bold;
            color: #323232;
            padding-left: 1em;
            text-decoration: underline;
        }

        div.job-map span.reveal {
            color: black;
        }

        body {
            width: 400px;
        }

        body,html {
            background-color: white;
        }

        .picme {
            margin-left: 5px;
            margin-top: 5px;
        }

        .picte {
            margin-right: 5px;
            margin-top: 5px;
        }


    </style>
</head>

<body>


<div id="page-wrapper-2" class="job-map" style="">

    <div class="row" style="background-color: lightslategray">
        <div class="col-xs-6 col-sm-6">
            <img src="<?= $job->images->edit_side_a->url?>" class="thumbnail img-responsive picme" alt="Front of Card" title="Front of Card">
        </div>

        <div class="col-xs-6 col-sm-6">
            <img src="<?= $job->images->edit_side_b->url?>" class="thumbnail img-responsive picte" alt="Back of Card" title="Back of Card">
        </div>
    </div>
    <div class="row" style="background-color: lightgrey">
        <div class="col-xs-6 col-sm-6">
            <span class="header">Name</span> <br>
            <span class="reveal"><?= $job->transcribe->fname  ?></span> <br>
            <span class="reveal"><?=  $job->transcribe->mname ?></span> <br>
            <span class="reveal"><?=  $job->transcribe->lname ?></span> <br>
        </div>

        <div class="col-xs-6 col-sm-6">
            <span class="header">Email</span> <br>
            <span class="reveal"><a href="mailto:<?= $job->transcribe->email ?>"> <?= $job->transcribe->email ?> </a></span>
        </div>


    </div>  <!-- end Row -->

    <div class="row" style="background-color: lightslategray">

        <div class="col-xs-6 col-sm-6">
            <span class="header">Company</span> <br>
            <span class="reveal"> <?= $job->transcribe->company ?> </span> <br>
            <span class="reveal"> <?= $job->transcribe->website ?> </span>
        </div>

        <div class="col-xs-6 col-sm-6">
            <span class="header">Address</span> <br>
            <span class="reveal"><?= $job->transcribe->suit .' '.$job->transcribe->address  ?></span> <br>
            <span class="reveal"><?=  $job->transcribe->city ?></span> <br>
            <span class="reveal"><?=  $job->transcribe->state .' '. $job->transcribe->zip?></span>
        </div>
    </div>  <!-- end Row -->



    <div class="row" style="background-color: lightgrey">

        <div class="col-xs-4 col-sm-4">
            <span class="header">Created</span> <br>
            <span class="reveal"> <?= $job->job->uploader_fname ?> </span> <br>
            <span class="reveal a-timestamp-short-date-time"  data-ts="<?= $job->job->created_timestamp ?>"></span>

        </div>

        <div class="col-xs-4 col-sm-4">
            <span class="header">Transcribed</span> <br>
            <span class="reveal"> <?= $job->translater->lname ?> </span> <br>
            <span class="reveal a-timestamp-short-date-time" data-ts="<?= $job->job->transcribed_timestamp ?>"></span> <br>
        </div>

        <div class="col-xs-4 col-sm-4">
            <span class="header">Checked</span> <br>
            <span class="reveal"> <?= $job->checker->lname ?> </span> <br>
            <span class="reveal a-timestamp-short-date-time" data-ts="<?= $job->job->checked_timestamp ?>"></span> <br>
        </div>


    </div>  <!-- end Row -->

</div> <!-- /.wrapper -->


<!-- Place any per-page javascript here -->

<script>
    var jobid = <?= $jobid;?>;
    var client_id = "<?= $job->job->client_id;?>";
</script>


<?php require_once $abs_us_root.$us_url_root.'users/includes/plain_page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<script src="js/jquery.phoenix.js"></script>

<script src="../users/js/jquery.noty.packaged.min.js"></script>
<script src="../users/js/moment-with-locales.min.js"></script>

<script src="js/timestamp_to_locale.js"></script>




</body>
</html>