<?php

namespace App\Support\Crud;

use Illuminate\Http\Request;

trait HandlesCrudQueries
{
    public function scopeCrud(
        $query,
        Request $request,
        array $config = []
    ) {
        return CrudQuery::apply(
            $query,
            $request,
            $config
        );
    }
}
