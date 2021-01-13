<?php

  use Neu\Data\DataPipe;

  /**
   * @param array $data
   * @return DataPipe
   */
  function pipe(array $data): DataPipe {
    return new DataPipe($data);
  }
