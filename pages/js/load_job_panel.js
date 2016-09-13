function load_job_panel(data,flag) {
    if (data.status != 'ok') {
        display_error(data.message);
    }
    var joblist = $('#job-list');
    joblist.html('');
    var jobs = data.jobs;
    for(var i=0;i<jobs.length;i++) {
        var job = jobs[i];
        var uploaded_dt = new Date(job.job.uploaded_timestamp * 1000);
        var human_uploaded_dt = uploaded_dt.toLocaleString();
        var seconds_old = Math.floor(Date.now()/1000) -  job.job.uploaded_timestamp;
        var liclass = 'normal';
        if (seconds_old > 30) {
            liclass = 'urgant';
        }
        var cspan = '';
        if (flag =='c') {
            if (job.translater.id && job.job.transcribed_timestamp) {
                var trans_dt = new Date(job.job.transcribed_timestamp * 1000);
                var human_trans_dt = uploaded_dt.toLocaleString();
                cspan = '<span class="job-transcribed-detail"> Transcribed by: '+
                job.translater.fname + ' ' + job.translater.lname + ' @ ' +
                human_trans_dt + '</span>';

                '</span>';
            } else {
                cspan = '<span class="job-transcribed-detail"> Not transcribed yet</span>';
            }
            liclass = 'normal';

        }
        var li = $(''+
        '<li onclick="open_job_page('+job.job.id+');" class="'+liclass+'">'+
        '<span class="job-preview-detail">'+
        job.job.client_id+ ' ' +  job.job.profile_id +
        '</span>' +
        '<span class="job-uploaded-date">'+
        human_uploaded_dt +
        '</span>' +
        '<img src="'+job.images.org_side_a.url+'"  class="img-side-preview">' +
        '<img src="'+job.images.org_side_b.url+'"  class="img-side-preview">' +
        cspan +
        '</li>');
        joblist.append(li);
    }
}

function display_error(message) {
    alert(message);
}

function open_job_page(job_id) {

    document.location.href = 'job.php?jobid=' + job_id;
}