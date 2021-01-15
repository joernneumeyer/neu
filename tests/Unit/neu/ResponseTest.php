<?php

  use Neu\Http\Response;
  use Neu\Http\StatusCode;

  it('should apply new headers', function () {
    $response = new Response();
    $response->header('Content-Type', 'text/plain');
    expect($response->header('Content-Type'))->toEqual('text/plain');
  });

  it('should apply new status code', function () {
    $response = new Response();
    $response->status(StatusCode::Created);
    expect($response->status())->toEqual(StatusCode::Created);
  });

  it('should have sane default for a "not found" response', function () {
    $response = Response::not_found();
    expect($response->status)->toEqual(StatusCode::NotFound);
  });
