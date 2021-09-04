<?php


  namespace Neu\Annotations;

  use Attribute;
  use Neu\Http\Middleware;

  #[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
  class UseMiddleware {
    /**
     * @param string $name
     * @throws \Exception
     */
    public function __construct(public string $name) {
      if (!class_exists($name)) {
        throw new \Exception("Cannot register undefined class '{$name}' as middleware!");
      }
      if (!(is_subclass_of($name, Middleware::class))) {
        throw new \Exception("Cannot register class '{$name}' as middleware, since it does not implement '" . Middleware::class . "'!");
      }
    }
  }
