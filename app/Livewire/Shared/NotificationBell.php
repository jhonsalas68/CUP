<?php

namespace App\Livewire\Shared;

use Livewire\Component;
use App\Models\Notificacion;

class NotificationBell extends Component
{
    public $listeners = ['notificationSent' => '$refresh'];

    public function markAsRead($id)
    {
        $notif = Notificacion::where('user_id', auth()->id())->findOrFail($id);
        $notif->update(['leido' => true]);
        
        $this->dispatch('notificationMarkedRead');
    }

    public function markAllAsRead()
    {
        Notificacion::where('user_id', auth()->id())
            ->where('leido', false)
            ->update(['leido' => true]);
            
        $this->dispatch('notificationMarkedRead');
    }

    public function render()
    {
        $notifications = Notificacion::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $unreadCount = Notificacion::where('user_id', auth()->id())
            ->where('leido', false)
            ->count();

        return view('livewire.shared.notification-bell', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
