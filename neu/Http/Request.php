<?php


  namespace Neu\Http;


  class Request {
    public function __construct(
      public string $method = 'GET',
      public string $path = '/',
      public array $route_parameters = [],
      public array $query = [],
      public array $body = [],
    ) {
    }

    public static function from_global_state(): Request {
      $protocol = $_SERVER['SERVER_PROTOCOL'];
      $post = file_get_contents('php://input');
      $post = json_decode($post, associative: true);
      $post = is_null($post) ? $_POST : $post;
      return new Request(
        method: $_SERVER['REQUEST_METHOD'],
        path: $_SERVER['PHP_SELF'],
        query: $_GET,
        body: $post,
      );
    }

    /**
     * Check for the existence of the given route parameter.
     * @param string $name
     * @return bool
     */
    public function has_param(string $name): bool {
      return isset($this->route_parameters[$name]);
    }

    public function has_query(string $name): bool {
      return isset($this->query[$name]);
    }

    public function has_body(string $name): bool {
      return isset($this->body[$name]);
    }

    /**
     * Load the value of the specified route parameter.
     * If the parameter is not set, the default value will be returned.
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function param(string $name, $default = null): mixed {
      return $this->route_parameters[$name] ?? $default;
    }

    public function query(string $name, $default = null): mixed {
      return $this->query[$name] ?? $default;
    }

    public function body(string $name, $default = null): mixed {
      return $this->body[$name] ?? $default;
    }
  }
