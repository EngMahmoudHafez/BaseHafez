<?php

namespace App\Modules\Base\Concerns;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Shared dashboard search + filter pipeline for resource list screens.
 *
 * Mirrors the hand-rolled query logic used across the dashboard services
 * (e.g. the user index: name/email/phone/whatsapp search + status + role via
 * a relationship) so every list controller/service can filter the same way.
 */
trait HandlesResourceQuery
{
    /**
     * Apply the shared dashboard search + filter constraints to a builder.
     *
     * Search: when the `search` query parameter is filled, every entry in
     * $searchable is matched with a case-insensitive `LIKE %term%` grouped in a
     * single OR clause. A column may reference a relationship with dot notation
     * (`relation.column`, or nested `relation.sub.column`), resolved through
     * `orWhereHas`.
     *
     * Filters: $filterable maps a request key to either a column name (applied
     * as an exact `where`, or `whereHas` when it is a `relation.column`) or a
     * closure `fn (Builder $query, mixed $value) => ...` for custom logic. Each
     * filter is only applied when its request key is filled.
     *
     * @param  Builder  $query  The base query to constrain.
     * @param  Request  $request  The current request carrying the query string.
     * @param  list<string>  $searchable  Columns (or `relation.column`) scanned by the `search` term.
     * @param  array<string, string|Closure>  $filterable  Request key => column, `relation.column`, or closure.
     * @return Builder The same builder instance, for chaining.
     */
    public function applyDashboardFilters(Builder $query, Request $request, array $searchable, array $filterable): Builder
    {
        $query->when(
            $request->filled('search') && $searchable !== [],
            function (Builder $query) use ($request, $searchable): void {
                $term = '%' . $request->string('search')->trim() . '%';

                $query->where(function (Builder $group) use ($searchable, $term): void {
                    foreach ($searchable as $column) {
                        $this->applySearchColumn($group, $column, $term);
                    }
                });
            },
        );

        foreach ($filterable as $key => $target) {
            if (! $request->filled($key)) {
                continue;
            }

            $value = $request->input($key);

            if ($target instanceof Closure) {
                $target($query, $value);

                continue;
            }

            $this->applyFilterColumn($query, $target, $value);
        }

        return $query;
    }

    /**
     * Add one searchable column to the OR-group, resolving `relation.column`.
     */
    private function applySearchColumn(Builder $group, string $column, string $term): void
    {
        if (str_contains($column, '.')) {
            [$relation, $relatedColumn] = $this->splitRelation($column);

            $group->orWhereHas(
                $relation,
                fn (Builder $relationQuery) => $relationQuery->where($relatedColumn, 'like', $term),
            );

            return;
        }

        $group->orWhere($column, 'like', $term);
    }

    /**
     * Apply one exact-match filter, resolving `relation.column` via `whereHas`.
     */
    private function applyFilterColumn(Builder $query, string $target, mixed $value): void
    {
        if (str_contains($target, '.')) {
            [$relation, $relatedColumn] = $this->splitRelation($target);

            $query->whereHas(
                $relation,
                fn (Builder $relationQuery) => $relationQuery->where($relatedColumn, $value),
            );

            return;
        }

        $query->where($target, $value);
    }

    /**
     * Split a `relation.column` reference into `[relation, column]`,
     * keeping any nested relation path intact (`a.b.column` => `['a.b', 'column']`).
     *
     * @return array{0: string, 1: string}
     */
    private function splitRelation(string $reference): array
    {
        $segments = explode('.', $reference);
        $column = array_pop($segments);

        return [implode('.', $segments), $column];
    }
}
