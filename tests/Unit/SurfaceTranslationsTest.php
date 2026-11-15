<?php

use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\OpenApi\Generator;
use Lunar\Api\Registry\SurfaceRegistry;
use Lunar\Api\Resources\Translations;
use Lunar\Api\Storefront\Resources\V1\ProductResource;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

test('a host surface documents translatable fields the way it serves them', function (): void {
    $resolved = app(ApiManager::class)->surface('pos', 'v1')
        ->resource(ProductResource::class)
        ->useTranslations(Translations::Resolved);

    $mapped = app(ApiManager::class)->surface('integrations', 'v1')
        ->resource(ProductResource::class);

    expect(productNameSchema($resolved))->toMatchArray(['type' => 'string'])
        ->and(productNameSchema($mapped))->toMatchArray(['$ref' => '#/components/schemas/TranslationMap']);
});

function productNameSchema(SurfaceRegistry $registry): array
{
    return app(Generator::class)->generate($registry)->toArray()['components']['schemas']['Product']['properties']['name'];
}
