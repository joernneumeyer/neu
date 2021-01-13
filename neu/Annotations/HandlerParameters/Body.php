<?php

  namespace Neu\Annotations\HandlerParameters;

  use Attribute;

  #[Attribute(Attribute::TARGET_PARAMETER)]
  class Body {
    public function __construct() {
    }
  }
