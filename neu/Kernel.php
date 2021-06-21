<?php


  namespace Neu;


  use App\DependencyFactory;
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
    private DependencyResolver $dr;

    public function boot() {
      $controller_refs = Neu::load_controller_reflections();
      $this->router    = Router::for_controller_reflections($controller_refs);
      $this->dr = new DependencyResolver();
      $userDependencies = (new \ReflectionClass(DependencyFactory::class))->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC);
      foreach ($userDependencies as $dep) {
        if (!$dep->hasReturnType()) {
          throw new \Error('Trying to register factory "' . $dep->getName() . '", but it is missing a return type hint!');
        } else {
          $this->dr->register(factory: $dep->getClosure(), for_type: $dep->getReturnType()->getName());
        }
      }
    }

    public function processRequest(Request $request): Response {
      try {
        $this->dr->register(fn() => $request, Request::class);
        try {
          $handler = $this->router->fetch_handler($request->path, $request->method);
        } catch (HttpMethodNotAllowed $e) {
          throw new RoutingFailure(previous: $e);
        }
        if ($handler === null) {
          return Response::not_found();
        } else {
          $controller = $this->dr->constructObject(of_type: $handler[0]);
          /** @var ReflectionMethod $handler_method */
          $handler_method  = $handler[1];
          $request->params = $handler[2];
          $handler_name    = $handler_method->getName();
          try {
            $args          = $this->dr->resolveHandlerArguments(with_request: $request, for_handler: $handler_method);
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
