<?php


  namespace Neu\Http;

  use Attribute;

  #[Attribute]
  class RedirectTo {
    public function __construct(string $path, ) {
    }
  }
