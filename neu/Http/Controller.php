<?php
  namespace Neu\Http;

  use Attribute;

  #[Attribute(Attribute::TARGET_CLASS)]
  class Controller {
    public function __construct(public string $path = '/') {
    }
  }
