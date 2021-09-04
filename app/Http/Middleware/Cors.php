<?php

  namespace App\Http\Middleware;

  use Neu\Http\PostMiddleware;
  use Neu\Http\Response;

  class Cors implements PostMiddleware {
    public function __construct(
      private Response $response
    ) {
    }

    function apply(): void {
      $this->response->headers['Access-Control-Allow-Origin'] = '*';
      $this->response->headers['Access-Control-Allow-Headers'] = '*';
      $this->response->headers['Access-Control-Allow-Method'] = '*';
    }
  }
