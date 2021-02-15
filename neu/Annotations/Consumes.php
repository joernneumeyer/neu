<?php

  namespace Neu\Annotations;

  use Attribute;

  #[Attribute]
  class Consumes {
    public function __construct(public string $contentType) {
    }
  }
