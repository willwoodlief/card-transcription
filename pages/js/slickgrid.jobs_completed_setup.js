function SlickSetupJobsCompleted() {


    this.getColumnsForSlickgrid=function(myFormatterObject,myValidatorObject,mySorterObject) {

        return [
            {
                id: "client_id",
                name: 'User',
                field: "client_id",
                width: 230,
                minWidth: 40,
                editor: null,
                validator: null,
                formatter: null,
                sortable: true
            },  //
            {
                id: "profile_id",
                name: 'Profile',
                field: "profile_id",
                width: 100,
                minWidth: 100,
                editor: null,
                validator: null,
                formatter: myFormatterObject.customFormatter,
                sortable: true,
                sorter: mySorterObject.objectCustomSort
            },
            {
                id: "uploaded_timestamp",
                name: 'Uploaded',
                field: "uploaded_timestamp",
                width: 100,
                minWidth: 100,
                editor: null,
                validator: null,
                formatter: null,
                sortable: true
            }
        ];



    };

    this.getGridRowHeight = function() {
        return 25;
    };

    this.getShowTopPanel = function() {
        return false;
    };

    this.getSearchKeywordColumn = function() {
        return 'client_id';
    };

    this.getSearchKeywordColumn2 = function() {
        return 'profile_id';
    };

    this.goesGridUseContextMenu = function() {
        return false;
    };

    this.onContextMenuItemClicked = function(data, action) {
       // var id = data.id;

    };

    this.getContextData = function() {
        return job_completed_data;
    };

    this.ContextWasClicked = function(data, row,cell) {
        if (!data) {
            return;
        }


    };

    this.confirm_deletion = function (id,stuffToDelete) {
        //do nothing here
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

//this is set up to be called by the rails controller as it sets up the data, coordinated with the xformatter,or activeFormatter
    this.delete_row_on_server = function(id, all_data, stuffToDelete, afterEffect, noEffect) {
        //do ajax call to delete and then if successful delete the row with afterEffect, or if error show that with noEffect



    };

    // this cannot be async because we need a return from this function
    this.remote_call_change = function(field, cell_data, value) {


        //return valid false to prevent data being updated in table
        return {valid: true, msg: message,refresh:false};
    };

//more than one class can be returned, seperate by a space for each . but this is not implemented
    this.get_class_for_field = function(field, value, row_data) {
        return '';
    };

    this.do_field_toggle = function(id, field, row_data,callback) {



        // {valid: true, message: 'ok', title: '?##$#$?', data: row_data};
    };

    this.add_row_on_server = function(row_data, callback, fail_callback) {
        //add row_data to server, get back everything it may have added, and message

        //here row_data is going to contain start_ts and end_ts, we will call the server with these params



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

