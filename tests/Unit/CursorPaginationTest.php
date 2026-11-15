<?php

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Lunar\Api\Contracts\ApiManager;
use Lunar\Api\Query\QueryApplier;
use Lunar\Api\Query\QueryParser;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Core\Models\Product;
use Lunar\Tests\Api\Fixtures\CursorProductResource;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->setUpStore();
    $this->products = Product::factory()->count(3)->create();
});

test('a cursor resource walks its collection from the first page', function (): void {
    $first = paginateCursorProducts(['page' => ['size' => '2']]);

    expect($first)->toBeInstanceOf(CursorPaginator::class)
        ->and(collect($first->items())->pluck('id')->all())->toBe($this->products->take(2)->pluck('id')->all())
        ->and($first->previousCursor())->toBeNull()
        ->and($first->nextCursor())->not->toBeNull();

    $second = paginateCursorProducts(['page' => ['size' => '2', 'cursor' => $first->nextCursor()->encode()]]);

    expect(collect($second->items())->pluck('id')->all())->toBe([$this->products->last()->id])
        ->and($second->nextCursor())->toBeNull();
});

test('a cursor resource still pages by number when asked to', function (): void {
    expect(paginateCursorProducts(['page' => ['number' => '1', 'size' => '2']]))
        ->toBeInstanceOf(LengthAwarePaginator::class)
        ->total()->toBe(3);
});

function paginateCursorProducts(array $query): LengthAwarePaginator|CursorPaginator
{
    $registry = app(ApiManager::class)->surface('cursor', 'v1')->resource(CursorProductResource::class);
    $definition = $registry->definition(CursorProductResource::class);
    $context = new SerializationContext($registry);
    $parsed = app(QueryParser::class)->parse(Request::create('/products', 'GET', $query), $definition, $context);
    $applier = app(QueryApplier::class);

    return $applier->paginate($applier->apply(Product::query(), $definition, $parsed, $context), $definition, $parsed);
}
