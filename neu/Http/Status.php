<?php


  namespace Neu\Http;

  use Attribute;

  #[Attribute(Attribute::TARGET_METHOD)]
  class Status {
    public function __construct(public int $status_code = StatusCode::Ok) {
    }
  }
