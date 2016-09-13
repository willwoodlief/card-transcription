/**
 * Created by will on 10/2/15.
 */

function format_date_value(field,row_data) {
    var expiration = row_data[field].value;
    if (!expiration) {
        return expiration_not_set_yet;
    }
    var selectedDate =  new Date(expiration);
    var now = new Date();

    var class_to_use = '';

    return  "<span class='" + class_to_use + "'>"+selectedDate.toLocaleDateString()+"</span>";

}

function format_date_time_value(field,row_data) {
    var expiration = row_data[field].value;
    if (!expiration) {
        return expiration_not_set_yet;
    }
    var selectedDate =  new Date(expiration);
    var now = new Date();

    var class_to_use = '';

    return  "<span class='" + class_to_use + "'>"+selectedDate.toLocaleString()+"</span>";

}

function get_custom_datetime_text(data) {
    var expiration = data.value;
    if (!expiration) {
        return expiration_not_set_yet;
    }
    var selectedDate =  new Date(expiration);
    return selectedDate.toLocaleString();
}

function sort_value_dates(a, b,sortcol,sortdir) {
    var regex_a = new RegExp("^((19[1-9][1-9])|([2][01][0-9]))\\d-([0]\\d|[1][0-2])-([0-2]\\d|[3][0-1])(\\s([0]\\d|[1][0-2])(\\:[0-5]\\d){1,2}(\\:[0-5]\\d){1,2})?$", "gi");
    var regex_b = new RegExp("^((19[1-9][1-9])|([2][01][0-9]))\\d-([0]\\d|[1][0-2])-([0-2]\\d|[3][0-1])(\\s([0]\\d|[1][0-2])(\\:[0-5]\\d){1,2}(\\:[0-5]\\d){1,2})?$", "gi");

    if (regex_a.test(a[sortcol].value) && regex_b.test(b[sortcol].value)) {
        var date_a = new Date(a[sortcol].value);
        var date_b = new Date(b[sortcol].value);
        var diff = date_a.getTime() - date_b.getTime();
        return sortdir * (diff === 0 ? 0 : (date_a > date_b ? 1 : -1));
    }
    else {
        var x = a[sortcol], y = b[sortcol];
        return sortdir * (x === y ? 0 : (x > y ? 1 : -1));
    }
}

function sort_value_dates_aws(a, b,sortcol,sortdir) {

    if (a && !b) {return 1;}
    if (!a && b) {return -1;}
    if (!a && !b) {return 0;}
    var date_a = new Date(a[sortcol].value);
    var date_b = new Date(b[sortcol].value);
    var diff = date_a.getTime() - date_b.getTime();
    return sortdir * (diff === 0 ? 0 : (date_a > date_b ? 1 : -1));

}
