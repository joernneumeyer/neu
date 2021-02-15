<?php

  namespace Neu\Annotations;

  use Attribute;

  #[Attribute]
  class Length {
    public function __construct(
      public int $min = -1,
      public int $max = -1,
      public int $exact = -1,
    ) {
    }
  }
