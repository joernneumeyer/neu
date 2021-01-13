<?php


  namespace Neu\Annotations;

  use Attribute;

  #[Attribute]
  class RedirectTo {
    public function __construct(string $path, ) {
    }
  }
