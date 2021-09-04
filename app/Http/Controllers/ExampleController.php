<?php


  namespace App\Http\Controllers;


  use App\Http\Middleware\Cors;
  use Neu\Annotations\Consumes;
  use Neu\Annotations\Controller;
  use Neu\Annotations\Produces;
  use Neu\Annotations\Route;
  use Neu\Annotations\UseMiddleware;
  use Neu\Http\ContentType;
  use Neu\Http\Request;

  #[Controller]
  class ExampleController {
    public function __construct(
      private Request $request
    ) {
    }

    /**
     * @return int[]
     */
    #[Route(method: 'POST')]
    #[Produces(ContentType::ApplicationJson)]
    #[Consumes(ContentType::ApplicationJson)]
    public function hello() {
      return [1,2,3];
    }

    /**
     *
     */
    #[Route(method: 'GET')]
    #[Produces(contentType: ContentType::TextPlain)]
    #[UseMiddleware(name: Cors::class)]
    public function someRandomHandler() {
      return 'response from handler!';
    }
  }
