<?php

  namespace Neu;

  use ErrorException;
  use Neu\Annotations\Controller;
  use RecursiveDirectoryIterator;
  use RecursiveIteratorIterator;
  use ReflectionClass;
  use ReflectionException;
  use SplFileInfo;





  class Neu {
    public static function bootstrap(): void {
      $controller_class_paths = self::fetch_all_controller_class_paths();
      foreach ($controller_class_paths as $controller_class_path) {
        require $controller_class_path;
      }
      set_error_handler([Neu::class, 'exceptions_error_handler']);
      register_shutdown_function([Neu::class, 'fatal_handler']);
    }

    public static function fatal_handler() {
      $error = error_get_last();
      if ($error) {
        throw new ErrorException(message: $error['message'], severity: $error['type'], filename: $error['file'], line: $error['line']);
      }
    }

    public static function exceptions_error_handler($severity, $message, $filename, $lineno) {
      throw new ErrorException($message, 0, $severity, $filename, $lineno);
    }

    private static function fetch_all_controller_class_paths(): array {
      $controllers_directory = join(DIRECTORY_SEPARATOR, [APP_ROOT, 'app', 'Http', 'Controllers']);
      $dir_iter = new RecursiveDirectoryIterator($controllers_directory);
      $iter = new RecursiveIteratorIterator($dir_iter);
      return pipe(iterator_to_array($iter))
        ->filter(fn(SplFileInfo $file) => $file->isFile(), preserveKeys: true)
        ->map(fn(SplFileInfo $file) => $file->getPath())
        ->keys();
    }

    /**
     * @return ReflectionClass[]
     * @throws ReflectionException
     */
    public static function load_controller_reflections(): array {
      return pipe(get_declared_classes())
        ->filter(fn($name) => str_starts_with($name, 'App\Http'))
        ->map(fn($name) => new ReflectionClass($name))
        ->filter(fn($class) => $class->getAttributes(Controller::class))
        ->data();
    }
  }
