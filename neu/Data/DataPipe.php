<?php

  namespace Neu\Data;

  class DataPipe {
    public function __construct(private array $data) {
    }

    public function data() {
      return $this->data;
    }

    public function map(callable $cb): DataPipe {
      $mapped = array_map($cb, $this->data);
      return new DataPipe($mapped);
    }

    public function filter(callable $cb): DataPipe {
      $filtered = array_filter($this->data, $cb);
      return new DataPipe($filtered);
    }

    public function reduce(callable $cb, mixed $initial = null) {
      return array_reduce($this->data, $cb, $initial);
    }

    public function keys(): array {
      return array_keys($this->data);
    }

    public function values(): array {
      return array_values($this->data);
    }

  }
