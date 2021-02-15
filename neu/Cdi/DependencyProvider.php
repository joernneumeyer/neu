<?php


  namespace Neu\Cdi;


  use Closure;
  use Neu\Errors\TypeMismatch;

  class DependencyProvider {
    public function __construct(
      public Closure $factory,
      public string $for_type,
      public ?object $shared_instance = null
    ) {
      if (!is_null($this->shared_instance) && get_class($this->shared_instance) !== $this->for_type) {
        throw new TypeMismatch('Trying to set shared instance with type mismatch!');
      }
    }

    /**
     * @return object
     */
    public function constructObject(): object {
      return ($this->factory)();
    }

    /**
     * @return object|null
     */
    public function loadSharedObject(): ?object {
      if (is_null($this->shared_instance)) {
        $this->shared_instance = $this->constructObject();
      }
      return $this->shared_instance;
    }
  }
