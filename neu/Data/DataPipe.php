<?php

  namespace Neu\Data;

  class DataPipe {
    public function __construct(private array $data) {
    }

    /**
     * @return array
     */
    public function data(): array {
      return $this->data;
    }

    /**
     * @param callable $cb
     * @return DataPipe
     */
    public function map(callable $cb): DataPipe {
      $mapped = [];
      foreach ($this->data as $key => $value) {
        $mapped[$key] = $cb($value, $key);
      }
      return new DataPipe($mapped);
    }

    /**
     * @param callable $cb
     * @param bool $preserveKeys
     * @return DataPipe
     */
    public function filter(callable $cb, bool $preserveKeys = false): DataPipe {
      $filtered = array_filter($this->data, $cb, ARRAY_FILTER_USE_BOTH);
      if (!$preserveKeys) {
        $filtered = array_values($filtered);
      }
      return new DataPipe($filtered);
    }

    /**
     * @param callable $cb
     * @param mixed|null $initial
     * @param bool $asPipe
     * @return DataPipe|mixed
     */
    public function reduce(callable $cb, mixed $initial = null, bool $asPipe = false): mixed {
      if (is_null($initial)) {
        $first_key = array_keys($this->data)[0];
        $first_field = $this->data[$first_key];
        $initial = match (gettype($first_field)) {
          'integer' => 0,
          'double' => 0.0,
          'string' => '',
          'array' => [],
          default => null
        };
      }
      $new_data = array_reduce($this->data, $cb, $initial);
      return $asPipe ? new DataPipe($new_data) : $new_data;
    }

    /**
     * @return array
     */
    public function keys(): array {
      return array_keys($this->data);
    }

    /**
     * @return array
     */
    public function values(): array {
      return array_values($this->data);
    }

    /**
     * @param bool $preserveKeys
     * @return DataPipe
     */
    public function reverse(bool $preserveKeys = false): DataPipe {
      if ($preserveKeys) {
        $result = [];
        foreach (array_reverse(array_keys($this->data)) as $k) {
          $result[$k] = $this->data[$k];
        }
      } else {
        $result = array_reverse([...$this->data]);
      }
      return new DataPipe($result);
    }
  }
