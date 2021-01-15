<?php


  namespace Neu;


  use Neu\Cdi\DependencyResolver;
  use Neu\Errors\HttpMethodNotAllowed;
  use Neu\Errors\InvalidModelData;
  use Neu\Errors\RoutingFailure;
  use Neu\Http\Request;
  use Neu\Http\Response;
  use Neu\Http\Router;
  use Neu\Http\StatusCode;
  use ReflectionMethod;

  class Kernel {
    private Router $router;

    public function boot() {
      $controller_refs = Neu::load_controller_reflections();
      $this->router    = Router::for_controller_reflections($controller_refs);
    }

    public function processRequest(Request $request): Response {
      try {
        $request = Request::from_global_state();
        $dr      = new DependencyResolver();
        $dr->register(fn() => $request, Request::class);
        try {
          $handler = $this->router->fetch_handler($request->path, $request->method);
        } catch (HttpMethodNotAllowed $e) {
          throw new RoutingFailure(previous: $e);
        }
        if ($handler === null) {
          return Response::not_found();
        } else {
          $controller = $dr->construct_object(of_type: $handler[0]);
          /** @var ReflectionMethod $handler_method */
          $handler_method  = $handler[1];
          $request->params = $handler[2];
          $handler_name    = $handler_method->getName();
          try {
            $args          = $dr->resolve_handler_arguments(with_request: $request, for_handler: $handler_method);
            $response_data = $controller->$handler_name(...$args);
            return (new Response(body: $response_data))->applyHandlerAnnotations(forHandler: $handler_method);
          } catch (InvalidModelData $e) {
            return new Response(status: 400, body: 'Invalid payload fields: ' . join(',', $e->with_invalid_fields));
          }
        }
      } catch (RoutingFailure) {
        return new Response(status: StatusCode::MethodNotAllowed);
      }
    }
  }
