<?php

  use Neu\Cdi\DependencyResolver;
  use Neu\Errors\HttpMethodNotAllowed;
  use Neu\Errors\RoutingFailure;
  use Neu\Http\Request;
  use Neu\Http\Response;
  use Neu\Http\Router;
  use Neu\Neu;

  require_once join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);
  define('APP_ROOT', dirname(__DIR__));

  try {
    Neu::bootstrap();
    $controller_refs = Neu::load_controller_reflections();
    $router          = Router::for_controller_reflections($controller_refs);
    $request         = Request::from_global_state();
    $dr              = new DependencyResolver();
    $dr->register(fn() => $request, Request::class);
    try {
      $handler = $router->fetch_handler($request->path, $request->method);
    } catch (HttpMethodNotAllowed $e) {
      throw new RoutingFailure(previous: $e);
    }
    if ($handler === null) {
      $response = Response::not_found();
    } else {
      $controller                = $dr->construct_object(of_type: $handler[0]);
      $handler_name              = $handler[1];
      $request->route_parameters = $handler[2];
      $args                      = $dr->resolve_handler_arguments(with_request: $request, for_handler: new ReflectionMethod($controller, $handler_name));
      $response_data             = $controller->$handler_name(...$args);
      $response                  = Response::from($response_data);
    }
    $response->send();
  } catch (Throwable $e) {
    echo $e;
  }
