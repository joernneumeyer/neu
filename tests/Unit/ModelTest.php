<?php

  use Neu\Annotations\ModelProperty;
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
    $user = Model::from($userData, SimpleUser::class);
    expect($user)->toBeInstanceOf(SimpleUser::class);
    expect($user->username)->toEqual('John');
  });

  it('should properly convert the text id into an int', function () {
    $userData = ['username' => 'John', 'id' => '44'];
    $user = Model::from($userData, UserWithId::class);
    expect($user)->toBeInstanceOf(UserWithId::class);
    expect($user->id)->toEqual(44);
  });

  it('should properly convert the sub-object into a proper instance', function () {
    $userData = ['username' => 'John', 'id' => '44', 'address' => ['city' => 'Venlo']];
    $user = Model::from($userData, UserWithAddress::class);
    expect($user)->toBeInstanceOf(UserWithAddress::class);
    expect($user->address->city)->toEqual('Venlo');
  });
