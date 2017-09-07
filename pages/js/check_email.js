$(function(){
    $( "#email" ).blur(function() {
        var val = $('#email').val();
        if (val.length >= 0) {
            var b_email_syntax_ok = validate_email(val);


            if (b_email_syntax_ok) {
                $('#email').removeClass('oops');
            } else {
                $('#email').addClass('oops');
            }

        }
    });
});

function validate_email(val) {
    //javascript regex testing
    // taken from emailregex.com  checks RFC 5322
    var regx = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    return regx.test(val);
}



