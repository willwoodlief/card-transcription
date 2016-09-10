var user_grid = null;
var package_grid = null;
var listing_grid = null;

function create_new_regular_user() {
    user_grid.addNewRow(submit_form_data);
    return false;
}

function create_new_package_from_form() {

    if (! package_grid.current_user_id) {
        package_grid.slick_setup.show_error_message(msg_choose_user__package_first,'error');
        return;
    }
    $('#make-new-user-bundle-dialog').modal('hide');
    var data = jQuery.extend(true, {}, submit_form_data);

    data.bundle_id = $( "#bundle_id_select").val();
    data.user_id =  package_grid.current_user_id;

    package_grid.addNewRow(data);
    return false;
}



function create_new_listing() {
    listing_grid.addNewRow(submit_form_data);
    return false;
}


function format_skipped_payment(row, cell, value, columnDef, dataContext) {
    var is_skipped = value.is_skipped; //boolean
    var can_skip =  value.can_skip; //boolean

    if (!is_skipped && can_skip) {
        return  "<span class='skip-command' data-idrow='" + dataContext.id +"' onclick='skip_command(this);'>"+ skip_command_name + "</span>";
    } else if (is_skipped) {
        return already_skipped;
    } else {
        return ""
    }

}

function skip_command(that) {
    var id = $(that).data('idrow');
    package_grid.updateRowByID(id,'skip',true);
}



function format_bundle_count(field,row_data) {
    var bundles = row_data[field].value;
    var mynum = Number(bundles);
    var class_to_use = '';
    if (mynum < 1) {
        class_to_use = ' zero-number-class '
    }
    return  "<span class='" + class_to_use + "'>"+mynum+"</span>";

}

function format_bundle_paid_count(field,row_data) {
    var bundles = row_data[field].value;
    var mynum = Number(bundles);

    var class_to_use = '';
    if (mynum < 1) {
        class_to_use = ' zero-number-class-red '
    }
    return  "<span class='" + class_to_use + "'>"+mynum+"</span>";

}

function format_inactive_listing_count(field,row_data) {
    var bundles = row_data[field].value;
    var mynum = Number(bundles);

    var class_to_use = '';
    if (mynum > 0) {
        class_to_use = ' zero-number-class-red '
    }
    return  "<span class='" + class_to_use + "'>"+mynum+"</span>";

}

function format_package_expiration(field,row_data) {
    var expiration = row_data['package_expiration'].value;
    if (!expiration) {
        return expiration_not_set_yet;
    }
    var selectedDate =  new Date(expiration);
    var now = new Date();

    var class_to_use = '';
    if (selectedDate < now) {
        // selected date is in the past
        class_to_use = ' zero-number-class-red '
    }


    return  "<span class='" + class_to_use + "'>"+expiration+"</span>";

}



function format_oxxo_code(field,row_data) {
    var words = row_data[field].value;
    var ss = row_data[field].bar_image;
    if (!words || !ss) {
        return '';
    }

    var js_click_handler = "onclick=\"show_bar_code('" + ss + "')\"";
    return  "<span class='bar-code-span' "+js_click_handler+" >"+words+"</span>";

}

function show_bar_code(url) {
    $('#bar-code-image').attr('src',url);
    $("#the-bar-code-url").text(url);
    $("#show-bar-code-dialog").modal();
}

// select_user_package_listing(3,254,1821)
function select_user_package_listing(user,package,listing) {
    function onReadyPackageGrid(package_id) {
       if (package_id) { package_grid.selectByID(package_id,onReadyListingGrid,listing); }
    }

    function onReadyListingGrid(listing_id) {
        if (listing) {listing_grid.selectByID(listing_id);}
    }
    user_grid.selectByID(user,onReadyPackageGrid,package);
}

function select_listing(listing_id) {
    var my_object = jQuery.extend(true, {}, submit_form_data);
    my_object.id = listing_id;
    $.ajax({
            type: "GET",
            url: staff_info_about_property_url,
            data: my_object,
            async: true,
            success: function(data) {
                if (data.valid) {
                    var user_id = data.user_id;
                    var package_id = data.package_id;
                    $("#search_bar_error").text('');
                    select_user_package_listing(user_id,package_id,listing_id);
                } else {
                    package_grid.clear_grid();
                    listing_grid.clear_grid();
                    $("#search_bar_error").text(cannot_find_listing_error);
                }

            }

        })
        .fail(function() {
            noEffect(id, error_deleting_header_str, error_deleting_header_str + ' Connection Error');
        })
    ;
}

function find_listing() {
    var id = $("#quick_listing_id_box").val();
    select_listing(id);
}


$(function() {

    var user_grid_opts = {
        slick_setup: new SlickSetupServiceUser,
        gridID : 'contextGrid-user',
        pageID : 'contextPager-user',
        search_id_array : ['contextKeywordSearch-user','contextKeywordSearch-user2'],
        context_menu_id : 'contextCMenu-user',
        inline_panel_id : 'inlineFilterPanel-user'

    };

    user_grid = new SlickGridBoilerplate(user_grid_opts);


    var package_grid_opts = {
        slick_setup: new SlickSetupServicePackage,
        gridID : 'contextGrid-package',
        pageID : 'contextPager-package',
        search_id_array : ['contextKeywordSearch-package','contextKeywordSearch-package2'],
        context_menu_id : 'contextCMenu-package',
        inline_panel_id : 'inlineFilterPanel-package'

    };

    package_grid = new SlickGridBoilerplate(package_grid_opts);
    package_grid.current_user_id = null;


    var listing_grid_opts  = {
        slick_setup: new SlickSetupServiceListing,
        gridID : 'contextGrid-listing',
        pageID : 'contextPager-listing',
        search_id_array : ['contextKeywordSearch-listing','contextKeywordSearch-listing2'],
        context_menu_id : 'contextCMenu-listing',
        inline_panel_id : 'inlineFilterPanel-listing'

    };

    listing_grid = new SlickGridBoilerplate(listing_grid_opts);


    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        e.target // newly activated tab
        var the_grid_class = window[$(e.target).data('gridvar')];
        the_grid_class.refresh();

        e.relatedTarget // previous active tab
    })


    $("#quick_listing_id_box").keyup(function(event){
        if(event.keyCode == 13){
            $("#button_for_listing_find").click();
        }
    });


});