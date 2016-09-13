$(function(){
//bootstrap 3 has different event than 2
    $('#report-tabs').on('shown.bs.tab', function(e) {
        var paneID = $(e.target).attr('href');
        var src = $(paneID).attr('data-src');
        // if the iframe hasn't already been loaded once
        if($(paneID+" iframe").attr("src")=="")
        {
            $(paneID+" iframe").attr("src",src);
        }
    });

});