$(function(){
    get_jobs_for_transcription();
});

function get_jobs_for_transcription() {
    $.get( "get_jobs_as_json.php", { status: "not_started" },
        function( data ) {
            load_job_panel(data,'t');
        },
        "json"  );

}

