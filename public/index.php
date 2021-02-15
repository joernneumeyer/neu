<?php

  use Neu\Http\Request;
  use Neu\Http\Response;
  use Neu\Http\StatusCode;
  use Neu\Kernel;
  use Neu\Neu;

  require_once join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);
  define('APP_ROOT', dirname(__DIR__));

  try {
    Neu::bootstrap();
    $kernel = new Kernel();
    $kernel->boot();
    $request = Request::from_global_state();
    $response = $kernel->processRequest($request);
  } catch (Throwable $e) {
    try {
      $response = niceResponseFromError($e);
    } catch (Throwable $t) {
      $response = (new Response(
        status: 500,
        body: 'Nice error reporting failed. You must have triggered a low-level/internal error! Please have a look at the following exception:'
          . "\r\n\r\n" . $t
      ))->contentType(\Neu\Http\ContentType::TextPlain);
    }
  }
  $response->send();
