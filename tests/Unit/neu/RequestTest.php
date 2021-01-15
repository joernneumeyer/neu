<?php

  use Neu\Http\Request;

  it('should properly check for params', function () {
    $request = new Request(params: ['username' => 'foobar']);
    expect($request->has_param('username'))->toBeTrue();
  });

  it('should properly check for query', function () {
    $request = new Request(query: ['page' => '3']);
    expect($request->has_query('page'))->toBeTrue();
  });

  it('should properly check for body', function () {
    $request = new Request(body: ['password' => 'f00b4r']);
    expect($request->has_body('password'))->toBeTrue();
  });

  it('should return null, if the param does not exist', function () {
    $request = new Request();
    expect($request->param('username'))->toBeNull();
  });

  it('should return null, if the body field does not exist', function () {
    $request = new Request();
    expect($request->body('password'))->toBeNull();
  });

  it('should return null, if the query does not exist', function () {
    $request = new Request();
    expect($request->query('page'))->toBeNull();
  });

  it('should return the param', function () {
    $request = new Request(params: ['username' => 'johndoe']);
    expect($request->param('username'))->toEqual('johndoe');
  });

  it('should return the body', function () {
    $request = new Request(body: ['password' => 'f00b4r']);
    expect($request->body('password'))->toEqual('f00b4r');
  });

  it('should return the query', function () {
    $request = new Request(query: ['page' => '4']);
    expect($request->query('page'))->toEqual('4');
  });


