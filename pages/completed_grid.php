<?php
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'users/helpers/helpers.php';
require_once $abs_us_root.$us_url_root.'pages/helpers/pages_helper.php';

$db = DB::getInstance();
$settingsQ = $db->query("Select * FROM settings");
$settings = $settingsQ->first();
if ($settings->site_offline==1){
    die("The site is currently offline.");
}
if (!securePage($_SERVER['PHP_SELF'])){die(); }
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="<?=$us_url_root ?>favicon.ico" />

    <link rel="stylesheet" href="../users/js/plugins/SlickGrid/css/smoothness/jquery-ui-1.11.3.custom.css" type="text/css"/>
    <link href="../users/js/plugins/SlickGrid/slick.grid.css" rel="stylesheet" type="text/css">
    <link href="../users/js/plugins/SlickGrid/examples/examples.css" rel="stylesheet" type="text/css">
    <title>Completed Jobs Report</title>
    <style>
        .slick-headerrow-column {
            background: #87ceeb;
            text-overflow: clip;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        .slick-headerrow-column input {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        .slick-column-name,
        .slick-sort-indicator {
            display: inline-block;
            float: left;
            margin-bottom: 100px;
        }
    </style>
</head>
<body style="background-color: floralwhite">
<?php
$completed = get_jobs(null,true,true);
$jobs = json_decode(json_encode($completed));
$completed = [];
for($i=0; $i < sizeof($jobs); $i++) {
    $node = [];
    $node['id'] = $jobs[$i]->job->id;
    $node['client_id'] = $jobs[$i]->job->client_id;
    $node['profile_id'] = $jobs[$i]->job->profile_id;
    $node['uploaded_timestamp'] = $jobs[$i]->job->uploaded_timestamp;
    $node['seconds_for_transcription'] = $jobs[$i]->job->transcribed_timestamp - $jobs[$i]->job->uploaded_timestamp;
    $node['transcriber_name'] = $jobs[$i]->translater->lname;
    $node['checker_name'] = $jobs[$i]->checker->lname;
    $node['seconds_for_checking'] = $jobs[$i]->job->checked_timestamp - $jobs[$i]->job->transcribed_timestamp;
    $node['card_name'] = $jobs[$i]->transcribe->fname.' '.$jobs[$i]->transcribe->lname;
    $node['card_link'] = '<a href="'.$abs_us_web_root.'pages/job.php?jobid=' .$jobs[$i]->job->id.
        '" target="_BLANK"> ' .
        $jobs[$i]->transcribe->fname.' '.$jobs[$i]->transcribe->lname .
        '</a>';
    array_push($completed,$node);
}

//print_nice($abs_us_web_root);
//print_nice($completed);
//print_nice($jobs);


?>
    <div style="width:930px;" style="padding: 0px;margin: 0px">
        <div id="myGrid" style="width:100%;height:500px;padding: 0px;margin: 0px"></div>
    </div>


<script src="../users/js/plugins/SlickGrid/lib/firebugx.js"></script>

<script src="../users/js/plugins/SlickGrid/lib/jquery-1.11.2.min.js"></script>
<script src="../users/js/plugins/SlickGrid/lib/jquery-ui-1.11.3.min.js"></script>
<script src="../users/js/plugins/SlickGrid/lib/jquery.event.drag-2.3.0.js"></script>


<script src="../users/js/plugins/SlickGrid/slick.core.js"></script>
<script src="../users/js/plugins/SlickGrid/slick.dataview.js"></script>
<script src="../users/js/plugins/SlickGrid/plugins/slick.rowselectionmodel.js"></script>
<script src="../users/js/plugins/SlickGrid/slick.grid.js"></script>
<script src="js/human_time_span.js"></script>


<script>
    var dataView;
    var grid;
    var data = <?= json_encode($completed) ;?>;
    var options = {
        enableCellNavigation: true,
        showHeaderRow: true,
        headerRowHeight: 30,
        explicitInitialization: true,
        enableColumnReorder: false
    };
    var columns = [];
    var columnFilters = {};


    var columns = [
        {id: "client_id", name: "User", field: "client_id", width: 120,sortable: true,sort_hint:'alpha'},
        {id: "profile_id", name: "Profile", field: "profile_id", width: 60,sortable: true,sort_hint:'alpha'},
        {id: "card_link", name: "Link", field: "card_link",  width: 120,sortable: true,sort_hint:'use_column',ref_column:'card_name',formatter:link_formatter},
        {id: "uploaded_timestamp", name: "Uploaded Time", field: "uploaded_timestamp", width: 150,sortable: true,sort_hint:'numeric_to_date',formatter:timestamp_formatter},
        {id: "seconds_for_transcription", name: "Processing Time", field: "seconds_for_transcription", width: 120,sortable: true,sort_hint:'numeric_to_span',formatter:seconds_formatter},
        {id: "transcriber_name", name: "Transcriber Name", field: "transcriber_name", width: 120,sortable: true,sort_hint:'alpha'},
        {id: "seconds_for_checking", name: "Checking Time", field: "seconds_for_checking", width: 120,sortable: true,sort_hint:'numeric_to_span',formatter:seconds_formatter},
        {id: "checker_name", name: "Checker Name", field: "checker_name", width: 120,sortable: true,sort_hint:'alpha'}
    ];


    function filter(item) {
        for (var columnId in columnFilters) {
            if (columnId !== undefined && columnFilters[columnId] !== "") {
                var c = grid.getColumns()[grid.getColumnIndex(columnId)];
                var regexp = new RegExp(columnFilters[columnId], "i");
                var test = item[c.field];
                switch (c.sort_hint){
                    case 'numeric_to_date': {
                        var uploaded_dt = new Date(test * 1000);
                        var human_uploaded_dt = uploaded_dt.toLocaleString();
                        test = human_uploaded_dt;
                        break;
                    }
                    case 'numeric_to_span': {
                        test = seconds_to_span(test);
                        break;
                    }
                    case 'use_column' : {
                        test =item[c.ref_column];
                        break;
                    }


                }
                if (test.search(regexp) < 0) {
                    return false;
                }
            }
        }
        return true;
    }

    $(function () {


        dataView = new Slick.Data.DataView();
        grid = new Slick.Grid("#myGrid", dataView, columns, options);
        grid.setSelectionModel(new Slick.RowSelectionModel());

        dataView.onRowCountChanged.subscribe(function (e, args) {
            grid.updateRowCount();
            grid.render();
        });

        dataView.onRowsChanged.subscribe(function (e, args) {
            grid.invalidateRows(args.rows);
            grid.render();
        });

        grid.onClick.subscribe(function (e) {
            var cell = grid.getCellFromEvent(e);
            //console.log(cell.row);
            grid.setSelectedRows([cell.row]);
            e.stopPropagation();

        });


        $(grid.getHeaderRow()).on("change keyup", ":input", function (e) {
            var columnId = $(this).data("columnId");
            if (columnId != null) {
                columnFilters[columnId] = $.trim($(this).val());
                dataView.refresh();
            }
        });

        grid.onHeaderRowCellRendered.subscribe(function(e, args) {
            $(args.node).empty();
            $("<input type='text'>")
                .data("columnId", args.column.id)
                .val(columnFilters[args.column.id])
                .appendTo(args.node);
        });


        //var sortcol = null;
        grid.onSort.subscribe(function (e, args) {
            //currentSortCol = args.sortCol;
            function comparer(a, b) {
                var x = a[sortcol], y = b[sortcol];
                return (x == y ? 0 : (x > y ? 1 : -1));
            }

            var sortcol = args.sortCol.field;
            dataView.sort(comparer, args.sortAsc);
           // isAsc = args.sortAsc;
           // dataView.refresh();
           // grid.invalidateAllRows();
           // grid.render();

        });



        grid.init();

        dataView.beginUpdate();
        dataView.setItems(data);
        dataView.setFilter(filter);
        dataView.endUpdate();
    });

    function link_formatter( row, cell, value, columnDef, dataContext ) {
        return dataContext['card_link'];
    }

    function timestamp_formatter( row, cell, value, columnDef, dataContext ) {
        var uploaded_dt = new Date(value * 1000);
        var human_uploaded_dt = uploaded_dt.toLocaleString();
        return human_uploaded_dt;
    }

    function seconds_to_span(seconds) {
        return millisecondsToStr(seconds* 1000).toString();
    }
    function seconds_formatter(row, cell, value, columnDef, dataContext ) {
        return seconds_to_span(value);
    }


</script>
</body>
</html>
