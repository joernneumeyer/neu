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
  use Neu\Http\StatusCode;

  #[Controller(path: '/moinsen')]
  class ExampleController {
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
    public function example_post(#[Body] SimpleUser $user) {
      return $user;
    }
  }
