<?php

namespace App\Http\Requests\Restify;

use Binaryk\LaravelRestify\Http\Requests\RepositorySyncRequest;
use Illuminate\Support\Collection;

class SyncRequest extends RepositorySyncRequest
{
    use ResolvesRelatedRepositoryFromField;

    public function syncRelatedModels(): Collection
    {
        return $this->resolveRelatedModels();
    }
}
