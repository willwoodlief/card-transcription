Update 2.0
------
Project 2

1) Added migrations (https://github.com/robmorgan/phinx). To update database to current changes goto top of app directory and type: php bin/phinx.phar  migrate -e dev . If pulling in changes this will probably be same directory. Production database can be done with way too now, with the -e production flag instead of the -e dev

2) Added more configeration settings for the database, these are optional but probably need to add the DB_CHARSET option if your database does not automatically connect as utf-8. Also need to set the production database settings if needing to update production database from local machine

3) Added in new app configeration set in admin section : folder_watch (added by migration above)

4) Added in new table file_watching (added by migration above)

5) Added in stub for the scan folder task which already gets the information in the watched folder



update 1.1
------

This improves the first version:
  
  
1) Fixed pages so only one person can checkout a job at a time. See: https://github.com/willwoodlief/card-transcription/commit/ba523611ba099daa7bab156a5592100c9f8adebd

2) add in sns notification in admin options and settings, set up exception catching to send a notice. See: https://github.com/willwoodlief/card-transcription/commit/072340feeffe75020422fb1e941299f5eb26703c

3) force all input to utf8. See: https://github.com/willwoodlief/card-transcription/commit/6a5de51c27c7fbe63833d0df3ca1da415cd65dd8


4) Change edited image sizes to 600 px wide and keeping the original aspect ratio. See: https://github.com/willwoodlief/card-transcription/commit/b9d9efa5d324547675250a3a9323e7ed8dd1aa39

5) Change layout of job pages so that the form is on top and the edited images are in a row below that. See: https://github.com/willwoodlief/card-transcription/commit/0ac3241f8cf05983b62a340b73073267e727f52b

6) Changed crop tool to select a rectangle the same ratio of width/length as index card (7/4). See: https://github.com/willwoodlief/card-transcription/commit/1cc7d3fb0b96e5c41fed1c14a0a98a000d7bc2bf
  
7) Added api hook, this url is configured in the private init setup which,  the private setup contains specific info and as security measure, is not uploaded to repo. See: https://github.com/willwoodlief/card-transcription/commit/36583d540c4e822da3c750e4634851f628431f9d 

8) Made the zoom buttons show the larger image in a better more accessable place on the screen. Now a person does not have to scroll up to the image to look at it. This change was done in step (5)
 
 
Added new blank database in install, and updated  users/private_init.example.php to show how to add api key

  TODO LIST
  ---------------

9) Black and White button needs to restore to color after clicking twice
   * Since black and white is a destructive operation need to achieve this by redoing the history to the beginning (rewinding it), taking out the black and white transformations, and then applying the history again automatically
   * need to change some of the library code so we can tag the transformations, and also have a hook to undo all,delete transfromation by tag, and then re-apply exiting transformations






  Original Install Notes
  -------------------------
  
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
