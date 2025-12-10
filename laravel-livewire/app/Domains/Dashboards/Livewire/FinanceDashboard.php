<?php

namespace App\Domains\Dashboards\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;

class FinanceDashboard extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('view-dashboard.finance');
    }

    public function render(): View
    {
        return view('livewire.dashboards.finance-dashboard')
            ->layout('components.layouts.dashboard', [
            'title' => __('Panel financiero'),
        ]);
    }
}
