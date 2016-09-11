<?php
# returns json status of ok or error with message
# anyone can post to this, but unless the images are in the bucket,which can only be added to by authorized,
# the post is ignored

#ideally, this message would be passed by a private messaging system like aws sqs, but
# have to write this assuming no way to set up a cron job or independent task that runs in background

require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'/users/includes/header_json.php';

printOkJSONAndDie(['message'=>'test flags']);