<?php

  use Neu\Annotations\ModelProperty;
  use Neu\Errors\InvalidModelData;
  use Neu\Model;

  class SimpleUser {
    public function __construct(#[ModelProperty] public string $username = '') {
    }
  }

  class UserWithNonAnnotatedProperties extends SimpleUser {
    public function __construct(string $username = '', public string $nonAnnotated) {
      $this->username = $username;
    }
  }

  class UserWithId extends SimpleUser {
    #[ModelProperty]
    public int $id;
  }

  class Address {
    #[ModelProperty]
    public string $city;
  }

  class UserWithAddress extends UserWithId {
    #[ModelProperty]
    public Address $address;
  }

  it('should be able to construct a simple type', function () {
    $userData = ['username' => 'John'];
    $user     = Model::from($userData, SimpleUser::class);
    expect($user)->toBeInstanceOf(SimpleUser::class);
    expect($user->username)->toEqual('John');
  });

  it('should properly convert the text id into an int', function () {
    $userData = ['username' => 'John', 'id' => '44'];
    $user     = Model::from($userData, UserWithId::class);
    expect($user)->toBeInstanceOf(UserWithId::class);
    expect($user->id)->toEqual(44);
  });

  it('should properly convert the sub-object into a proper instance', function () {
    $userData = ['username' => 'John', 'id' => '44', 'address' => ['city' => 'Venlo']];
    $user     = Model::from($userData, UserWithAddress::class);
    expect($user)->toBeInstanceOf(UserWithAddress::class);
    expect($user->address->city)->toEqual('Venlo');
  });

  it('should throw, if invalid model data is supplied', function () {
    $userData = ['username' => 'John', 'id' => '44'];
    Model::from($userData, UserWithAddress::class);
  })->throws(InvalidModelData::class);

  it('should provide information, which properties failed', function () {
    try {
      $userData = ['username' => 'John', 'id' => '44'];
      Model::from($userData, UserWithAddress::class);
    } catch (InvalidModelData $e) {
      expect($e->with_invalid_fields)->toMatchArray(['address']);
    }
  });

  it('should provide information, which nested properties failed', function () {
    try {
      $userData = ['username' => 'John', 'id' => '44', 'address' => []];
      Model::from($userData, UserWithAddress::class);
    } catch (InvalidModelData $e) {
      expect($e->with_invalid_fields)->toMatchArray(['address.city']);
    }
  });

  it('should return an instance of stdClass, with the proper attribute value', function () {
    $user           = new SimpleUser();
    $user->username = 'John Doe';
    $prepared       = Model::prepareForSerialization($user);
    expect($prepared)->toBeInstanceOf(stdClass::class);
    expect($prepared->username)->toEqual($user->username);
  });

  it('should properly transform complex members to stdClass', function () {
    $user                = new UserWithAddress();
    $user->id            = 0;
    $user->username      = 'John Doe';
    $user->address       = new Address();
    $user->address->city = 'Venlo';
    $prepared            = Model::prepareForSerialization($user);
    expect($prepared->address)->toBeInstanceOf(stdClass::class);
    expect($prepared->address->city)->toEqual('Venlo');
  });

  it('just returns scalar values', function ($v) {
    $prepared = Model::prepareForSerialization($v);
    expect($prepared)->toEqual($v);
  })->with(['foo', 4, 5.6, false]);

  it('should convert an array of model to an array of stdClass', function () {
    $models = [new SimpleUser('foobar'), new SimpleUser('John Doe')];
    $prepared = Model::prepareForSerialization($models);
    expect($prepared)->toBeArray();
    expect($prepared[0]->username)->toEqual('foobar');
    expect($prepared[1]->username)->toEqual('John Doe');
  });

  it('should return null, if null is given', function () {
    expect(Model::prepareForSerialization(null))->toBeNull();
  });

  it('should skip attributes, which do not have a ModelProperty annotation', function () {
    $user = new UserWithNonAnnotatedProperties('John Doe', 'something else');
    $prepared = Model::prepareForSerialization($user);
    expect($prepared->username)->toEqual('John Doe');
    expect($prepared)->not->toHaveProperty('nonAnnotated');
  });

  it('should parse incoming JSON data', function () {
    $data = json_encode(new SimpleUser('John Doe'));
    $model = Model::from($data, SimpleUser::class);
    expect($model->username)->toEqual('John Doe');
  });

  it('should return the model as stdClass, if the given model type is invalid', function () {
    $data = json_encode(new SimpleUser('John Doe'));
    $model = Model::from($data, 'foobar');
    expect($model)->toBeInstanceOf(stdClass::class);
    expect($model->username)->toEqual('John Doe');
  });
