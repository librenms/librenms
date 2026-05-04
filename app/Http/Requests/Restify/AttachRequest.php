<?php

namespace App\Http\Requests\Restify;

use Binaryk\LaravelRestify\Http\Requests\RepositoryAttachRequest;
use Illuminate\Support\Collection;

class AttachRequest extends RepositoryAttachRequest
{
    use ResolvesRelatedRepositoryFromField;

    public function attachRelatedModels(): Collection
    {
        return $this->resolveRelatedModels();
    }
}
