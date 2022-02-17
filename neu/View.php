<?php

  namespace Neu;

  use ArrayAccess;
  use Neu\Pipe7\Collections\ArrayStack;
  use Neu\Pipe7\Collections\DefaultArrayAccessImplementations;

  class View implements ArrayAccess {
    use DefaultArrayAccessImplementations;

    private string $baseView = '';

    /** @var ArrayStack<string> */
    private ArrayStack $sections;

    public function __construct(
      private string $basePath,
      private string $templatePath = '',
      private ?OutputBuffer $buffer = null,
    ) {
      if (!$this->buffer) {
        $this->buffer = new OutputBuffer();
      }
      $this->sections = new ArrayStack();
    }

    public function buffer(): OutputBuffer {
      return $this->buffer;
    }

    public function extend(string $view): void {
      $this->baseView = $view;
    }

    public function render(string $templatePath = ''): string {
      $templatePath = $templatePath ?: $this->templatePath;
      if (!$templatePath) {
        throw new \InvalidArgumentException('Please provide a template path!');
      }

      $finishedContent = '';
      $fullPath = $this->basePath . '/' . $templatePath . '.php';
      if (!file_exists($fullPath)) {
        throw new \RuntimeException('Please provide a valid template path! Tried to load file ' . $fullPath);
      }
      try {
        $templateContent = file_get_contents($fullPath);
        if (str_starts_with($templateContent, '<?php')) {
          $templateContent = substr($templateContent, 5);
        }
        extract($this->data);
        eval($templateContent);
        $finishedContent = ob_get_contents();
      } catch (\Throwable $e) {
        dd($e, $templateContent);
        throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
      } finally {
        ob_clean();
      }
      if ($this->baseView) {
        $v = $this->baseView;
        $this->baseView = '';
        return $this->render($v);
      } else {
        return $finishedContent;
      }
    }

    public function assign(array $data): static {
      foreach ($data as $k => $v) {
        $this->data[$k] = $v;
      }
      return $this;
    }

    public function __toString(): string {
      return $this->render();
    }

    public function begin_section(string $name): void {
      $this->sections->push($name);
      $this->buffer->openFrame();
    }

    public function end_section(): void {
      $section_title = implode('.', $this->sections->data());
      $this->sections->pop();
      $this[$section_title] = $this->buffer->finishCurrentFrame();;
    }
  }
