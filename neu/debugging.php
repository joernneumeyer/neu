<?php

  namespace {
    /**
     * @param array<mixed> ...$args
     */
    function dd(...$args): void {
      if (php_sapi_name() == 'cli-server') {
        echo '<pre>';
      }
      foreach ($args as $arg) {
        if (is_null($arg)) {
          $arg = '[[NULL]]';
        } else if ($arg === false) {
          $arg = '[[FALSE]]';
        } else if ($arg === true) {
          $arg = '[[TRUE]]';
        }
        print_r($arg);
        echo PHP_EOL;
      }
      exit(0);
    }
  }

  namespace Neu\Debug {
    use Neu\Http\Response;
    use Neu\Pipe7\Reducers;
    use Throwable;
    use function Neu\Pipe7\pipe;

    function loadFileSnippet(string $path, int $line, int $snippet_length = 5) {
      try {
        $lines = explode("\n", file_get_contents($path));
      } catch (Throwable) {
        throw new \RuntimeException("Cannot load file '$path'!");
      }
      $mid = (int)ceil($snippet_length / 2);
      $low = $line - $mid;
      if ($low < 0) $low = 0;
      $marker_index = $line - $low - 1;
      $snippet = pipe(array_slice($lines, $low, $snippet_length))
        ->map(fn($line, $i) => str_pad($i + $low, $snippet_length) . ($i === $marker_index ? '|>' : '| ') . $line)
        ->reduce(Reducers::join("\r\n"));
      return $snippet;
    }

    function highlightSnippet(string $snippet) {
      $result = [];
      foreach (str_split($snippet) as $c) {
        $result[] = match ($c) {
          default => $c
        };
      }
      return join('', $result);
    }

    function niceResponseFromError(Throwable $error): Response {
      $view_data = [];
      do {
        $snippet = loadFileSnippet($error->getFile(), $error->getLine());
        $trace = pipe($error->getTrace())
          ->map(fn($t) => [
            (isset($t['class']) ? $t['class'] . '::' : '') . $t['function'],
            isset($t['file']) ? highlightSnippet(loadFileSnippet($t['file'], $t['line'])) : '[[NO SNIPPET AVAILABLE]]',
            $t['file'] ?? '[[NO FILE GIVEN]]'
          ])
          ->toArray();
        $view_data[] = [$error, $snippet, $trace];
      } while ($error = $error->getPrevious());
      $debug_view = join(DIRECTORY_SEPARATOR, [__DIR__, 'debugging-view.php']);
      ob_start();
      require $debug_view;
      $result = ob_get_contents();
      ob_end_clean();
      return new Response(status: 500, body: $result);
    }
  }
