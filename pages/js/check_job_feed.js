$(function(){
    get_jobs_for_checking();
});

function get_jobs_for_checking() {
    $.get( "get_jobs_as_json.php", { status: "transcribed_not_checked" },
        function( data ) {
            load_job_panel(data,'c');  //c is for check flag
        },
        "json"  );

}

