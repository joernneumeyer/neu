<?php

  use Neu\Http\ContentType;
  use Neu\Http\Request;
  use Neu\Http\Response;
  use Neu\Http\StatusCode;
  use Neu\Kernel;
  use Neu\Neu;
  use Neu\OutputBuffer;
  use Neu\View;
  use function Neu\Debug\niceResponseFromError;

  require_once join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);
  define('APP_ROOT', dirname(__DIR__));

  $buffer = new OutputBuffer();
  try {
    $buffer->start();
    Neu::bootstrap();
    $kernel = new Kernel();
    $kernel->boot();
    $kernel
      ->dependencyResolver()
      ->register(fn() => $buffer, OutputBuffer::class)
      ->register(fn() => new View(APP_ROOT . '/views', buffer: $buffer), View::class);
    $request  = Request::from_global_state();
    $response = $kernel->processRequest($request);
  } catch (Throwable $e) {
    try {
      $response = niceResponseFromError($e);
    } catch (Throwable $t) {
      $response = (new Response(
        status: StatusCode::InternalServerError,
        body: 'Nice error reporting failed. You must have triggered a low-level/internal error! Please have a look at the following exception:'
              . "\r\n\r\n" . $t
      ))->contentType(ContentType::TextPlain);
    }
  } finally {
    $buffer->end();
    $response->send();
  }
