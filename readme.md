This is pretty much ready to take out of the box and add to any server without modifying the server.

Requirements
* the server has to be set up to run at least php 5.5
* the database has to be set up, the tables and data are in install/starting_database.sql.zip
* the config file has to be filled in , users/private_init.example.php is an example of it, just copy it
   and fill in the commented areas, and rename it by taking out the .example in the name
* permissions have to set for the tmp directory inside the project so php can read and write files there

There are some users already in the database, for demo purposes

When a copy of this app has no internet connection, the uploads can still goto the main server by running the script:
php do_uploads_now.php

========================================================

update 1.1
------

This improves the first version:
  
  
1) Fixed pages so only one person can checkout a job at a time. see:

       https://github.com/willwoodlief/card-transcription/commit/ba523611ba099daa7bab156a5592100c9f8adebd

2) add in sns notification in admin options and settings, set up exception catching to send a notice. see:
  
       https://github.com/willwoodlief/card-transcription/commit/072340feeffe75020422fb1e941299f5eb26703c

3) force all input to utf8. See:
        
        https://github.com/willwoodlief/card-transcription/commit/6a5de51c27c7fbe63833d0df3ca1da415cd65dd8


4) Change edited image sizes to 600 px wide and keeping the original aspect ratio
  see:
  
       https://github.com/willwoodlief/card-transcription/commit/b9d9efa5d324547675250a3a9323e7ed8dd1aa39

  TODO LIST
  ---------------
5) Change layout of job pages, have form under the edited images which will be in a row, and then under form have originals. make form look like example in the pdf notes

6) Black and White button needs to restore to color after clicking twice
   * Since black and white is a destructive operation need to achieve this by redoing the history to the beginning (rewinding it), taking out the black and white transformations, and then applying the history again automatically
   * need to change some of the library code so we can tag the transformations, and also have a hook to undo all,delete transfromation by tag, and then re-apply exiting transformations

7) Crop tool is currently square rectangle, change crop size to a rectangle with an aspect ratio of 7 : 4 or 600X343

8) Api hook : add api call as described in the pdf, have option to test it without calling api to make sure its working okay

9) add better magnification tool and process
