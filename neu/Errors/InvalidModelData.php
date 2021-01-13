<?php


  namespace Neu\Errors;


  use Exception;
  use Throwable;

  class InvalidModelData extends Exception {
    public function __construct(public array $with_invalid_fields, $message = "", $code = 0, Throwable $previous = null) {
      parent::__construct($message, $code, $previous);
    }
  }
