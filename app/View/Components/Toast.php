<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\View\Component;

class Toast extends Component
{
    public array $purifier_config = [
        'HTML.Allowed' => 'a[href],b,i,ul,ol,li,h1,h2,h3,h4,br,p,pre',
        'URI.DisableExternal' => true,
    ];
    public ?array $toasts;

    /**
     * Create a new component instance.
     */
    public function __construct(Request $request, SessionManager $session)
    {
        $this->purifier_config['URI.Host'] = $request->getHttpHost();
        $this->toasts = $session->get('toasts');
        $session->forget('toasts'); // to ward againsts double toasts
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.toast');
    }
}
