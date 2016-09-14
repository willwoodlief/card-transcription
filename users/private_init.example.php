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





//captcha : goto https://www.google.com/recaptcha/admin and set up keys for your domain
putenv("CAPTCHA_KEY= goto google and get captcha key for your domain");
putenv("CAPTCHA_SECRET= the captch secret goes here");

