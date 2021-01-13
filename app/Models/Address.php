<?php


  namespace App\Models;


  use Neu\Annotations\ModelProperty;

  class Address {
    #[ModelProperty]
    private string $city;
  }
