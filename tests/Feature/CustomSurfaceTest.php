<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Lunar\Api\Contracts\ApiManager;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

test('a surface the host registers renders its errors as JSON:API error objects', function (): void {
    app(ApiManager::class)->surface('pos', 'v1');

    Route::get('api/pos/v1/broken', fn () => throw ValidationException::withMessages(['name' => 'Required.']))
        ->name('lunar.api.pos.v1.broken');

    $this->getJson('/api/pos/v1/missing')
        ->assertNotFound()
        ->assertJsonPath('errors.0.status', '404');

    $this->getJson('/api/pos/v1/broken')
        ->assertUnprocessable()
        ->assertJsonPath('errors.0.status', '422')
        ->assertJsonPath('errors.0.source.pointer', '/name');

    $this->getJson('/elsewhere')
        ->assertNotFound()
        ->assertJsonMissingPath('errors');
});

test('a host surface follows its configured prefix', function (): void {
    config()->set('lunar.api.pos.prefix', 'till');
    app(ApiManager::class)->surface('pos', 'v1');

    $this->getJson('/till/v1/missing')->assertJsonPath('errors.0.status', '404');
    $this->getJson('/api/pos/v1/missing')->assertJsonMissingPath('errors');
});
