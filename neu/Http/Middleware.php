<?php

  namespace Neu\Http;

  interface Middleware {
    function apply(): void;
  }
