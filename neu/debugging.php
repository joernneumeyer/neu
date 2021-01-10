<?php
  function dd(...$args): void {
    if (php_sapi_name() == 'cli-server') {
      echo '<pre>';
    }
    foreach ($args as $arg) {
      if (is_null($arg)) {
        $arg = '[[NULL]]';
      } else if ($arg === false) {
        $arg = '[[FALSE]]';
      } else if ($arg === true) {
        $arg = '[[TRUE]]';
      }
      print_r($arg);
      echo PHP_EOL;
    }
    exit(0);
  }
