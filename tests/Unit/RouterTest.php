<?php

  use Neu\Errors\InvalidRouteSupplied;
  use Neu\Http\Router;

  $routes_valid = [
    ['/hello', '/hello'],
    ['/world', '/world'],
    ['/foo/bar', '/foo/bar'],
    ['/foo/abc-bar', '/foo/abc-bar'],
    ['/foo/abc-bar/', '/foo/abc-bar'],
    ['/foo/abc-bar', '/foo/abc-bar/'],
  ];

  $routes_invalid = [
    ['/hello', '/world'],
    ['/hel', '/hello'],
    ['/hello', '/hel'],
    ['/foo', '/bar'],
    ['/foo', '/alice'],
    ['/foo/bar', '/foo/baz'],
    ['/fo0/bar', '/foo/bar'],
  ];

  $routes_valid_with_parameters = [
    ['/{foo}', '/hello', ['foo' => 'hello']],
    ['/{foo}/baz', '/baz/baz', ['foo' => 'baz']],
    ['/users/a-{id}', '/users/a-4432', ['id' => '4432']],
    ['/users/a-{id}fo', '/users/a-4432fo', ['id' => '4432']],
    ['/users/t-{id}/about', '/users/t-fg6a/about', ['id' => 'fg6a']],
  ];

  $routes_invalid_with_parameters = [
    ['/{foo}', '/hello/bar'],
    ['/{foo}/baz', '/foo/a/baz'],
    ['/users/a-{id}', '/users/a4432'],
    ['/users/t-{id}/about', '/users/tfg6a/about'],
  ];

  $routes_valid_with_parameters_and_regex = [
    ['/{foo:[abc]+}', '/abbcab', ['foo' => 'abbcab']],
    ['/user/{id:\d+}', '/user/1254', ['id' => '1254']],
  ];

  $routes_invalid_with_parameters_and_regex = [
    ['/{foo:[abc]+}', '/abb4cab'],
    ['/user/{id:\d+}', '/user/12a54'],
  ];

  $malformated_routes = [
    ['/foo/{param', '/foo/bar']
  ];

  it('should match correct paths', function(string $route, string $path) {
    expect(Router::match_path($path, $route))->toBeArray();
  })->with($routes_valid);

  it('should not match a simple invalid path', function(string $route, string $path) {
    expect(Router::match_path($path, $route))->toBeFalse();
  })->with($routes_invalid);

  it('should be able to correctly match routes with parameters', function(string $route, string $path, array $actual_route_parameters) {
    $route_parameters = Router::match_path($path, $route);
    expect($route_parameters)->toMatchArray($actual_route_parameters);
  })->with($routes_valid_with_parameters);

  it('should not match routes with parameters', function(string $route, string $path) {
    $route_parameters = Router::match_path($path, $route);
    expect($route_parameters)->toBeFalse();
  })->with($routes_invalid_with_parameters);

  it('should match valid paths to route constraints', function(string $route, string $path, array $actual_route_parameters) {
    $route_parameters = Router::match_path($path, $route);
    expect($route_parameters)->toMatchArray($actual_route_parameters);
  })->with($routes_valid_with_parameters_and_regex);

  it('should not match paths with violated parameter constraints', function(string $route, string $path) {
    $route_parameters = Router::match_path($path, $route);
    expect($route_parameters)->toBeFalse();
  })->with($routes_invalid_with_parameters_and_regex);

  it('should throw, if a malformated route is supplied', function (string $route, string $path) {
    Router::match_path($path, $route);
  })->with($malformated_routes)->throws(InvalidRouteSupplied::class);
