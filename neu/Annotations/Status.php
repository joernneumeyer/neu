<?php


  namespace Neu\Annotations;

  use Attribute;
  use Neu\Http\StatusCode;

  #[Attribute(Attribute::TARGET_METHOD)]
  class Status {
    public function __construct(public int $code) {
    }
  }
