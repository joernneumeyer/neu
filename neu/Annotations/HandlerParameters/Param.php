<?php

  namespace Neu\Annotations\HandlerParameters;

  use Attribute;

  #[Attribute(Attribute::TARGET_PARAMETER)]
  class Param {
    public function __construct(public string $name = '', public mixed $default = null) {
    }
  }
