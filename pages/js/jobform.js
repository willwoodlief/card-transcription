$( function() {

$('.form-control').phoenix({
    namespace: 'phoenixStorage-job-' + jobid,
    webStorage: 'sessionStorage',
    maxItems: 100,
    saveInterval: 1000,
    clearOnSubmit: '.a-job-form',
    keyAttributes: ['tagName', 'id']
});


$('.a-job-form').submit(function(e){
 //   $('.form-control').phoenix('remove');
});

    $('.form-control').change(function() {
        start_view_time = Date.now()/1000;
    });

    setInterval(doTimeView, 60000);


    $(window).on('beforeunload', function(){
        clear_alive();
    });

    $(document).click(function() {
        start_view_time = Date.now()/1000;
    });
});

function doTimeView() {
    var time_diff = Date.now()/1000 - start_view_time;
    if (time_diff > timeout_in_seconds) {
        window.location = redirect_timeout_url;
        return;
    } else {
        ping_alive();
    }
}




function clear_alive() {
    $.get( "clear_alive.php", { jobid: jobid },
        function( data ) {
            if (data.status == 'ok') {

            } else {

            }

        },
        "json"  ).fail(function() {
        show_error_message('Could not connect to server');
    });
}

function ping_alive() {
    $.get( "ping_alive.php", { jobid: jobid },
        function( data ) {
            if (data.status == 'ok') {

            } else {

            }

        },
        "json"  ).fail(function() {
        show_error_message('Could not connect to server');
    });
}

function show_error_message(message) {
    $(".main-header").noty({
        text: message,
        type: 'error',
        dismissQueue: true,
        layout: 'top',
        theme: 'defaultTheme',
        timeout: 20000
    });
};