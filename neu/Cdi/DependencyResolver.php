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
     * @param string $forType
     * @throws TypeMismatch
     */
    public function register(Closure $factory, string $forType) {
      $this->providers[$forType] = new DependencyProvider($factory, $forType);
    }

    /**
     * @param string $for_type
     * @return bool
     */
    public function hasFactory(string $for_type): bool {
      return isset($this->providers[$for_type]);
    }

    /**
     * @param string $forType
     * @param int $withLoadMode
     * @param bool $returnNullDontThrow
     * @return object|null
     * @throws InvalidDependencyLoadMode
     * @throws TryToConstructUnregisteredDependency
     */
    public function constructDependency(string $forType, int $withLoadMode = self::LoadShared, bool $returnNullDontThrow = false): object|null {
      if (!isset($this->providers[$forType])) {
        if ($returnNullDontThrow) {
          return null;
        } else {
          throw new TryToConstructUnregisteredDependency();
        }
      }
      return match ($withLoadMode) {
        self::LoadUnique => $this->providers[$forType]->constructObject(),
        self::LoadShared => $this->providers[$forType]->loadSharedObject(),
        default => throw new InvalidDependencyLoadMode(),
      };
    }

    /**
     * @param string $ofType
     * @return object|null
     * @throws InvalidDependencyLoadMode
     * @throws TryToConstructUnregisteredDependency
     * @throws UnresolvableDependencyType
     * @throws ReflectionException
     */
    public function constructObject(string $ofType): object|null {
      try {
        $ref_class = new ReflectionClass($ofType);
      } catch (ReflectionException $e) {
        throw new UnresolvableDependencyType(previous: $e);
      }
      $ctor_ref = $ref_class->getConstructor();
      if (is_null($ctor_ref) || $ctor_ref->getNumberOfParameters() === 0) {
        return new $ofType;
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
        $args[] = $this->hasFactory($param_type_name) ? $this->constructDependency($param_type_name, withLoadMode: $load_mode) : $this->constructObject($param_type_name);
      }
      return $ref_class->newInstance(...$args);
    }

    /**
     * @param Request $withRequest
     * @param ReflectionMethod $forHandler
     * @return array
     */
    public function resolveHandlerArguments(Request $withRequest, ReflectionMethod $forHandler): array {
      $params = $forHandler->getParameters();
      $args   = [];
      foreach ($params as $param) {
        $is_param = $param->getAttributes(Param::class);
        $is_query = $param->getAttributes(Query::class);
        $is_body  = $param->getAttributes(Body::class);
        if ($is_param) {
          /** @var Param $param_attribute */
          $param_attribute = $is_param[0]->newInstance();
          $param_name      = $param_attribute->name ?: $param->getName();
          $param_value     = $withRequest->param($param_name, $param_attribute->default);
          $args[]          = $param_value;
          continue;
        }
        if ($is_query) {
          /** @var Query $query_attribute */
          $query_attribute = $is_query[0]->newInstance();
          $param_name      = $query_attribute->name ?: $param->getName();
          $param_value     = $withRequest->query($param_name, $query_attribute->default);
          $args[]          = $param_value;
          continue;
        }
        if ($is_body) {
          $param_type = $param->getType()?->getName();
          if (is_null($param_type)) {
            $param_value = json_decode(json_encode($withRequest->body));
          } else {
            $param_value = Model::from(data: $withRequest->body, intoType: $param_type);
          }
          $args[] = $param_value;
        }
      }
      return $args;
    }
  }
