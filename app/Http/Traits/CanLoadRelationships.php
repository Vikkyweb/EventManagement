<?php

namespace App\Https\Traits;

trait CanLoadRelationships
{

    public function loadRelationships($query, array $relations)
    {
        foreach ($relations as $relation) {
            $query->when(
                $this->shouldIncludeRelation($relation),
                function ($q) use ($relation) {
                    $q->with($relation);
                }
            );
        }
    }

    protected function shouldIncludeRelation(string $relation): bool
    {
        $includeRelations = request()->query('include', '');

        if(!$includeRelations){
            return false;
        }

        $relations = array_map('trim', explode(',', $includeRelations));

        return in_array($relation, $relations);
    }
}