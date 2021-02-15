<?php

  namespace Neu\Data;

  class Reducers {
    /**
     * @return \Closure
     */
    public static function sum() {
      return fn(int|float $carry, int|float $i): int|float => $carry + $i;
    }

    /**
     * @param string $with
     * @return \Closure
     */
    public static function join(string $with) {
      return fn(string $carry, string $i): string => $carry . $with . $i;
    }

    /**
     * @param array $a
     * @return array
     */
    private static function flattenArray(array $a) {
      $result = [];
      foreach ($a as $item) {
        if (is_array($item)) {
          $flat = self::flattenArray($item);
          foreach ($flat as $f) {
            $result[] = $f;
          }
        } else {
          $result[] = $item;
        }
      }
      return $result;
    }

    /**
     * @return \Closure
     */
    public static function flatMap() {
      return function (array $carry, mixed $i): array {
        if (is_array($i)) {
          $flat = self::flattenArray($i);
          $carry = array_merge($carry, $flat);
        } else {
          $carry[] = $i;
        }
        return $carry;
      };
    }
  }
