<?php


  namespace Neu\Http;


  class Request {
    public function __construct(public string $method, public string $path) {
    }

    public static function from_global_state() {
      return new Request(
        method: $_SERVER['REQUEST_METHOD'],
        path: $_SERVER['REQUEST_URI']
      );
    }
  }
