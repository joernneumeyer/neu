<?php

  namespace App;

  use Neu\Dal\ModelRepository;

  class DependencyFactory {
    public static function models(): ModelRepository {
      return new ModelRepository();
    }
  }
