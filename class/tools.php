<?php

  class tools {

    public static function getUptimeFromService($id,$uptime) {
      if (isset($uptime['values'])) {
        foreach ($uptime['values'] as $row) {
          if ($row[0] == $id) {
            return $row;
          }
        }
      } else {
        return False;
      }
    }

  }

?>
