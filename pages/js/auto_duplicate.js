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
                            var true_count = AutoDuplicateDoGUI(true,data);
                            if (true_count > 0) {
                                $('#email_for_duplicate').val(email);
                            }


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

    var count_dupes = 0;
    if (is_duplicate) {
        var link_html = "<ul>";

        for(var i = 0; i < data.duplicates.length; i ++) {
            var dup = data.duplicates[i];
            count_dupes++;
            if ( parseInt(dup.id) === jobid ) {
                count_dupes--;
                continue;
            }
            var the_link = dup.link;
            link_html +=  '<li>' + the_link + '</li>';

        }
        link_html += "</ul>";
        $('div.duplicate-control div.message').html(data.message  );
        $('div.duplicate-control div.dupe-link-container').html(link_html);
        if (count_dupes > 0) {
            $('div.duplicate-control').show();
        } else {
            $('div.duplicate-control').hide();
        }

    }

    $('[rel="popover"]').popover({
        html: true
           }).on('shown.bs.popover', function(/*event*/) {
               var iframe = $('.preview-iframe')[0];
               if (iframe) {
                   var winh = iframe.contentWindow.document.body.scrollHeight;
                   iframe.style.height = winh + 'px';
               }

           });

    if (count_dupes > 0) {
        toggleDuplicateGUI(true);
    } else {
        toggleDuplicateGUI(false);
    }
    return count_dupes;
}



