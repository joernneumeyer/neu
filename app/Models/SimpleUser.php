<?php

  namespace App\Models;

  use Neu\Annotations\ModelProperty;

  class SimpleUser {
    #[ModelProperty]
    public string $username = 'John';
    #[ModelProperty]
    private ?Address $address = null;
  }
