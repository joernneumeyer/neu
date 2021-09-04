<?php

  namespace Neu;

  use Neu\Annotations\Consumes;
  use Neu\Http\Request;
  use Neu\Http\Response;
  use ReflectionMethod;
  use Neu\Annotations\Produces;
  use Neu\Annotations\Status;
  use Neu\Http\ContentType;
  use Neu\Model;
  use function Neu\preparedModelToXml;

  return [
    Status::class => function(Request $request, Response $response, ReflectionMethod $handler) {
      $status = $handler->getAttributes(Status::class);
      if ([] !== $status) {
        $response->status = $status[0]->newInstance()->code;
      }
    },
    Produces::class => function(Request $request, Response $response, ReflectionMethod $handler) {
      /** @var Produces $produces */
      $produces = $handler->getAttributes(Produces::class)[0]->newInstance();
      $response->headers['Content-Type'] = $produces->contentType;
      $bodyModel = Model::prepareForSerialization($response->body);
      $response->body = match ($produces->contentType) {
        ContentType::ApplicationJson => json_encode($bodyModel),
        ContentType::ApplicationXml => preparedModelToXml($bodyModel, tag: get_class($response->body))->asXML(),
        default => (string)$response->body,
      };
    },
    Consumes::class => function(Request $request, Response $response, ReflectionMethod $handler) {
    /** @var Consumes $consumes */
      $consumes = $handler->getAttributes(Consumes::class)[0]->newInstance();
      $requestContentType = $request->headers['Content-Type'] ?? '';
      if ($consumes->contentType !== $requestContentType) {
        throw new \Exception("Invalid content type!");
      }
    },
  ];
