<?php


  namespace Neu\Http;


  use Neu\Annotations\Produces;
  use Neu\Annotations\Status;
  use Neu\Model;
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

    /**
     * @param \ReflectionMethod $forHandler
     * @return $this
     */
    public function applyHandlerAnnotations(\ReflectionMethod $forHandler): self {
      if ($status = $forHandler->getAttributes(Status::class)) {
        $this->status = $status[0]->newInstance()->code;
      }
      if ($contentType = $forHandler->getAttributes(Produces::class)) {
        $contentType = $contentType[0]->newInstance()->contentType;
        $this->headers['Content-Type'] = $contentType;
        $bodyModel = Model::prepareForSerialization($this->body);
        $this->body = match ($contentType) {
          ContentType::ApplicationJson => json_encode($bodyModel),
          ContentType::ApplicationXml => preparedModelToXml($bodyModel, tag: get_class($this->body))->asXML(),
          default => (string)$this->body
        };
      }
      return $this;
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
        status: StatusCode::NotFound
      );
    }
  }
