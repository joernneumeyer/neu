<?php

  use Neu\Neu;

  require_once join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);
  define('APP_ROOT', dirname(__DIR__));

  Neu::bootstrap();

