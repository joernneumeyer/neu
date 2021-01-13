<?php

  namespace App\Models;

  use Neu\Annotations\ModelProperty;

  class SimpleUser {
    #[ModelProperty]
    private string $username = '';
    #[ModelProperty]
    private ?Address $address = null;
  }
