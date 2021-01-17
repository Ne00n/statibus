<?php

//Stolen from http://stackoverflow.com/questions/45953/php-execute-a-background-process

class backgroundProcess {
  //This launches the command $cmd, redirects the command output to $outputfile, and writes the process id to $pidfile.
  public static function startProcess($cmd) {
    exec(sprintf("%s > /dev/null 2>&1 & echo $! >> /dev/null", $cmd));
  }

}

?>
