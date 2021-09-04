<?php


  namespace Neu\Http;

  use Neu\Annotations\Produces;
  use Neu\Annotations\Status;
  use Neu\Errors\TypeMismatch;
  use Neu\Model;
  use Stringable;
  use function Neu\preparedModelToXml;

  class Response {
    public function __construct(
      public int $status = StatusCode::Ok,
      public mixed $body = '',
      public array $headers = [],
    ) {
    }

    public function header(string $name, ?string $value = null): Response|string {
      if ($value) {
        $this->headers[$name] = $value;
        return $this;
      } else {
        return $this->headers[$name];
      }
    }

    public function contentType(?string $contentType): string|Response {
      if ($contentType) {
        return $this->header('Content-Type', $contentType);
      }
      return $this->header('Content-Type');
    }

    public function status(?int $code = null): Response|int {
      if ($code) {
        $this->status = $code;
        return $this;
      } else {
        return $this->status;
      }
    }

    public function send(): void {
      foreach ($this->headers as $header => $value) {
        header($header . ': ' . $value);
      }

      http_response_code($this->status);

      $body_is_valid = is_scalar($this->body) || $this->body instanceof Stringable;
      if (!$body_is_valid) {
        // TODO define proper semantic exception
        throw new \Exception('Cannot send response, if the body cannot be converted to a string!');
      }

      echo $this->body;
    }

    public static function not_found(): Response {
      return new Response(
        status: StatusCode::NotFound
      );
    }
  }
