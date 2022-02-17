<?php

  namespace App\Http\Controllers;

  use Neu\Annotations\Controller;
  use Neu\Annotations\Produces;
  use Neu\Annotations\Route;
  use Neu\Http\ContentType;
  use Neu\View;

  #[Controller]
  class ExampleController {
    public function __construct(
      private View $view,
    ) {
    }

    #[Route(method: 'GET')]
    #[Produces(ContentType::TextHtml)]
    public function welcome(): string {
      $this->view->assign([
        'numbers' => [4,8,2,4,76,879,53,123,346,658,78,678]
      ]);
      return $this->view->render('static/welcome');
    }
  }
