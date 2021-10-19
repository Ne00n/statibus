<?php

//Title
define("_title","whatever");

//Domain
define("_domain","statibus.local");

//Timezone see https://www.php.net/manual/en/timezones.php
date_default_timezone_set('Europe/Amsterdam');
define("_timeFormat",'d M H:i');
define("_timeFormatDetails",'d M H:i:s');
define("_timeFormatRSS",'D, d M Y H:i:s O');

//Cleanup history after x days, 0 = disabled
define("_cleanup",91);

//Threshold needed to flag as Online
define("_remoteThreshold",1);
//Amount of external checks that will be done
define("_remoteChecks",2);

//rqlite
define("_rqliteIP","127.0.0.1");
define("_rqlitePort",4001);

?>
