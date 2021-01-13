<?php


  namespace Neu\Http;


  use Neu\Model;

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

    public static function from(mixed $result): Response|null {
      if ($result instanceof Response) {
        return $result;
      }
      if (is_string($result)) {
        return new Response(
          body: $result,
          headers: ['Content-Type' => ContentType::TextPlain]
        );
      }
      if (is_object($result)) {
        return new Response(
          body: Model::toJson($result),
          headers: ['Content-Type' => 'application/json']
        );
      }

      return null;
    }

    public function send(): void {
      foreach ($this->headers as $header => $value) {
        header($header . ': ' . $value);
      }

      http_response_code($this->status);

      echo $this->body;
    }

    public static function not_found(): Response {
      return new Response(
        status: StatusCode::NotFound,
        status_text: 'Not Found'
      );
    }
  }
