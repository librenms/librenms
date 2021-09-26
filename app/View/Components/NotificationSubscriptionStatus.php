<?php

namespace App\View\Components;

use Illuminate\View\Component;

class NotificationSubscriptionStatus extends Component
{
    /**
     * @var bool
     */
    public $userHasTransport;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->userHasTransport = \Auth::user()->hasBrowserPushTransport();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.notification-subscription-status');
    }
}
