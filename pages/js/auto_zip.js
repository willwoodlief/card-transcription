$(function(){

});

$( "#zip" ).keyup(function() {
    var val = $('#zip').val();
    if (val.length >= 5) {
        $.get( "get_zip_information.php", { zip: val },
            function( data ) {
                if (data.status == 'ok') {
                    var city = data.city;
                    var state = data.state;
                    $("#city").val(city);
                    $("#state").val(state);
                }

            },
            "json"  );
    }
});

