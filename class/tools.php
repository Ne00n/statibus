<?php

  class tools {

    public static function escape($text) {
      return htmlspecialchars($text,ENT_QUOTES);
    }

  }

?>
