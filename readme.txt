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

