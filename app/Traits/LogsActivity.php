<?php

namespace App\Traits;

use App\Models\Bitacora;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            static::logActivity('crear', $model);
        });

        static::updated(function ($model) {
            static::logActivity('actualizar', $model);
        });

        static::deleted(function ($model) {
            static::logActivity('eliminar', $model);
        });
    }

    protected static function logActivity(string $action, $model)
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        $user = Auth::user();
        $userId = $user ? $user->id : null;
        $ip = request()->ip();

        $modelName = class_basename($model);
        $recordId = $model->getKey();
        $objeto = "{$modelName} #{$recordId}";

        // Generate a friendly description
        $desc = "Se realizó la acción de '{$action}' sobre el objeto '{$objeto}'";
        if ($user) {
            $desc .= " por el usuario '{$user->name}'";
        } else {
            $desc .= " por el sistema";
        }

        // Capture changed attributes for updates
        $payload = [];
        if ($action === 'actualizar') {
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            $payload = [
                'dirty' => $changes,
                'original' => array_intersect_key($model->getOriginal(), $changes)
            ];
            
            // Customize description for common attributes
            if ($modelName === 'Postulante' && isset($changes['estado_admision'])) {
                $old = $payload['original']['estado_admision'] ?? 'ninguno';
                $new = $changes['estado_admision'];
                $desc = "Se actualizó el estado de admisión del postulante '{$model->nombres_apellidos}' de '{$old}' a '{$new}'";
                if ($user) $desc .= " por el usuario '{$user->name}'";
            }
        } elseif ($action === 'crear') {
            $payload = $model->getAttributes();
            unset($payload['created_at'], $payload['updated_at']);
        }

        Bitacora::create([
            'user_id' => $userId,
            'action' => $action,
            'objeto' => $objeto,
            'descripcion' => $desc,
            'payload' => $payload,
            'ip_address' => $ip,
        ]);
    }
}
