<?php


  namespace Neu\Cdi;


  use Closure;

  class DependencyProvider {
    public function __construct(
      public Closure $factory,
      public string $for_type,
      public ?object $shared_instance = null
    ) {
      if (!is_null($this->shared_instance) && get_class($this->shared_instance) !== $this->for_type) {
        throw new \Exception('Trying to set shared instance with type mismatch!');
      }
    }

    public function construct_object(): object {
      return ($this->factory)();
    }

    public function load_shared_object() {
      if (is_null($this->shared_instance)) {
        $this->shared_instance = $this->construct_object();
      }
      return $this->shared_instance;
    }
  }
