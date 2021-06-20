<?php

  namespace Neu;

  use SimpleXMLElement;

  function preparedModelToXml(object $obj, ?SimpleXMLElement $ref = null, string $tag = ''): SimpleXMLElement {
    if (str_contains($tag, '\\')) {
      $tag = substr($tag, strrpos($tag, '\\') + 1);
    }

    if (!$ref) {
      $ref = new SimpleXMLElement('<' . $tag . '/>');
    } else {
      $ref = $ref->addChild($tag);
    }

    foreach (get_object_vars($obj) as $key => $value) {
      if (is_scalar($value)) {
        $ref->addChild($key, $value);
      } else {
        preparedModelToXml($value, $ref, $key);
      }
    }

    return $ref;
  }
