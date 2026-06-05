<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class NameSearch
{
    /**
     * Apply a case-insensitive LIKE filter on $column if $text is non-empty.
     * Works on both MySQL (case-insensitive by default) and SQLite (LIKE is case-insensitive for ASCII).
     * Returns the builder for chaining.
     * Requirements: 1.6, 2.7, 10.3
     */
    public static function filter(Builder $query, string $column, ?string $text): Builder
    {
        if ($text !== null && $text !== '') {
            $query->whereRaw("LOWER({$column}) LIKE ?", ['%' . mb_strtolower($text) . '%']);
        }
        return $query;
    }
}
