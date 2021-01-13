<?php


  namespace Neu\Annotations;

  use Attribute;

  #[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
  class Produces {
    public function __construct(public string $content_type) {
    }
  }
