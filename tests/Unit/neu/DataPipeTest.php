<?php

  it('should produce a new pipe with mapped data', function () {
    $data   = [1, 2, 3, 4, 5];
    $mapped = pipe($data)->map(fn($number) => (string)$number)->data();
    expect($mapped)->toMatchArray(['1', '2', '3', '4', '5']);
  });

  it('should filter for suitable items', function () {
    $ages     = [34, 65, 12, 87, 3, 9, 4, 65, 2];
    $filtered = pipe($ages)->filter(fn($age) => $age > 40)->data();
    expect($filtered)->toMatchArray([65, 87, 65]);
  });

  it('should reduce the data into one value', function () {
    $nums    = [2, 4, 6, 8, 10, 12, 14];
    $product = pipe($nums)->reduce(fn($accu, $num) => $accu * $num, 1);
    expect($product)->toEqual(645120);
  });
