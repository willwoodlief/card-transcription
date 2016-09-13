var remember_job_ids = {};
function count_new_jobs(data) {
    var count_new = 0;
    for(var i =0; i <data.jobs.length; i++) {
         var job = data.jobs[i];
         var jobid = job.job.id;
         if ( ! remember_job_ids[jobid]) {
             count_new ++;
             remember_job_ids[jobid] = 1;
         } else {
             remember_job_ids[jobid] += 1;
         }

     }
    return count_new;
}