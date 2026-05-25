<?php

namespace App\Support\Crud;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CrudQuery
{
    public static function apply(
        Builder $query,
        Request $request,
        array $config = []
    ) {
        self::applySearch($query, $request, $config);

        self::applyFilters($query, $request, $config);

        self::applySorting($query, $request, $config);

        return self::applyPagination($query, $request);
    }

    protected static function applySearch(
        Builder $query,
        Request $request,
        array $config
    ): void {

        $search = $request->get('search');

        $searchable = $config['searchable'] ?? [];

        if (!$search || empty($searchable)) {
            return;
        }

        $query->where(function ($q) use ($search, $searchable) {

            foreach ($searchable as $field) {

                $q->orWhere($field, 'LIKE', "%{$search}%");
            }
        });
    }

    protected static function applyFilters(
        Builder $query,
        Request $request,
        array $config
    ): void {

        $filters = $config['filters'] ?? [];

        foreach ($filters as $filter) {

            $value = $request->get($filter);

            if ($value === null || $value === '') {
                continue;
            }

            $query->where($filter, $value);
        }
    }

    protected static function applySorting(
        Builder $query,
        Request $request,
        array $config
    ): void {

        $sortable = $config['sortable'] ?? [];

        $sort = $request->get('sort');

        $direction = $request->get('direction', 'asc');

        if (
            $sort &&
            in_array($sort, $sortable)
        ) {
            $query->orderBy($sort, $direction);
        }
    }

    protected static function applyPagination(
        Builder $query,
        Request $request
    ) {

        $perPage = $request->get('per_page', 10);

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }
}
