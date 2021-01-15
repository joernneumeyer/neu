<?php

  use Neu\Http\Request;
  use Neu\Http\Response;
  use Neu\Http\StatusCode;
  use Neu\Kernel;
  use Neu\Neu;

  require_once join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);
  define('APP_ROOT', dirname(__DIR__));

  Neu::bootstrap();
  $kernel = new Kernel();
  $kernel->boot();
  try {
    $request = Request::from_global_state();
    $response = $kernel->processRequest($request);
  } catch (Throwable $e) {
    $response = new Response(status: StatusCode::InternalServerError, body: $e);
  }
  $response->send();
