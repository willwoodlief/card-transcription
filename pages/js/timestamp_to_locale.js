$(function() {
    $(".a-timestamp-full-date-time").each(function() {
        var qthis = $(this);
        var ts = $(this).data('ts');
        if (ts === 0 || ts === '0' || ts === '') {
            qthis.text('' );
        } else {
            var m = moment(ts * 1000);
            qthis.text(m.format('LLLL'));
        }
    });

    $(".a-timestamp-full-date").each(function() {
        var qthis = $(this);
        var ts = $(this).data('ts');
        if (ts === 0 || ts === '0' || ts === '') {
            qthis.text('' );
        } else {
            var m = moment(ts * 1000);
            qthis.text(m.format('LL'));
        }
    });

    $(".a-timestamp-short-date-time").each(function() {
        var qthis = $(this);
        var ts = $(this).data('ts');
        if (ts === 0 || ts === '0' || ts === '') {
            qthis.text('' );
        } else {
            var m = moment(ts*1000);
            qthis.text(m.format('lll') );
        }

    });

    $(".a-timestamp-short-date").each(function() {
        var qthis = $(this);
        var ts = $(this).data('ts');
        if (ts === 0 || ts === '0' || ts === '') {
            qthis.text('' );
        } else {
            var m = moment(ts * 1000);
            qthis.text(m.format('ll'));
        }
    });

});