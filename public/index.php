<?php

  use Neu\Http\Request;
  use Neu\Http\Router;
  use Neu\Neu;

  require_once join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);
  define('APP_ROOT', dirname(__DIR__));

  try {
    Neu::bootstrap();
    $controller_refs = Neu::load_controller_reflections();
    $router          = Router::for_controller_reflections($controller_refs);
    $request         = Request::from_global_state();
    $handler         = $router->fetch_handler($request->path, $request->method);
    if ($handler === null) {
      exit;
    }
    $controller = $handler[0]->newInstance();
    $handler_name = $handler[1];
    $response = $controller->$handler_name();
    dd($response);
  } catch (Throwable $e) {
    echo $e;
  }
