<?php

namespace Nuwave\Lighthouse\Execution\Arguments;

use GraphQL\Error\Error;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Support\Contracts\ArgResolver;

class UpdateModel implements ArgResolver
{
    const MISSING_PRIMARY_KEY_FOR_UPDATE = 'Missing primary key for update.';
    /**
     * @var callable|\Nuwave\Lighthouse\Support\Contracts\ArgResolver
     */
    private $previous;

    /**
     * @param callable|\Nuwave\Lighthouse\Support\Contracts\ArgResolver $previous
     */
    public function __construct(callable $previous)
    {
        $this->previous = $previous;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation  $modelOrRelation
     * @param  \Nuwave\Lighthouse\Execution\Arguments\ArgumentSet  $args
     */
    public function __invoke($modelOrRelation, $args)
    {
        $model = $modelOrRelation instanceof Model ? $modelOrRelation : $modelOrRelation->make();

        /** @var \Nuwave\Lighthouse\Execution\Arguments\Argument|null $id */
        $id = Arr::pull($args->arguments, 'id')
            ?? Arr::pull($args->arguments, $model->getKeyName())
            ?? null;

        if ($id === null) {
            throw new Error(self::MISSING_PRIMARY_KEY_FOR_UPDATE);
        }

        $model = $modelOrRelation->newQuery()->findOrFail($id->value);

        return ($this->previous)($model, $args);
    }
}
