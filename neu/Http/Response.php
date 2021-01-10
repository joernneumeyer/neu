<?php


  namespace Neu\Http;


  class Response {
    public function __construct(
      public int $status = StatusCode::Ok,
      public string $status_text = 'OK',
      public string $body = '',
      public array $headers = [],
    ) {
    }

    public function header(string $name, ?string $value): Response|string {
      if ($value) {
        $this->headers[$name] = $value;
        return $this;
      } else {
        return $this->headers[$name];
      }
    }

    public function status(?int $code): Response|int {
      if ($code) {
        $this->status = $code;
        return $this;
      } else {
        return $this->status;
      }
    }
  }
