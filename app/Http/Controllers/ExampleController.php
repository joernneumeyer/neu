<?php
  namespace App\Http\Controllers;

  use App\Models\SimpleUser;
  use Neu\Annotations\HandlerParameters\Body;
  use Neu\Annotations\HandlerParameters\Param;
  use Neu\Annotations\HandlerParameters\Query;
  use Neu\Annotations\Controller;
  use Neu\Annotations\Produces;
  use Neu\Annotations\Route;
  use Neu\Annotations\Status;
  use Neu\Dal\ModelRepository;
  use Neu\Http\ContentType;
  use Neu\Http\StatusCode;

  #[Controller(path: '/moinsen')]
  class ExampleController {
    public function __construct(
      private ModelRepository $repository
    ) {
    }

    #[Route(method: 'GET', path: '/{username}')]
    public function example(#[Param] string $username, #[Query] ?int $bar) {
      $response = "Hello, $username!";
      if (!is_null($bar)) {
        $response .= " I'm at this bar $bar.";
      }
      return $response;
    }

    #[Route(method: 'POST')]
    #[Status(code: StatusCode::Created)]
    #[Produces(contentType: ContentType::ApplicationXml)]
    public function example_post(#[Body] SimpleUser $user) {
      return $user;
    }
  }
