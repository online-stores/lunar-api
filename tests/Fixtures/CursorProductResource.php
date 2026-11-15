<?php

namespace Lunar\Tests\Api\Fixtures;

use Lunar\Api\Storefront\Resources\V1\ProductResource;

/** A resource that walks its collection by cursor. */
class CursorProductResource extends ProductResource
{
    public function supportsCursorPagination(): bool
    {
        return true;
    }
}
