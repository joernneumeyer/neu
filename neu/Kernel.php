<?php

  namespace Neu;

  use App\DependencyFactory;
  use Neu\Annotations\UseMiddleware;
  use Neu\Cdi\DependencyResolver;
  use Neu\Errors\HttpMethodNotAllowed;
  use Neu\Errors\InvalidModelData;
  use Neu\Errors\RoutingFailure;
  use Neu\Http\Middleware;
  use Neu\Http\PostMiddleware;
  use Neu\Http\PreMiddleware;
  use Neu\Http\Request;
  use Neu\Http\Response;
  use Neu\Http\Router;
  use Neu\Http\StatusCode;
  use ReflectionMethod;

  class Kernel {
    private Router $router;
    private DependencyResolver $dr;

    public function dependencyResolver(): DependencyResolver {
      return $this->dr;
    }

    private static function loadHandlerAnnotationProcessors() {
      static $handlerAnnotationsProcessors;
      if (!$handlerAnnotationsProcessors) {
        $handlerAnnotationsProcessors = require implode(DIRECTORY_SEPARATOR, [__DIR__, 'handlerAnnotationProcessors.php']);
      }
      return $handlerAnnotationsProcessors;
    }
    /**
     * @throws Errors\TypeMismatch
     * @throws \ReflectionException
     */
    public function boot(): void {
      $controller_refs = Neu::load_controller_reflections();
      $this->router    = Router::for_controller_reflections($controller_refs);
      $this->dr = new DependencyResolver();
      $userDependencies = (new \ReflectionClass(DependencyFactory::class))->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC);
      foreach ($userDependencies as $dep) {
        if (!$dep->hasReturnType()) {
          throw new \Error('Trying to register factory "' . $dep->getName() . '", but it is missing a return type hint!');
        } else {
          $this->dr->register(factory: $dep->getClosure(), forType: $dep->getReturnType()->getName());
        }
      }
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Errors\InvalidDependencyLoadMode
     * @throws Errors\InvalidRouteSupplied
     * @throws Errors\TryToConstructUnregisteredDependency
     * @throws Errors\TypeMismatch
     * @throws Errors\UnresolvableDependencyType
     * @throws \ReflectionException
     */
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
          $controller = $this->dr->constructObject(ofType: $handler[0]);
          /** @var ReflectionMethod $handler_method */
          $handler_method  = $handler[1];
          $request->params = $handler[2];
          $handler_name    = $handler_method->getName();
          $middlewareToApply = $handler_method->getAttributes(UseMiddleware::class);
          foreach ($middlewareToApply as $m) {
            /** @var UseMiddleware $useMiddlewareAttribute */
            $useMiddlewareAttribute = $m->newInstance();
            if (!is_subclass_of($useMiddlewareAttribute->name, PreMiddleware::class)) continue;
            /** @var Middleware $middleware */
            $middleware = $this->dr->constructObject(ofType: $useMiddlewareAttribute->name);
            $middleware->apply();
          }
          try {
            $args          = $this->dr->resolveHandlerArguments(withRequest: $request, forHandler: $handler_method);
            $response_data = $controller->$handler_name(...$args);
            $response = new Response(body: $response_data);
            foreach (self::loadHandlerAnnotationProcessors() as $processorName => $processor) {
              if (!$handler_method->getAttributes($processorName)) continue;
              ($processor)($request, $response, $handler_method);
            }
            $this->dr->register(factory: fn() => $response, forType: Response::class);
            foreach ($middlewareToApply as $m) {
              /** @var UseMiddleware $useMiddlewareAttribute */
              $useMiddlewareAttribute = $m->newInstance();
              if (!is_subclass_of($useMiddlewareAttribute->name, PostMiddleware::class)) continue;
              /** @var Middleware $middleware */
              $middleware = $this->dr->constructObject(ofType: $useMiddlewareAttribute->name);
              $middleware->apply();
            }
            return $response;
          } catch (InvalidModelData $e) {
            return new Response(status: 400, body: 'Invalid payload fields: ' . join(',', $e->with_invalid_fields));
          }
        }
      } catch (RoutingFailure) {
        return new Response(status: StatusCode::MethodNotAllowed);
      }
    }
  }
