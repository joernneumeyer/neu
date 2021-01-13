<?php
  if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;
  } else {
    require join(DIRECTORY_SEPARATOR, [__DIR__, 'public', 'index.php']);
  }
