<?php

  // --min=70 --coverage --coverage-html coverage

  use Neu\Cdi\DependencyResolver;
  use Neu\Errors\InvalidDependencyLoadMode;
  use Neu\Errors\TryToConstructUnregisteredDependency;
  use Neu\Errors\UnresolvableDependencyType;
  use Neu\Http\Request;
  use Neu\Http\Response;

  class ClassWithDependencies {
    public function __construct(public Request $request) {
    }
  }

  class UsesUnregisteredType {
    public function __construct(ClassWithDependencies $class_with_dependencies) {
    }
  }

  class UsesPrimitiveParameters {
    public function __construct(string $foo) {
    }
  }

  class DoesNotHaveAnAttachedType {
    public function __construct($foo) {
    }
  }

  beforeEach(function () {
    $this->dr = $dr = new DependencyResolver();
    $dr->register(factory: fn() => new Request(), for_type: Request::class);
    $dr->register(factory: fn() => new Response(), for_type: Response::class);
  });

  it('should generate instances of the proper type', function () {
    $req = $this->dr->construct_dependency(for_type: Request::class);
    $res = $this->dr->construct_dependency(for_type: Response::class);
    expect($req)->toBeInstanceOf(Request::class);
    expect($res)->toBeInstanceOf(Response::class);
  });

  it('should generate shared instances by default', function () {
    $req_a       = $this->dr->construct_dependency(for_type: Request::class);
    $req_b       = $this->dr->construct_dependency(for_type: Request::class);
    $req_a->path = '/foobar';
    expect($req_b->path)->toEqual('/foobar');
  });

  it('should also be able to construct unique instances', function () {
    $req_a       = $this->dr->construct_dependency(for_type: Request::class, with_load_mode: DependencyResolver::LoadUnique);
    $req_b       = $this->dr->construct_dependency(for_type: Request::class, with_load_mode: DependencyResolver::LoadUnique);
    $req_a->path = '/foobar';
    expect($req_b->path)->toEqual('/');
  });

  it('should throw, if an instance of an unknown type is requested', function () {
    $this->dr->construct_dependency(for_type: DependencyResolver::class);
  })->throws(TryToConstructUnregisteredDependency::class);

  it('should throw, if an unsupported load mode is provided', function () {
    $this->dr->construct_dependency(for_type: Request::class, with_load_mode: -1);
  })->throws(InvalidDependencyLoadMode::class);

  it('should be able to resolve dependencies from registered providers', function () {
    $obj = $this->dr->construct_object(ClassWithDependencies::class);
    expect($obj)->toBeInstanceOf(ClassWithDependencies::class);
  });

  it('should be able to resolve dependencies from unregistered types, which can be resolved', function () {
    $obj = $this->dr->construct_object(UsesUnregisteredType::class);
    expect($obj)->toBeInstanceOf(UsesUnregisteredType::class);
  });

  it('should throw, if the constructor contains a parameter, which is of a primitive type', function () {
    $this->dr->construct_object(UsesPrimitiveParameters::class);
  })->throws(UnresolvableDependencyType::class);

  it('should throw, if the constructor contains a parameter, without a type', function () {
    $this->dr->construct_object(DoesNotHaveAnAttachedType::class);
  })->throws(UnresolvableDependencyType::class);
