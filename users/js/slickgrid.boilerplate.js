



function SlickGridBoilerplate(options) {

    // slick_setup,gridID,pageID ,search_id_array,context_menu_id,inline_panel_id
    var slick_setup = options.slick_setup;
    slick_setup.my_grid = this;
    this.grid_setup = slick_setup;
    var gridID = options.gridID;
    var pageID = options.pageID;
    var search_id_array = options.search_id_array;
    var context_menu_id = options.context_menu_id;
    var inline_panel_id = options.inline_panel_id;


    var contextDataView = null;
    var contextGrid = null;
    var remember_click_row = null;
    var remember_click_cell = null;

    
    
    var myFormatterObject = new MySlickgridformatters();
    var myValidatorObject = new MySlickgridValidators();
    var mySorterObject = new MySlickgridSorters();

    setupContextGrid();
    this.dataview = contextDataView;

    function MySlickgridValidators() {
        this.requiredFieldValidator = function (value) {
            if (value == null || value == undefined || !value.length) {
                return {valid: false, msg: "This is a required field"};
            }
            else {
                return {valid: true, msg: null};
            }
        };

        this.remoteCallValidator = function (value) {

            var cellium = contextGrid.getActiveCell();
            var row = cellium.row;

            var cell = cellium.cell;
            var cell_data = contextGrid.getDataItem(row);
            if (!cell_data) {
                //click on row with no data
                return {valid: true, msg: null};
            }
            var columns = contextGrid.getColumns();
            var field = columns[cell].field;

            //see if there is a confirmation dialog to changing this
            if (typeof slick_setup.confirm_field_change == 'function' ) {
                what = slick_setup.confirm_field_change(field,value,cell_data);
                if (!what) {
                    return {valid: false, msg: null};
                }
            }


            var ret = slick_setup.remote_call_change(field, cell_data, value);



            if (ret.valid) {
                if ('refresh' in ret && ret.refresh) { //if it exists and is not false

                    setTimeout(function() {
                        var the_grid_class = window[$("#" + gridID).data('gridvar')];
                        the_grid_class.updateRowAndData(ret.refresh,row);
                    }, 2000);

                }
                return {valid: true, msg: null};
            } else {
                slick_setup.show_error_message(ret.msg, 'error');
                return {valid: false, msg: ret.msg};
            }


        };



    }

    function MySlickgridformatters() {


        this.selectFormatter = function (row, cell, value, columnDef, dataContext) {

            if (columnDef.options) {
              var  pairs = columnDef.options.split(',');
            } else {
                return value; //if no options return the raw display
            }

            var key_pairs = {};
            for (var i in pairs) {
                var v = pairs[i];
                var i_val = v.split(':');
                key_pairs[i_val[1]] = i_val[0];
            }

            // get index of data in row
            var row_data = contextGrid.getDataItem(row);
            var columns = contextGrid.getColumns();
            var field = columns[cell].field;

            row_data[field+'--alt'] = key_pairs[value];

            if (value == null) {
                value = '';
            }
            //console.log("format : ["+value+"] as ["+key_pairs[value]+"]" );
            return key_pairs[value];

            //Now the row will display your button
        };


        this.passwordFormatter  = function (row, cell, value, columnDef, dataContext) {
            //hides whatever is in the field
            return '********';
            //Now the row will display your button
        };
        this.xFormatter = function (row, cell, value, columnDef, dataContext) {
            if (!value) {
                return '';
            }
            var grid_class_name = $("#" + gridID).data('gridvar');
            var button = "<span class='delrow redx glyphicon glyphicon-remove' data-row='" + row + "' data-cell='" + cell + "' id='sg-redx-" + dataContext.id + "' onclick='"+grid_class_name+".deleteRowHere(" + dataContext.id + ");' >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
            //the id is so that you can identify the row when the particular button is clicked
            return button;
            //Now the row will display your button
        };

        this.antisafeFormatter = function (row, cell, value, columnDef, dataContext) {

            var v = value.replace(/&lt;/g, '<');
            var v2 = v.replace(/&gt;/g, '>');
            return v2;
            //Now the row will display your button
        };

        this.fancySpanFormatter = function (row, cell, value, columnDef, dataContext) {


            var words = value.words;
            var classes = value.class;
            var button = "<span class='" + classes + "'>"+words+"</span>";
            //the id is so that you can identify the row when the particular button is clicked
            return button;

        };


        this.customFormatter = function (row, cell, value, columnDef, dataContext) {

            if (value === null) {
                return '';
            }

            if (typeof value !== 'object') {
                return value;
            }

            if (! ('custom_formatter' in value) ) {
                //cant process
                throw "custom formatter value does not have custom_formatter pointer"
            }
            //check if value is an object
            var row_data = contextGrid.getDataItem(row);
            var columns = contextGrid.getColumns();
            var field = columns[cell].field;
            var fn = window[value.custom_formatter];
            if(typeof fn === 'function') {
                return fn(field,dataContext);
            } else {
                throw "custom_formatter is not a function"
            }


        };


        //shows a span with a class selected by a callback, which will be based on the value
        //the link can be clicked, and will go to a callback function that can change the data, refresh the cell to show new state
        // the callback can make a series of changes in a cycle or something more complex
        this.classClickToggleFormatter = function (row, cell, value, columnDef, dataContext) {

            var grid_class_name = $("#" + gridID).data('gridvar');
            //get data for the row
            var row_data = contextGrid.getDataItem(row);
            //get the field used here
            var columns = contextGrid.getColumns();
            var field = columns[cell].field;

            var inner_html = '';
            if ('inner_formatter' in columnDef) {
                inner_html = columnDef.inner_formatter(row, cell, value, columnDef, dataContext);
            } else {
                inner_html = '<span>'+value+'</span>';
            }
            var button = "<span style='cursor:pointer' data-row='" + row + "' data-cell='" + cell + "' id='sg-toggle-" + field + '-' + dataContext.id + "' onclick='"+grid_class_name+".toggleCellHere(" + dataContext.id + ",\"" + field + "\");' >"+inner_html+"</span>";
            //the id is so that you can identify the row when the particular button is clicked
            return button;
            //Now the row will display your button
        };


        this.MyLongTextFormatter = function (row, cell, value, columnDef, dataContext) {
            if (!value) {
                return "none";
            }
            return "<p style='white-space: normal;margin-top:2px;padding-top:0px;margin-bottom:2px;padding-bottom:0px;'>" +
                value + "</p>";
        };

        this.MyPercentFormatter = function (row, cell, value, columnDef, dataContext) {
            if (!value) {
                return "0 %";
            }
            return  value + " %";
        };

        this.MyTimestampFormatter = function (row, cell, value, columnDef, dataContext) {
            if (!value) {
                return "none";
            }
            var date = new Date(value * 1000);
            var year = date.getFullYear();
            var month = date.getMonth() + 1;
            var date = date.getDate();

            var time = month + '/' + date + '/' + year;
            return time;
        }

        //todo this can be hooked up to the currency conversation tables and made to do different dominations
        this.MyCentsToCurrencyFormatter = function (row, cell, value, columnDef, dataContext) {
            if (!value) {
                return "free";
            }
            var dollars = Math.floor(value / 100);
            var remainingCents = value - dollars * 100;
            if (remainingCents > 0) {
                return '$ ' + dollars + '.' + remainingCents;
            } else {
                return '$ ' + dollars;
            }
        }

        this.MySecondsToDurationFormatter = function (row, cell, value, columnDef, dataContext) {
            if (!value) {
                return "none";
            }

            function numberEnding(number) {
                return (number > 1) ? 's' : '';
            }

            var years = Math.floor(value / 31536000);
            if (years) {
                return years + ' year' + numberEnding(years);
            }

            var days = Math.floor((value %= 31536000) / 86400);

            var months = Math.floor(days / 30);
            if (months) {
                return months + ' month' + numberEnding(months);
            }


            if (days) {
                return days + ' day' + numberEnding(days);
            }
            var hours = Math.floor((value %= 86400) / 3600);
            if (hours) {
                return hours + ' hour' + numberEnding(hours);
            }
            var minutes = Math.floor((value %= 3600) / 60);
            if (minutes) {
                return minutes + ' minute' + numberEnding(minutes);
            }
            var seconds = value;
            if (seconds) {
                return seconds + ' second' + numberEnding(seconds);
            }
            return 'none';
        };

        function millisecondsToStr(milliseconds) {


            var temp = Math.floor(milliseconds / 1000);
            var years = Math.floor(temp / 31536000);
            if (years) {
                return years + ' year' + numberEnding(years);
            }
            //TODO: Months! Maybe weeks?
            var days = Math.floor((temp %= 31536000) / 86400);
            if (days) {
                return days + ' day' + numberEnding(days);
            }
            var hours = Math.floor((temp %= 86400) / 3600);
            if (hours) {
                return hours + ' hour' + numberEnding(hours);
            }
            var minutes = Math.floor((temp %= 3600) / 60);
            if (minutes) {
                return minutes + ' minute' + numberEnding(minutes);
            }
            var seconds = temp % 60;
            if (seconds) {
                return seconds + ' second' + numberEnding(seconds);
            }
            return 'less than a second'; //'just now' //or other string you like;
        }


        this.div_wrap_formatter= function (row, cell, value, columnDef, dataContext) {

            return "<div class=''>" + value + "</div> ";
        };

        this.urlFormatter = function (row, cell, value, columnDef, dataContext) {
            if (!value.class) {
                value.class = '';
            }
            if (!value.target) {
                value.target = '';
            }
            return "<a class='" + value.class + "' href='" + value.url + "' target='" + value.target + "'>" + value.txt + "</a> ";
        };

        this.imageFormatter = function (row, cell, value, columnDef, dataContext) {
            if (!value.class) {
                value.class = '';
            }

            if (!value.txt) {
                value.txt = '';
            }

            return "<img class='" + value.class + "' src='" + value.url + "' alt='" + value.txt + "'>";
        };


        this.tagFormatter = function (row, cell, value, columnDef, dataContext) {

            if (value instanceof Array) {
                var what = value.join();
            } else {
                var what = tags;
            }

            return what;
        };


        this.SelectCellEditor = function (args) {
            var $select;
            var defaultValue;
            var scope = this;
            var key_pair = {};

            this.init = function () {
                var opt_values = null;

                if (args.column.options) {
                    opt_values = args.column.options.split(',');
                } else {
                    opt_values = "yes,no".split(',');
                }
                var option_str = "";
                for (var i in opt_values) {
                    var v = opt_values[i];
                    var i_val = v.split(':');
                    key_pair[i_val[1]] = i_val[0];
                    option_str += "<OPTION value='" + i_val[1] + "'>" + i_val[0] + "</OPTION>";
                }
                $select = $("<SELECT tabIndex='0' class='editor-select'>" + option_str + "</SELECT>");
                $select.appendTo(args.container);
                $select.focus();
            };

            this.destroy = function () {
                $select.remove();
            };

            this.focus = function () {
                $select.focus();
            };

            this.loadValue = function (item) {
                var defaultValue =  item[args.column.field];
                $select.val(defaultValue);
            };

            this.serializeValue = function () {
                if (args.column.options) {
                    return $select.val();
                } else {
                    return ($select.val() == "yes");
                }
            };

            this.applyValue = function (item, state) {
                item[args.column.field] = state;
            };

            this.isValueChanged = function () {
                return ($select.val() != defaultValue);
            };

            this.validate = function () {
                if (args.column.validator) {
                    var validationResults = args.column.validator($select.val());
                    if (!validationResults.valid) {
                        return validationResults;
                    }
                }

                return {
                    valid: true,
                    msg: null
                };
            };



            this.init();
        };


        //improved by better jquery test and goes to validator
        this.MyCheckBoxEditor = function(args) {
            var $select;
            var defaultValue;
            var scope = this;

            this.init = function () {
                $select = $("<INPUT type=checkbox value='true' class='editor-checkbox' hideFocus>");
                $select.appendTo(args.container);
                $select.focus();
            };

            this.destroy = function () {
                $select.remove();
            };

            this.focus = function () {
                $select.focus();
            };

            this.loadValue = function (item) {
                a_value = item[args.column.field];
                if (a_value == 'false' || a_value == '0') {
                    a_value = false;
                }

                defaultValue = !!a_value;
                if (defaultValue) {
                    $select.attr("checked", "checked");
                } else {
                    $select.removeAttr("checked");
                }
            };

            this.serializeValue = function () {
                return !!$select.prop('checked');
            };

            this.applyValue = function (item, state) {
                item[args.column.field] = state;
            };

            this.isValueChanged = function () {
                return (this.serializeValue() !== defaultValue);
            };

            this.validate = function () {
                if (args.column.validator) {
                    var validationResults = args.column.validator(this.serializeValue());
                    if (!validationResults.valid) {
                        return validationResults;
                    }
                }

                return {
                    valid: true,
                    msg: null
                };
            };

            this.init();
        }

    }

    function MySlickgridSorters() {
        var sortcol = null;
        var sortdir = null;
        this.setSortCol = function(s) {sortcol = s;};
        this.setSortDir = function(s) {sortdir = s;};

        this.sortOnValueProperty = function(a, b) {
            var x = a[sortcol].value, y = b[sortcol].value;
            return sortdir * (x === y ? 0 : (x > y ? 1 : -1));
        };


        this.sorterStringCompare = function(a, b) {
            var x = a[sortcol], y = b[sortcol];
            return sortdir * (x === y ? 0 : (x > y ? 1 : -1));
        };

        this.objectCustomSort = function(a,b) {
            if ('sort_function' in a[sortcol]) {
                var the_function = window[a[sortcol].sort_function]
               return the_function(a, b,sortcol,sortdir)
            } else {
                throw "custom sort propety does not have 'sort_function' field in individual data"// if the function is not there
            }

        };

        this.sorterNumeric = function(a, b) {
            var x = (isNaN(a[sortcol]) || a[sortcol] === "" || a[sortcol] === null) ? -99e+10 : parseFloat(a[sortcol]);
            var y = (isNaN(b[sortcol]) || b[sortcol] === "" || b[sortcol] === null) ? -99e+10 : parseFloat(b[sortcol]);
            return sortdir * (x === y ? 0 : (x > y ? 1 : -1));
        };

        this.sorterRating = function(a, b) {
            var xrow = a[sortcol], yrow = b[sortcol];
            var x = xrow[3], y = yrow[3];
            return sortdir * (x === y ? 0 : (x > y ? 1 : -1));
        };

        this.sorterDateIso = function(a, b) {
            var regex_a = new RegExp("^((19[1-9][1-9])|([2][01][0-9]))\\d-([0]\\d|[1][0-2])-([0-2]\\d|[3][0-1])(\\s([0]\\d|[1][0-2])(\\:[0-5]\\d){1,2}(\\:[0-5]\\d){1,2})?$", "gi");
            var regex_b = new RegExp("^((19[1-9][1-9])|([2][01][0-9]))\\d-([0]\\d|[1][0-2])-([0-2]\\d|[3][0-1])(\\s([0]\\d|[1][0-2])(\\:[0-5]\\d){1,2}(\\:[0-5]\\d){1,2})?$", "gi");

            if (regex_a.test(a[sortcol]) && regex_b.test(b[sortcol])) {
                var date_a = new Date(a[sortcol]);
                var date_b = new Date(b[sortcol]);
                var diff = date_a.getTime() - date_b.getTime();
                return sortdir * (diff === 0 ? 0 : (date_a > date_b ? 1 : -1));
            }
            else {
                var x = a[sortcol], y = b[sortcol];
                return sortdir * (x === y ? 0 : (x > y ? 1 : -1));
            }
        };
    }

    this.get_setup = function() { return this.package_grid;}

    this.clear_grid = function() {
        remember_click_row = null;
        contextDataView.beginUpdate();
        contextDataView.getItems().length = 0;
        contextDataView.endUpdate();
        contextGrid.invalidateAllRows();
        contextGrid.render();
    };

    this.resetRememberRow = function() {
        remember_click_row = null;
    }

    this.gotoCell = function(row, cell, forceEdit) {
        contextGrid.gotoCell(row,cell,forceEdit);
    };
//new datarow does not contain id yet
// this is called by outside function which will package the new stuff in a javascript object
// here it will be sent to the server and then displayed
    this.addNewRow = function(new_data_row,back_grid,afterwards,afterwards_data) {
        //add new data_row to server

        function onAddRowHere(ret) {
            if (ret.valid) {
                if (ret.data_array && ret.data_array.length > 0) {
                    for (var i = 0; i < ret.data_array.length; i++) {
                        contextDataView.addItem(ret.data_array[i]);
                    }
                    var row = contextDataView.getRowById(ret.data_array[ret.data_array.length - 1].id);
                    contextGrid.setSelectedRows([row]);
                    contextGrid.scrollRowIntoView(row);
                    if (ret.message) {
                        if(!ret.hasOwnProperty('no_show_message'))
                        {
                            slick_setup.show_error_message(ret.message, 'success');
                        }

                    }
                }
                if (typeof  afterwards == 'function') {
                    afterwards(afterwards_data);
                }

            } else {
                var message = ret.message;
                slick_setup.show_error_message(message, 'error');
            }

            if (back_grid) {
                var to_grid = back_grid.grid;
                var  to_row = back_grid.row;
                var to_cell = back_grid.cell;
                to_grid.gotoCell(to_row,to_cell,true);
            }
        }

        function fail_callback(ret) {
            var title = ret.title;
            var message = ret.message;
            slick_setup.show_error_message(message, 'error');

            if (back_grid) {
                var to_grid = back_grid.grid;
                var  to_row = back_grid.row;
                var to_cell = back_grid.cell;
                to_grid.gotoCell(to_row,to_cell,true);
            }
        }

        slick_setup.add_row_on_server(new_data_row, onAddRowHere,fail_callback);


    };

    this.updateRowAndData = function(data,row) {
        contextDataView.updateItem(data.id, data);
        contextGrid.updateRow(row);
    };

    this.refresh = function() {
        contextGrid.invalidate();
        contextGrid.invalidateRows([0,1,3]);
        contextGrid.render();
    }

    this.toggleContextFilterRow = function() {
        contextGrid.setTopPanelVisibility(!contextGrid.getOptions().showTopPanel);
    };

    this.updateRowByID = function(id,field,value) {
        var stuffToUpdate = contextDataView.getItemById(id);
        var row = contextDataView.getRowById(id);
        var ret = slick_setup.remote_call_change(field, stuffToUpdate, value);



        if (ret.valid) {
            if ('refresh' in ret && ret.refresh) { //if it exists and is not false

                setTimeout(function() {
                    var the_grid_class = window[$("#" + gridID).data('gridvar')];
                    the_grid_class.updateRowAndData(ret.refresh,row);
                }, 2000);

            }
            return {valid: true, msg: null};
        } else {
            slick_setup.show_error_message(ret.msg, 'error');
            return {valid: false, msg: ret.msg};
        }
    };
    
    this.removeRow = function(id) {
        contextDataView.deleteItem(id); //get rid of item for good
        contextGrid.invalidate();
        contextGrid.render();
        contextDataView.refresh();
    };

    this.deleteRowHere = function(id) {


        var all_data =  slick_setup.getContextData();
        var stuffToDelete = contextDataView.getItemById(id);
        var link_id = 'sg-redx-' + id;
        var link = $('#' + link_id);
        var row = link.data("row");
        var cell = link.data("cell");

        function afterEffect(id, message) {
            contextDataView.deleteItem(id); //get rid of item for good
            contextDataView.refresh();
            slick_setup.show_error_message(message, 'success');
        }

        function noEffect(id, title, message) {


            link.removeClass('redx');
            link.addClass('bad-redx');

            var cellNode = contextGrid.getCellNode(row, cell);

            $(cellNode).popover({
                title: title,
                content: message,
                placement: "auto left",
                trigger: "hover", // focus hover click
                container: "body",
                html : true
            });
            slick_setup.show_error_message(message, 'error');
        }

        //hightlight row

        contextGrid.setSelectedRows([row]);
        //get confirmation, see if setup has a confirm deletion handler
        var what = false;


        if (typeof slick_setup.confirm_deletion == 'function' ) {
            what = slick_setup.confirm_deletion(id,stuffToDelete);
        } else {
            what =  confirm(confirm_delete_str);
        }

        if (what) {
            slick_setup.delete_row_on_server(id, all_data, stuffToDelete, afterEffect, noEffect);
        }

    };

    this.toggleCellHere = function(id, field) {

        var link_id = "sg-toggle-" + field + '-' + id;
        var link = $('#' + link_id);
        var row = $('#' + link_id).data("row");
        var cell = $('#' + link_id).data("cell");
        var this_data = contextGrid.getDataItem(row);

         slick_setup.do_field_toggle(id, field, this_data,on_toggled);

        function on_toggled(ret) {

            if (ret.valid) {

                //update the table data at the row
                var changed_data = ret.data;

                contextDataView.updateItem(Number(id), changed_data);
                //refresh the cell
                contextGrid.invalidateRows([row]);
                contextGrid.updateRowCount();
                contextGrid.render();
            } else {
                //show error message at cell
                slick_setup.show_error_message(ret.message, 'error');
                var cellNode = contextGrid.getCellNode(row, cell);
                $(cellNode).popover({
                    title: ret.title? ret.title: '',
                    content: ret.message,
                    placement: "auto left",
                    trigger: "hover",
                    container: "body",
                    html : true
                });
            }
        }



    };

    //acts like row was clicked on the first cell
    this.selectByID = function(id,afterwards,afterwards_data) {
      var cell = 0;
      var row =  contextDataView.getRowById(id);
      if (typeof row == 'undefined') {return;}
      remember_click_row = null;
      this.selectRow(row,cell,true,afterwards,afterwards_data);
    };

    this.selectRow = function(row,cell,b_scroll,afterwards,afterwards_data) {
        //nn select row

        //SA-15 only send message once per click in same row and cell
        if (remember_click_row == row  ) {
            return;
        } else {
            remember_click_row = row;
            remember_click_cell = cell;
        }

        var tim = contextDataView.getItem(row);
        slick_setup.ContextWasClicked(tim, row,cell,afterwards,afterwards_data);
        var selectedRows = [];
        selectedRows.push(row);
        //   contextGrid.resetActiveCell();
        contextGrid.setSelectedRows(selectedRows);
        if (b_scroll) {
            contextGrid.scrollRowIntoView(row,false);
        }
        contextGrid.render();
    };



    function setupContextGrid() {

        var columns = slick_setup.getColumnsForSlickgrid(myFormatterObject,myValidatorObject,mySorterObject);


        var options = {
            editable: true,
            autoEdit: true,
            enableAddRow: false,
            enableCellNavigation: true,
            asyncEditorLoading: true,
            forceFitColumns: false,
            topPanelHeight: 25,
            showTopPanel: true,
            multiColumnSort: true

        };
        // rowHeight:100
        if (typeof(slick_setup.getGridRowHeight) == "function") {
            var rowHeight = slick_setup.getGridRowHeight();
            options.rowHeight = rowHeight;
        }

        if (typeof(slick_setup.getShowTopPanel) == "function") {
            var showPanel = slick_setup.getShowTopPanel();
            options.showTopPanel = showPanel;
        }

        var sortcol = "name";
        var sortdir = 1;


        var data = slick_setup.getContextData();
        //data will be in .data of above

        /*
         keys
         name,type,mode,uuid,notes,tags,create_ts
         */


        contextDataView = new Slick.Data.DataView({inlineFilters: false});


        //allows us to add stuff per row
        function row_metadata(old_metadata_provider) {
            return function(row) {
                var item = this.getItem(row),
                    ret = old_metadata_provider(row);

                //todo this does not seem like the way to disable but keep this to add other css classes later
                if(false){
                    if (ret) {
                        ret.cssClasses = (ret.cssClasses || '') + ' disable-edits';
                    } else
                    {
                        ret =  { cssClasses: 'disable-edits' };
                    }

                }
                //console.log(ret);
                return ret;
            };
        }

        contextDataView.getItemMetadata = row_metadata(contextDataView.getItemMetadata);

        contextDataView.getItemMetaData = function(row){
            if(row == data.length - 1){
                return { cssClasses: 'disable-edits' };
            }
            return {};
        };
        contextGrid = new Slick.Grid("#" + gridID, contextDataView, columns, options);
        contextGrid.setSelectionModel(new Slick.RowSelectionModel());

        var pager = new Slick.Controls.Pager(contextDataView, contextGrid, $("#" + pageID));
        $(".slick-pager-settings-expanded").toggle();


        var columnpicker = new Slick.Controls.ColumnPicker(columns, contextGrid, options);

        if (slick_setup.goesGridUseContextMenu()) {
            contextGrid.onContextMenu.subscribe(function (e) {
                e.preventDefault();
                var cell = contextGrid.getCellFromEvent(e);
                $("#" + context_menu_id)
                    .data("row", cell.row)
                    .css("top", e.pageY)
                    .css("left", e.pageX)
                    .show();

                $("body").one("click", function () {
                    $("#" + context_menu_id).hide();
                });
            });
        }

        $("#" + context_menu_id).click(function (e) {
            if (!$(e.target).is("li")) {
                return;
            }
            /*
             if (!grid.getEditorLock().commitCurrentEdit()) {
             return;
             }
             *///blh hahaha
            var row = $(this).data("row");
            var option = $(e.target).attr("data");

            slick_setup.onContextMenuItemClicked(data[row], option);


        });

        contextGrid.onClick.subscribe(function (e, args) {
            slick_setup.my_grid.selectRow(args.row,args.cell,false);
        });

        contextGrid.onCellChange.subscribe(function (e, args) {


            var index = args.cell;


            var cell = args.cell;
            var item = args.item;
            var columns = contextGrid.getColumns();
            var field = columns[cell].field;
            var new_field_value = item[field];

            slick_setup.contextDataChanged(item, field, new_field_value);

            contextDataView.updateItem(args.item.id, args.item);


        });


        var handleValidationError = function (e, args) {
            var validationResult = args.validationResults;
            var activeCellNode = args.cellNode;
            var editor = args.editor;
            var errorMessage = validationResult.msg;
            $(activeCellNode).popover({
                title: error_saving_header_str,
                content: errorMessage,
                placement: "auto left",
                trigger: "hover",
                container: "body",
                html : true
            });

        };

        contextGrid.onValidationError.subscribe(handleValidationError);


        contextGrid.onAddNewRow.subscribe(function (e, args) {

            var data = slick_setup.getContextData();
            var node = {};
            node.id = data.length;
            if (!args.item.name) {
                alert("need name of new entry");
                return;
            }


            node.name = args.item.name;
            node.rank = args.item.rank;
            node.action = 'add';
            node.index = -1;


            contextDataView.addItem(node);
            //adds node to data
        });

        contextGrid.onKeyDown.subscribe(function (e) {
            // select all rows on ctrl-a
            if (e.which != 65 || !e.ctrlKey) {
                return false;
            }

            var rows = [];
            for (var i = 0; i < contextDataView.getLength(); i++) {
                rows.push(i);
            }

            contextGrid.setSelectedRows(rows);
            e.preventDefault();
            return true;
        });

        //from http://stackoverflow.com/questions/22559891/slickgrid-custom-column-sorters, modified slightly by will
        contextGrid.onSort.subscribe(function (e, args) {


            var cols = args.sortCols;

            contextDataView.sort(function (dataRow1, dataRow2) {
                for (var i = 0, l = cols.length; i < l; i++) {
                    sortdir = cols[i].sortAsc ? 1 : -1;
                    sortcol = cols[i].sortCol.field;
                    mySorterObject.setSortCol(sortcol);
                    mySorterObject.setSortDir(sortdir);

                    var result = null;
                    //columns might have sorter:sorterStringCompare, or not
                    if ('sorter' in cols[i].sortCol) {
                        result = cols[i].sortCol.sorter(dataRow1, dataRow2); // sorter property from column definition comes in play here
                    } else {
                        var default_sort = mySorterObject.sorterStringCompare;
                        result = default_sort(dataRow1, dataRow2);
                    }

                    if (result != 0) {
                        return result;
                    }
                }
                return 0;
            });
            args.grid.invalidateAllRows();
            args.grid.render();

        });


        // wire up model events to drive the contextGrid
        contextDataView.onRowCountChanged.subscribe(function (e, args) {
           // contextGrid.updateRowCount();
            contextGrid.invalidate();
           // contextGrid.render();
        });

        contextDataView.onRowsChanged.subscribe(function (e, args) {
            contextGrid.invalidateRows(args.rows);
            contextGrid.invalidate();
           // contextGrid.render();
        });

        contextDataView.onPagingInfoChanged.subscribe(function (e, pagingInfo) {
            var isLastPage = pagingInfo.pageNum == pagingInfo.totalPages - 1;
            var enableAddRow = isLastPage || pagingInfo.pageSize == 0;
            var options = contextGrid.getOptions();

            if (options.enableAddRow != enableAddRow) {
                contextGrid.setOptions({enableAddRow: enableAddRow});
            }
        });



        contextGrid.onBeforeEditCell.subscribe(function(e,args) {
            if (typeof(slick_setup.isCellEditable) == "function") {
                return slick_setup.isCellEditable(args.row, args.cell, args.item);
            }
            return true;
        });



        ///////////search stuff

        var searchWord = '';
        var searchWord2 = '';

        function get_grid_class() {
            return window[$("#" + gridID).data('gridvar')];
        }



        function myFilter(item, args) {

            var word = args.searchWord;
            var word2 = args.searchWord2;
            var grid = args.grid_class_name;

            var isWord = true;
            var isWord2 = true;

            if (!word && !word2) {
                return true;
            }

            if (word) {
                var itemIndex = grid.grid_setup.getSearchKeywordColumn();
                var str = '';
                if(item.hasOwnProperty(itemIndex +'--alt')) {
                    str = item[itemIndex +'--alt'];
                } else if (item[itemIndex] && item[itemIndex].hasOwnProperty('get_text_function')) {
                    var str_fnc = item[itemIndex].get_text_function;
                    var fn = window[str_fnc];
                    if (typeof fn === 'function') {
                        str = fn(item[itemIndex]);

                    }
                }

                else {
                    str = item[itemIndex];
                }

                if (!str || 0 === str.length) {
                    isWord = false;
                }
                else if (str.toLowerCase().indexOf(word.toLowerCase()) !== -1) {
                    isWord = true;
                }
                else {
                    isWord = false;
                }
            }

            if (word2) {
                if (typeof(grid.grid_setup.getSearchKeywordColumn2) == "function") {

                    if (!word2) {
                        return true;
                    }
                    var itemIndex2 = grid.grid_setup.getSearchKeywordColumn2();
                    var str2 = '';
                    if(item.hasOwnProperty(itemIndex2 +'--alt')) {
                        str2 = item[itemIndex2 +'--alt'];
                    } else {
                        str2 = item[itemIndex2];
                    }
                    if (!str2) {
                        isWord2 = false;
                    }
                    else if (str2.toLowerCase().indexOf(word2.toLowerCase()) !== -1) {
                        isWord2 = true;
                    }
                    else {
                        isWord2 = false;
                    }
                } else {
                    alert('no function');
                }
            }
            if (word && word2) {
                return isWord && isWord2;
            }
            if (word) {
                return isWord;
            }
            if (word2) {
                return isWord2;
            }
            return false;
        }//end filter function


        // wire up the search textbox to apply the filter to the model
        $("#" + search_id_array[0]).keyup(function (e) {
            Slick.GlobalEditorLock.cancelCurrentEdit();

            // clear on Esc
            if (e.which == 27) {
                this.value = "";
            }

            searchWord = $("#" + search_id_array[0]).val();
            //get the negative tags
            updateFilter();
        });

        $("#" + search_id_array[1]).keyup(function (e) {
            Slick.GlobalEditorLock.cancelCurrentEdit();
            // clear on Esc
            if (e.which == 27) {
                this.value = "";
            }

            searchWord2 = $("#" + search_id_array[1]).val();
            //get the negative tags
            updateFilter();
        });

        function updateFilter() {



            contextDataView.setFilterArgs({

                searchWord: searchWord,
                searchWord2: searchWord2,
                grid_class_name : get_grid_class()


            });


           // contextGrid.invalidate();
            contextDataView.refresh();
        }


        ////////////////////


        // initialize the model after all the events have been hooked up
        contextDataView.beginUpdate();
        contextDataView.setItems(data);

        //seaerch stuff
        contextDataView.setFilterArgs({
            searchWord: searchWord
        });

        contextDataView.setFilter(myFilter);

        ///se///////////

        contextDataView.endUpdate();

        // if you don't want the items that are not visible (due to being filtered out
        // or being on a different page) to stay selected, pass 'false' to the second arg
        contextDataView.syncGridSelection(contextGrid, true);


        //uncomment this to have draggable areas
        //$("#context-data-side").resizable();
        ////context contextGrid

        contextGrid.registerPlugin(new Slick.AutoTooltips());

        // move the filter panel defined in a hidden div into grid top panel
        $("#" + inline_panel_id)
            .appendTo(contextGrid.getTopPanel())
            .show();


        return contextGrid;

    }

}
      
      

