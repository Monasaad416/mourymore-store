<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class Wishlist
{
    private const SESSION_KEY = 'store.wishlist';

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        $raw = session(self::SESSION_KEY, []);

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_map(intval(...), $raw));
    }

    public function contains(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    public function toggle(int $productId): bool
    {
        $ids = $this->ids();

        if (in_array($productId, $ids, true)) {
            $ids = array_values(array_filter($ids, fn (int $id): bool => $id !== $productId));
            session([self::SESSION_KEY => $ids]);

            return false;
        }

        if (! Product::query()->whereKey($productId)->exists()) {
            return false;
        }

        array_unshift($ids, $productId);
        session([self::SESSION_KEY => array_slice($ids, 0, 40)]);

        return true;
    }

    public function count(): int
    {
        return count($this->ids());
    }

    /**
     * @return Collection<int, Product>
     */
    public function products(): Collection
    {
        $ids = $this->ids();

        if ($ids === []) {
            return collect();
        }

        return Product::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(function (Product $product) use ($ids): int {
                $index = array_search($product->id, $ids, true);

                return $index === false ? 0 : $index;
            })
            ->values();
    }
}
