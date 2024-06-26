<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Filter extends Component
{

    public $withTime; // Com datetime ou sem datetime.
    public $closedPeriodDisabled; // Com datetime ou sem datetime.

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($withTime = false, $closedPeriodDisabled = false)
    {
        $this->withTime = $withTime;
        $this->closedPeriodDisabled = $closedPeriodDisabled;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.filter');
    }
}
