<?php

//this file is not included in version control and should be added manually to new install
// below is the keys that are expected, just fill it out and then take the .example out of the file name
// the gitignore is set to exclude the real file from any commit

//aws, this is the keys for the non root user account, to set these up goto iam server on aws
putenv("AWS_ACCESS_KEY_ID=the access key ");
putenv("AWS_SECRET_ACCESS_KEY= the secret key");
putenv("AWS_REGION=us-west-2");

//database
putenv("DB_USERNAME= database user name here");
putenv("DB_PASSWORD= database user password here");
putenv("DB_NAME= database name here");
putenv("DB_HOST= database url here (localhost for local install)");
putenv("DB_PORT=3306");
putenv("DB_CHARSET=utf8"); //check your localhost for the connection for it, it may not be utf8


//production database, this does not have to be added for the local to work
// and is only used for migrating production, or testing production
putenv("PRODUCTION_DB_USERNAME=production database user name here");
putenv("PRODUCTION_DB_PASSWORD=production database user password here");
putenv("PRODUCTION_DB_NAME= production database name here");
putenv("PRODUCTION_DB_HOST=production database url here");
putenv("PRODUCTION_DB_PORT=3306");
putenv("PRODUCTION_DB_CHARSET=utf8");





//captcha : goto https://www.google.com/recaptcha/admin and set up keys for your domain
putenv("CAPTCHA_KEY= goto google and get captcha key for your domain");
putenv("CAPTCHA_SECRET= the captch secret goes here");

//contact info
putenv("EMERGANCY_EMAIL",'some_email@gmail.com');

//api call
putenv("API_ON_FINISH=http://someurl.com?key=somekey");