<?php

  namespace Neu\Http;

  use Neu\Annotations\Controller;
  use Neu\Errors\HttpMethodNotAllowed;
  use Neu\Errors\InvalidRouteSupplied;
  use Neu\Annotations\Route;
  use ReflectionClass;
  use ReflectionMethod;
  use function Neu\Pipe7\pipe;

  class Router {
    /**
     * Router constructor.
     * @param ReflectionClass[] $controller_refs
     */
    private function __construct(private array $controller_refs) {
    }

    /**
     * @param ReflectionClass[] $controller_refs
     * @return Router
     */
    public static function for_controller_reflections(array $controller_refs): Router {
      return new Router($controller_refs);
    }

    /**
     * @param string $path
     * @param string $method
     * @return array|null
     * @throws InvalidRouteSupplied
     * @throws HttpMethodNotAllowed
     */
    public function fetch_handler(string $path, string $method): array|null {
      foreach ($this->controller_refs as $ref) {
        $handler_methods = pipe($ref->getMethods())
          ->filter(fn(ReflectionMethod $method) => $method->getAttributes(Route::class))
          ->toArray();
        /** @var Controller $controller */
        $controller = $ref->getAttributes(Controller::class)[0]->newInstance();

        foreach ($handler_methods as $handler_method) {
          /** @var ReflectionMethod $handler_method */
          $route = $handler_method->getAttributes(Route::class)[0];
          if ($route->isRepeated()) {
            // TODO throw adequate error
            throw new \Exception();
          }
          $route = $route->newInstance();
          /** @var Route $route */
          $route_method_is_valid =
            is_string($route->method)
              ? $route->method === $method
              : in_array($method, $route->method);
          $handler_route         = $controller->path . $route->path;
          $handler_route = str_replace('//', '/', $handler_route);
          $route_match           = self::match_path($path, $handler_route);
          if ($route_match !== false) {
            if (!$route_method_is_valid) {
              throw new HttpMethodNotAllowed();
            }
            return [$ref->getName(), $handler_method, $route_match];
          }
        }
      }
      return null;
    }

    /**
     * @param string $path
     * @param string $route
     * @return array|false
     * @throws InvalidRouteSupplied
     */
    public static function match_path(string $path, string $route): array|false {
      if ($path === $route) {
        return [];
      }
      $path = rtrim($path, '/');
      $route = rtrim($route, '/');
      $path_length  = strlen($path);
      $route_length = strlen($route);
      $params       = [];

      for ($ri = 0, $pi = 0; $ri < $route_length && $pi < $path_length; ++$ri, ++$pi) {
        if ($ri >= $route_length || $pi >= $path_length) {
          return false;
        }
        if ($route[$ri] === '{') {
          $parameter_start = $ri;
          $brace_counter   = 1;
          for (++$ri; $ri < $route_length; ++$ri) {
            if ($route[$ri] === '{') {
              ++$brace_counter;
            } else {
              if ($route[$ri] === '}') {
                --$brace_counter;
              }
            }
            if ($brace_counter === 0) {
              break;
            }
          }
          if ($ri === $route_length) {
            throw new InvalidRouteSupplied();
          }
          $parameter             = explode(
            separator: ':',
            string: substr($route, offset: $parameter_start + 1, length: $ri - $parameter_start - 1),
            limit: 2
          );
          $parameter_value_start = $pi;
          ++$ri;
          if ($ri >= $route_length) {
            for (; $pi < $path_length && $path[$pi] !== '/'; ++$pi) {
              ;
            }
            if ($pi === $path_length) {
              --$pi;
            }
          } else {
            for (; $pi < $path_length; ++$pi) {
              if ($path[$pi] === '/' || $route[$ri] === $path[$pi]) {
                break;
              }
            }
            --$pi;
            if ($route[$ri] !== '/') {
              --$ri;
            }
          }
          if ($pi === $path_length && $ri !== $route_length - 1) {
            return false;
          }
          $parameter_value = substr($path, offset: $parameter_value_start, length: $pi - $parameter_value_start + 1);
          if (isset($parameter[1])) {
            $matches = [];
            preg_match("/^$parameter[1]$/", $parameter_value, $matches);
            if ($matches === []) {
              return false;
            }
          }
          if (isset($route[$ri]) && $route[$ri] === '/') {
            --$ri;
          }
          $params[$parameter[0]] = $parameter_value;
        } else {
          if ($route[$ri] !== $path[$pi]) {
            return false;
          }
        }
      }
      if ($ri >= $route_length && $pi < $path_length) {
        return false;
      } else {
        if ($ri < $route_length && $pi >= $path_length) {
          return false;
        }
      }
      return $params;
    }
  }
