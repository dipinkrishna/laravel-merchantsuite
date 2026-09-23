<?php

namespace DK\MerchantSuite\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * One page of search results. Pass $continueFrom back in to get the next
 * page, or use the resource's cursor() method to walk every page lazily.
 *
 * @template T
 *
 * @implements IteratorAggregate<int, T>
 */
final readonly class SearchResults implements Countable, IteratorAggregate
{
    /**
     * @param  list<T>  $items
     */
    public function __construct(
        public array $items,
        public ?string $continueFrom,
        public int $resultCount,
    ) {}

    public function hasMore(): bool
    {
        return $this->continueFrom !== null && $this->continueFrom !== '';
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
