<?php


  namespace Neu\Annotations;

  use Attribute;

  #[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
  class Middleware {
    public function __construct(public string $middleware_class) {
    }
  }
