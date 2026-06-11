<?php

namespace App\Http\Requests\Restify;

use Binaryk\LaravelRestify\Http\Requests\RepositoryDetachRequest;
use Illuminate\Support\Collection;

class DetachRequest extends RepositoryDetachRequest
{
    use ResolvesRelatedRepositoryFromField;

    public function detachRelatedModels(): Collection
    {
        return $this->resolveRelatedModels();
    }
}
