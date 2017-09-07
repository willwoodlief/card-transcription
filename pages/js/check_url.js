$(function(){
    $( "#website" ).blur(function() {
        var val = $('#website').val();
        if (val.length >= 0) {
            $.get( "check_url_exists.php", { url: val },
                function( data ) {
                    if (data.status === 'ok') {
                        if (data.exists) {
                            //clear bad class
                            $('#website').removeClass('oops');
                        } else {
                            //add bad class
                            $('#website').addClass('oops');
                        }

                    } else {
                       console.log(data);
                    }

                },
                "json"  );
        }
    });
});



