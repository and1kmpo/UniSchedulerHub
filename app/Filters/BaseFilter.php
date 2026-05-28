<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class BaseFilter
{
    protected Request $request;

    protected Builder $query;

    protected array $allowedSorts = [];

    protected string $defaultSort = 'created_at';

    protected string $defaultDirection = 'desc';

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $query): Builder
    {
        $this->query = $query;

        foreach ($this->filters() as $filter => $value) {

            if (
                method_exists($this, $filter) &&
                $value !== null &&
                $value !== ''
            ) {
                $this->$filter($value);
            }
        }

        $this->applySorting();

        return $this->query;
    }

    protected function applySorting(): void
    {
        $sort = $this->request->get(
            'sort',
            $this->defaultSort
        );

        $direction = $this->request->get(
            'direction',
            $this->defaultDirection
        );

        if (
            in_array($sort, $this->allowedSorts)
        ) {
            $this->query->orderBy($sort, $direction);
        }
    }

    protected function filters(): array
    {
        return $this->request->all();
    }
}
