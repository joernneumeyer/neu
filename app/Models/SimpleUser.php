<?php

  namespace App\Models;

  use Neu\Annotations\Length;
  use Neu\Annotations\ModelProperty;

  class SimpleUser {
    #[ModelProperty]
    #[Length(min: 4)]
    public string $username = 'John';
    #[ModelProperty]
    private ?Address $address = null;
  }
