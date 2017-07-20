$(function(){
    $( "#email" ).blur(function() {
        check_if_duplicate();
    });

    if (duplicate_flag) {
        check_if_duplicate();  //if the flag is set in the php code generating the page, then talk to server after page runs to see what kind of duplicate info is going on
    }

    function check_if_duplicate() {
        var email = $('#email').val();
        if (email.length >0) {
            $.get( "get_duplicate_information.php", { email: email,client_id: client_id },
                function( data ) {
                    if (data.status === 'ok') {
                        if (data.is_duplicate) {
                            $('#email_for_duplicate').val(email);
                            AutoDuplicateDoGUI(true,data);
                        } else {
                            AutoDuplicateDoGUI(false,data);
                        }
                    }

                },
                "json"  );
        }
    }

});

function toggleDuplicateGUI(b_on) {
    if (b_on) {
        $('div.duplicate').show();
        $('div.not-duplicate').hide();
        $("input[name='email']").addClass('duplicate');
        $("input[name!='email'][type='text']").prop("disabled", true);
        $("input[name='transcribe']").prop("disabled", true);

    } else {
        $('div.not-duplicate').show();
        $('div.duplicate').hide();
        $("input[name='email']").removeClass('duplicate');
        $("input[name!='email'][type='text']").prop("disabled", false);
        $("input[name='transcribe']").prop("disabled", false);
    }
}

function AutoDuplicateDoGUI(is_duplicate,data) {
    toggleDuplicateGUI(is_duplicate);
        if (is_duplicate) {
            var link_html = "<ul>";
            for(var i = 0; i < data.duplicates.length; i ++) {
                var dup = data.duplicates[i];
                var the_link = dup.link;
                link_html +=  '<li>' + the_link + '</li>';

            }
            link_html += "</ul>";
            $('div.duplicate-control div.message').html(data.message  );
            $('div.duplicate-control div.dupe-link-container').html(link_html)
        }
}



