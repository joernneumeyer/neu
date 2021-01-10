<?php


  namespace Neu\Http;

  use Attribute;

  #[Attribute(Attribute::TARGET_METHOD)]
  class Route {
    public function __construct(public array|string $method, public string $path = '') {
    }
  }
