<?php


  namespace Neu\Cdi;


  use Closure;
  use Neu\Annotations\HandlerParameters\Body;
  use Neu\Annotations\HandlerParameters\Param;
  use Neu\Annotations\HandlerParameters\Query;
  use Neu\Annotations\InjectUnique;
  use Neu\Errors\InvalidDependencyLoadMode;
  use Neu\Errors\TryToConstructUnregisteredDependency;
  use Neu\Errors\TypeMismatch;
  use Neu\Errors\UnresolvableDependencyType;
  use Neu\Http\Request;
  use Neu\Model;
  use ReflectionClass;
  use ReflectionException;
  use ReflectionFunction;
  use ReflectionMethod;

  class DependencyResolver {
    public const LoadUnique = 0;
    public const LoadShared = 1;

    /**
     * @var DependencyProvider[]
     */
    private array $providers = [];

    public function __construct() {
    }

    /**
     * @param Closure $factory
     * @param string $for_type
     * @throws TypeMismatch
     */
    public function register(Closure $factory, string $for_type) {
      $this->providers[$for_type] = new DependencyProvider($factory, $for_type);
    }

    /**
     * @param string $for_type
     * @return bool
     */
    public function has_factory(string $for_type): bool {
      return isset($this->providers[$for_type]);
    }

    /**
     * @param string $for_type
     * @param int $with_load_mode
     * @param bool $return_null_dont_throw
     * @return object|null
     * @throws InvalidDependencyLoadMode
     * @throws TryToConstructUnregisteredDependency
     */
    public function construct_dependency(string $for_type, int $with_load_mode = self::LoadShared, bool $return_null_dont_throw = false): object|null {
      if (!isset($this->providers[$for_type])) {
        if ($return_null_dont_throw) {
          return null;
        } else {
          throw new TryToConstructUnregisteredDependency();
        }
      }
      return match ($with_load_mode) {
        self::LoadUnique => $this->providers[$for_type]->constructObject(),
        self::LoadShared => $this->providers[$for_type]->loadSharedObject(),
        default => throw new InvalidDependencyLoadMode(),
      };
    }

    /**
     * @param string $of_type
     * @return object|null
     * @throws InvalidDependencyLoadMode
     * @throws TryToConstructUnregisteredDependency
     * @throws UnresolvableDependencyType
     * @throws ReflectionException
     */
    public function construct_object(string $of_type): object|null {
      try {
        $ref_class = new ReflectionClass($of_type);
      } catch (ReflectionException $e) {
        throw new UnresolvableDependencyType(previous: $e);
      }
      $ctor_ref = $ref_class->getConstructor();
      if (is_null($ctor_ref) || $ctor_ref->getNumberOfParameters() === 0) {
        return new $of_type;
      }
      $params = $ctor_ref->getParameters();
      $args   = [];
      foreach ($params as $param) {
        $param_type_name = $param->getType()?->getName();
        if (is_null($param_type_name)) {
          throw new UnresolvableDependencyType();
        }
        $load_mode = count($param->getAttributes(InjectUnique::class)) > 0
          ? self::LoadUnique
          : self::LoadShared;
        $args[] = $this->has_factory($param_type_name) ? $this->construct_dependency($param_type_name, with_load_mode: $load_mode) : $this->construct_object($param_type_name);
      }
      return $ref_class->newInstance(...$args);
    }

    /**
     * @param Request $with_request
     * @param ReflectionMethod $for_handler
     * @return array
     */
    public function resolve_handler_arguments(Request $with_request, ReflectionMethod $for_handler): array {
      $params = $for_handler->getParameters();
      $args   = [];
      foreach ($params as $param) {
        $is_param = $param->getAttributes(Param::class);
        $is_query = $param->getAttributes(Query::class);
        $is_body  = $param->getAttributes(Body::class);
        if ($is_param) {
          /** @var Param $param_attribute */
          $param_attribute = $is_param[0]->newInstance();
          $param_name      = $param_attribute->name ?: $param->getName();
          $param_value     = $with_request->param($param_name, $param_attribute->default);
          $args[]          = $param_value;
          continue;
        }
        if ($is_query) {
          /** @var Query $query_attribute */
          $query_attribute = $is_query[0]->newInstance();
          $param_name      = $query_attribute->name ?: $param->getName();
          $param_value     = $with_request->query($param_name, $query_attribute->default);
          $args[]          = $param_value;
          continue;
        }
        if ($is_body) {
          $param_type = $param->getType()?->getName();
          if (is_null($param_type)) {
            $param_value = json_decode(json_encode($with_request->body));
          } else {
            $param_value = Model::from(data: $with_request->body, into_type: $param_type);
          }
          $args[] = $param_value;
        }
      }
      return $args;
    }
  }
