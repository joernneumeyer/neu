<?php


  namespace Neu\Dal;

  class ModelRepository {
    public function __construct(
      private string $defaultModel = ''
    ) {
    }

    /**
     * @param string $model
     * @return ModelRepository
     */
    public function withDefaultModel(string $model) {
      $this->defaultModel = $model;
      return $this;
    }

    /**
     * @param null $model
     * @throws \Exception
     */
    public function load($model = null) {
      if (!$model) {
        $model = $this->defaultModel;
      }
      if (!$model) {
        throw new \Exception('Missing model type!');
      }
    }

    public function persist($instance) {

    }
  }
