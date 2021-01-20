<?php

  class tools {

    public static function escape($text) {
      return htmlspecialchars($text,ENT_QUOTES);
    }

    public static function checkResult($response) {
      if (!isset($response['error']) && $response != False) {
        return "Success";
      } else {
        return "Error: ".($response != False ? $response['error'] : "rqlite not reachable.");
      }
    }

    public static function checkRow($response) {
      if (!isset($response['rows'])) { print("Error: ".($response != False ? $response['error'] : "rqlite not reachable.")."\n"); }
    }

  }

?>
