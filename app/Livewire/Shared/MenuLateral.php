<?php

namespace App\Livewire\Shared;

use App\Livewire\Actions\Logout;
use Livewire\Component;

class MenuLateral extends Component
{
    public function logout(Logout $logout)
    {
        $logout();
        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.shared.menu-lateral');
    }
}
