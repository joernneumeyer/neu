<?php


  namespace Neu\Annotations;

  use Attribute;

  #[Attribute(Attribute::TARGET_PROPERTY)]
  class ModelProperty {
    public function __construct(public string $with_mapped_name = '') {
    }
  }
