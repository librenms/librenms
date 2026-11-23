<?php

namespace App\Http\Controllers;

use App\Models\Pseudowire;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PseudowiresController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Pseudowire::class);

        $view = $request->query('view') === 'minigraphs' ? 'minigraphs' : 'detail';

        return view('pseudowires', [
            'view' => $view,
            'options' => [
                'detail' => ['text' => __('Details'), 'link' => route('pseudowires', ['view' => 'detail'])],
                'minigraphs' => ['text' => __('Mini Graphs'), 'link' => route('pseudowires', ['view' => 'minigraphs'])],
            ],
            'circuits' => Pseudowire::circuits($request->user()),
        ]);
    }
}
