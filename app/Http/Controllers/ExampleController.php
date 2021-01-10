<?php
  namespace App\Http\Controllers;

  use Neu\Http\Controller;
  use Neu\Http\Route;

  #[Controller]
  class ExampleController {
    #[Route(method: 'GET')]
    public function example() {
      return 'Hello, World!';
    }

    public function some_other_method() {

    }
  }
