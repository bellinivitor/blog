<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/_client', fn (Request $request) => [
        'ip' => $request->ip(),
        'secure' => $request->isSecure(),
    ]);
});

test('reads the visitor ip and https from requests forwarded by cloudflare', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '162.158.1.10'])
        ->withHeaders(['X-Forwarded-For' => '200.100.50.25', 'X-Forwarded-Proto' => 'https'])
        ->get('/_client')
        ->assertExactJson(['ip' => '200.100.50.25', 'secure' => true]);
});

test('ignores forwarded headers sent straight to the origin', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
        ->withHeaders(['X-Forwarded-For' => '200.100.50.25', 'X-Forwarded-Proto' => 'https'])
        ->get('/_client')
        ->assertExactJson(['ip' => '203.0.113.7', 'secure' => false]);
});
