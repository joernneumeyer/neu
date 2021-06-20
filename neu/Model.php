<?php


  namespace Neu;


  use Neu\Annotations\ModelProperty;
  use Neu\Errors\InvalidModelData;
  use Neu\Errors\NonTrivialConstructor;
  use function Neu\Pipe7\pipe;

  class Model {
    public static function prepareForSerialization(mixed $model): float|int|bool|array|string|null|\stdClass {
      if (is_scalar($model)) {
        return $model;
      }
      if (is_array($model)) {
        return pipe($model)->map(fn($m) => Model::prepareForSerialization($m))->toArray();
      }
      if (is_null($model)) {
        return null;
      }
      $obj = new \stdClass();
      $model_ref = new \ReflectionObject($model);
      $props_ref = $model_ref->getProperties();
      foreach ($props_ref as $prop) {
        $attr = $prop->getAttributes(ModelProperty::class);
        if (!$attr) {
          continue;
        }
        $prop->setAccessible(true);
        $model_prop_value = $prop->getValue($model);
        if (is_scalar($model_prop_value)) {
          $obj->{$prop->getName()} = $model_prop_value;
        } else {
          $obj->{$prop->getName()} = self::prepareForSerialization($model_prop_value);
        }
      }
      return $obj;
    }

    /**
     * @param $data
     * @param string $into_type
     * @return object
     * @throws InvalidModelData
     * @throws NonTrivialConstructor
     * @throws \ReflectionException
     */
    public static function from($data, string $into_type): object {
      if (is_string($data)) {
        $data = json_decode($data);
      } else if (is_array($data)) {
        $data = json_decode(json_encode($data));
      }
      try {
        $type_ref = new \ReflectionClass($into_type);
      } catch (\ReflectionException $e) {
        return $data;
      }
      if (($type_ref->getConstructor()?->getNumberOfRequiredParameters() ?? 0) !== 0) {
        throw new NonTrivialConstructor();
      }
      $instance = $type_ref->newInstance();
      $props_ref = $type_ref->getProperties();
      $invalid_properties = [];
      foreach ($props_ref as $prop) {
        $attr = $prop->getAttributes(ModelProperty::class);
        if (!$attr) {
          continue;
        }
        $prop->setAccessible(true);
        $prop_type = $prop->getType()?->getName();
        if (!isset($data->{$prop->getName()})) {
          $invalid_properties[] = $prop->getName();
          continue;
        }
        $data_value = $data->{$prop->getName()};
        if (is_null($prop_type) || is_scalar($data_value)) {
          $prop->setValue($instance, $data_value);
        } else {
          try {
            $sub_object = self::from($data_value, $prop_type);
            $prop->setValue($instance, $sub_object);
          } catch (InvalidModelData $e) {
            $sub_errors = pipe($e->with_invalid_fields)->map(fn($field) => $prop->getName() . '.' . $field)->toArray();
            array_push($invalid_properties, ...$sub_errors);
          }
        }
      }
      if ($invalid_properties) {
        throw new InvalidModelData(with_invalid_fields: $invalid_properties);
      }
      return $instance;
    }
  }
