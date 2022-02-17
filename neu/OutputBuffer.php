<?php

  namespace Neu;

  use Neu\Pipe7\Collections\ArrayStack;

  class OutputBuffer {
    private ArrayStack $stack;

    public function __construct() {
      $this->stack = new ArrayStack();
    }

    public function start(): void {
      ob_start();
    }

    public function end(): void {
      ob_end_clean();
    }

    public function openFrame(): void {
      $this->stack->push(ob_get_contents());
      ob_clean();
    }

    public function finishCurrentFrame(): string {
      $result = ob_get_contents();
      ob_clean();
      echo $this->stack->pop();
      return $result;
    }
  }
