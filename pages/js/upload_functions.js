function upload_now() {
    //start spinner
    $("#waiting-for-uploads").html('<img src="images/wait64x64.gif" alt="someimage" />');

    $.get( "upload_jobs_now.php", { },
        function( data ) {
            if (data.status == 'ok') {
                $("#waiting-for-uploads").html('');
                show_upload_results(data.results);
            } else {
                //error
                alert(error.message);
                show_upload_results(data.results);
            }

        },
        "json"  );

}

function show_upload_results(res) {
    var errs = [];
    var good_count = 0;
    for(var i=0;i < res.length; i++) {
        if (res[i].status == 'ok') {
            good_count ++;
        } else {
            errs.push(res[i].message)
        }
    }

    var err_string = '<ul>';
    for (i = 0; i< errs.length; i ++) {
        err_string += '<li><span style="color:red">' + errs[i] + '</span></li>'
    }
    err_string += '</ul>';

    $('#upload-status').html('<h4>Uploaded ' + good_count + ' jobs</h4><br>' + err_string);
    $('#upload-now-button').hide();
}