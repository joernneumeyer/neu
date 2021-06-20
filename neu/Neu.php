<?php

  namespace Neu;

  use Closure;
  use ErrorException;
  use Neu\Annotations\Controller;
  use RecursiveDirectoryIterator;
  use ReflectionClass;
  use ReflectionException;
  use SplFileInfo;
  use function Neu\Pipe7\pipe;


  class Neu {
    public static function bootstrap(): void {
      $controller_class_paths = self::fetch_all_controller_class_paths();
      foreach ($controller_class_paths as $controller_class_path) {
        require $controller_class_path;
      }
      set_error_handler(Closure::fromCallable([Neu::class, 'exceptions_error_handler']));
      register_shutdown_function(Closure::fromCallable([Neu::class, 'fatal_handler']));
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
      return pipe($dir_iter)
        ->filter(fn(SplFileInfo $file) => $file->isFile())
        ->map(fn(SplFileInfo $file) => $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename())
        ->toArray();
    }

    /**
     * @return ReflectionClass[]
     * @throws ReflectionException
     */
    public static function load_controller_reflections(): array {
      /** @var ReflectionClass[] $result */
      $result = pipe(get_declared_classes())
        ->filter(fn($name) => str_starts_with($name, 'App\Http'))
        ->map(fn($name) => new ReflectionClass($name))
        ->filter(fn($class) => $class->getAttributes(Controller::class))
        ->toArray(preserveKeys: false);
      return $result;
    }
  }
