function SlickSetupServiceUser() {

    this.getColumnsForSlickgrid=function(myFormatterObject,myValidatorObject,mySorterObject) {
//
        var org =  [
            {
                id: "is_active",
                name: user_is_active_column,
                field: "is_active",
                width: 50,
                minWidth: 40,
                cssClass: "cell-title",
                editor: null, //myFormatterObject.SelectCellEditor
                options: user_active_options_for_slick,
                validator: null,
                formatter: myFormatterObject.classClickToggleFormatter ,
                inner_formatter: myFormatterObject.fancySpanFormatter,
                sortable: true
            },  //
            {
                id: "name",
                name: user_column_name + required_astrix,
                field: "name",
                width: 100,
                minWidth: 40,
                cssClass: "cell-title",
                editor: Slick.Editors.Text,
                validator: myValidatorObject.remoteCallValidator,
                formatter: null,
                sortable: true
            },

            {
                id: "email",
                name: user_column_email   + required_astrix,
                field: "email",
                width: 200,
                minWidth: 40,
                cssClass: "cell-title",
                editor: Slick.Editors.Text,
                validator: myValidatorObject.remoteCallValidator,
                formatter: null,
                sortable: true
            },
            {
                id: "password",
                name: user_column_password   + required_astrix,
                field: "password",
                width: 100,
                minWidth: 40,
                cssClass: "cell-title",
                editor: Slick.Editors.Text,
                validator: myValidatorObject.remoteCallValidator,
                formatter: myFormatterObject.passwordFormatter,
                sortable: true
            },
            {
                id: "dont_email_new_package",
                name: user_column_should_mail_new_package,
                field: "dont_email_new_package",
                width: 150,
                minWidth: 30,
                cssClass: "cell-title",
                formatter: Slick.Formatters.Checkmark,
                editor: myFormatterObject.MyCheckBoxEditor,
                validator: myValidatorObject.remoteCallValidator,
                sortable: true
            },
            {
                id: "bundle_count_total",
                name: user_column_bundle_count_total,
                field: "bundle_count_total",
                width: 100,
                minWidth: 40,
                cssClass: "cell-title",
                editor: null,
                validator: null,
                formatter: myFormatterObject.customFormatter,
                sortable: true,
                sorter: mySorterObject.sortOnValueProperty
            },//
            {
                id: "bundle_count_active_not_expired",
                name: user_column_bundle_paid_total,
                field: "bundle_count_active_not_expired",
                width: 100,
                minWidth: 40,
                cssClass: "cell-title",
                editor: null,
                validator: null,
                formatter: myFormatterObject.customFormatter,
                sortable: true,
                sorter: mySorterObject.sortOnValueProperty
            },
            {
                id: "listing_count_active",
                name: user_column_active_listing_count,
                field: "listing_count_active",
                width: 100,
                minWidth: 40,
                cssClass: "cell-title",
                editor: null,
                validator: null,
                formatter: myFormatterObject.customFormatter,
                sortable: true,
                sorter: mySorterObject.sortOnValueProperty
            },//
            {
                id: "listing_count_inactive",
                name: user_column_inactive_listing_count,
                field: "listing_count_inactive",
                width: 100,
                minWidth: 40,
                cssClass: "cell-title",
                editor: null,
                validator: null,
                formatter: myFormatterObject.customFormatter,
                sortable: true,
                sorter: mySorterObject.sortOnValueProperty
            },
            {
                id: "locale",
                name: user_column_locale,
                field: "locale",
                width: 100,
                minWidth: 40,
                cssClass: "cell-title no-tooltip",
                editor: myFormatterObject.SelectCellEditor,
                options: user_locale_options_for_slick,
                validator: myValidatorObject.remoteCallValidator,
                formatter: myFormatterObject.selectFormatter,
                sortable: true
            },
            {
                id: "telephone",
                name: user_column_work_telephone,
                field: "telephone",
                width: 100,
                minWidth: 40,
                cssClass: "cell-title",
                editor: Slick.Editors.Text,
                validator: myValidatorObject.remoteCallValidator,
                sortable: true
            },
            {
                id: "website",
                name: user_column_website,
                field: "website",
                width: 100,
                minWidth: 40,
                cssClass: "cell-title",
                editor: Slick.Editors.Text,
                validator: myValidatorObject.remoteCallValidator,
                sortable: true
            },
            {
                id: "home_phone",
                name: user_column_home_telephone,
                field: "home_phone",
                width: 100,
                minWidth: 40,
                cssClass: "cell-title",
                editor: Slick.Editors.Text,
                validator: myValidatorObject.remoteCallValidator,
                sortable: true
            },
            {
                id: "mailing_address",
                name: user_column_mailing_address,
                field: "mailing_address",
                width: 250,
                minWidth: 40,
                cssClass: "cell-title",
                editor: Slick.Editors.LongText,
                validator: myValidatorObject.remoteCallValidator,
                sortable: true
            },
            {
                id: "admin_notes",
                name: commission_bundle_column_notes,
                field: "admin_notes",
                width: 360,
                minWidth: 40,
                cssClass: "cell-title",
                editor: Slick.Editors.Text,
                validator: myValidatorObject.remoteCallValidator,
                sortable: true
            },
            {
                id: "currency",
                name: commission_bundle_column_currency,
                field: "currency",
                width: 220,
                minWidth: 40,
                cssClass: "cell-title no-tooltip",
                editor: myFormatterObject.SelectCellEditor,
                options: currency_options_for_slick,
                validator: myValidatorObject.remoteCallValidator,
                formatter: myFormatterObject.selectFormatter,
                sortable: true
            },
            {
                id: "country_code",
                name: user_column_country_code,
                field: "country_code",
                width: 100,
                minWidth: 40,
                cssClass: "cell-title no-tooltip",
                editor: myFormatterObject.SelectCellEditor,
                options: country_options_for_slick,
                validator: myValidatorObject.remoteCallValidator,
                formatter: myFormatterObject.selectFormatter,
                sortable: true
            },
            {
                id: "is_using_metric",
                name: user_column_is_metric,
                field: "is_using_metric",
                width: 60,
                minWidth: 30,
                cssClass: "cell-title",
                formatter: Slick.Formatters.Checkmark,
                editor: myFormatterObject.MyCheckBoxEditor,
                validator: myValidatorObject.remoteCallValidator,
                sortable: true
            },
            {
                id: "id",
                name: user_column_id,
                field: "id",
                width: 60,
                minWidth: 40,
                cssClass: "cell-title",
                editor: null,
                validator: null,
                sortable: true
            },
            {
                id: "delete_row",
                name: delete_column_name,
                field: "delete_row",
                width: 60,
                minWidth: 40,
                cssClass: "cell-title",
                formatter: myFormatterObject.xFormatter
            }

        ];



        return org;



    };

    this.getGridRowHeight = function() {
        return 25;
    };

    this.getShowTopPanel = function() {
        return true;
    };

    this.getSearchKeywordColumn = function() {
        return 'name';
    };

    this.getSearchKeywordColumn2 = function() {
        return 'email';
    };

    this.goesGridUseContextMenu = function() {
        return false;
    };

    this.onContextMenuItemClicked = function(data, action) {
       // var id = data.id;

    };

    this.getContextData = function() {
        return user_data;
    };

    this.ContextWasClicked = function(data, row,cell,afterwards,afterwards_data) {
        if (!data) {
            return;
        }
        var back_grid = {grid:this.my_grid, row:row , cell:cell};
        var id = data.id;
        $("#user-name-selected").text(data.name);
        package_grid.clear_grid();
        listing_grid.clear_grid(); //so that stuff does not get mixed up
        $("#package-selected").text(msg_click_bundle_for_listings);
        package_grid.addNewRow(data,back_grid,afterwards,afterwards_data);

    };

    this.contextDataChanged = function(item, field, new_field_value) {
       // var d = item[field];

    };

    this.isCellEditable = function(row,cell,item) {
        if (item) {
            return true;
        }
        return false;
    };

    this.confirm_deletion = function(id,stuffToDelete) {
        //confirm via javascript they are aware of the number of listings they are deleting
        //make string in confirmration box
        var total_listings = parseInt(stuffToDelete['listing_count_active'].value) +parseInt(stuffToDelete['listing_count_inactive'].value);
        var yy = stuffToDelete.name + " ("  + stuffToDelete.email  + ") " +
            "\n" + user_deletion_warning_line1 +
            "\n" + stuffToDelete['bundle_count_total'].value + ' ' + user_deletion_warning_line2 +
            "\n" + total_listings + ' ' + user_deletion_warning_line3 +
            "\n" +    user_deletion_warning_line4;
        var r = parseInt (prompt(yy) );
        if (r != total_listings) {

            return false;
        }

        return true;
    };

//this is set up to be called by the rails controller as it sets up the data, coordinated with the xformatter,or activeFormatter
    this.delete_row_on_server = function(id, all_data, stuffToDelete, afterEffect, noEffect) {
        //do ajax call to delete and then if successful delete the row with afterEffect, or if error show that with noEffect




        var my_object = jQuery.extend(true, {}, submit_form_data);
        my_object.id = id;
        $.ajax({
            type: "POST",
            url: "delete_user",
            data: my_object,
            async: true,
            success: function(data) {
                if (data.valid) {
                    afterEffect(data.id, data.message);
                    $("#user-name-selected").text('');
                    package_grid.clear_grid();
                    listing_grid.clear_grid(); //so that stuff does not get mixed up
                    $("#package-selected").text(msg_click_bundle_for_listings);
                } else {
                    noEffect(data.id, error_deleting_header_str, data.message);
                }

            }

        })
            .fail(function() {
                noEffect(id, error_deleting_header_str, error_deleting_header_str + ' Connection Error');
            })
        ;
    };

    // this cannot be async because we need a return from this function
    this.remote_call_change = function(field, cell_data, value) {
        //update_commission_bundle
        var message = '';
        var myret = false;
        var my_object = jQuery.extend(true, {}, submit_form_data);
        my_object.param = cell_data;
        my_object.param[field] = value;
        my_object.changed_field = field;
        var b_do_refresh = false;
        $.ajax({
            type: "POST",
            url: "update_user",
            data: my_object,
            async: false,
            success: function(data) {
                if (data.valid) {
                   myret = true;
                   if (data.set_is_active) {
                       cell_data.is_active= data.set_is_active;
                       b_do_refresh = cell_data;
                   }
                }
                message = data.message;

            }

        })
            .fail(function() {
                 message = 'Could not connect';
            })
        ;

        //return valid false to prevent data being updated in table
        return {valid: myret, msg: message,refresh:b_do_refresh};
    };

//more than one class can be returned, seperate by a space for each . but this is not implemented
    this.get_class_for_field = function(field, value, row_data) {
        return '';
    };

    this.do_field_toggle = function(id, field, row_data,callback) {

        if (field=='is_active') {

            var my_object = jQuery.extend(true, {}, submit_form_data);
            my_object.field = field;
            my_object.value = row_data[field];
            my_object.param = row_data;
            $.ajax({
                type: "POST",
                url: "toggle_user",
                data: my_object,
                async: true,
                success: function(data) {
                    if (data.valid) {
                        //add delete column in
                        callback(data);
                    } else {
                        callback(data);
                    }

                }

            })
                .fail(function() {
                    callback({message:'connection error',title:"Error"});
                })
            ;
        }

        // {valid: true, message: 'ok', title: '?##$#$?', data: row_data};
    };

    this.add_row_on_server = function(row_data, callback, fail_callback) {
        //add row_data to server, get back everything it may have added, and message


        $.ajax({
            type: "POST",
            url: "create_user",
            data: row_data,
            async: true,
            success: function(data) {
                if (data.valid) {
                    //add delete column in
                    for (var i = 0; i < data.data_array.length; i++) {
                        data.data_array[i].delete_row = true;
                    }
                    callback(data);
                } else {
                    fail_callback(data);
                }

            }

        })
            .fail(function() {
                fail_callback({message:'connection error',title:"Error"});
            })
        ;

    };

//types alert,information,error,warning,notification,success
    this.show_error_message = function(message, type) {
        $(".main-header").noty({
            text: message,
            type: type,
            dismissQueue: true,
            layout: 'top',
            theme: 'defaultTheme',
            timeout: 20000
        });
    };

}

