<?php

  use Neu\Annotations\ModelProperty;
  use Neu\Errors\InvalidModelData;
  use Neu\Model;

  class SimpleUser {
    #[ModelProperty]
    public string $username;
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
