<?php

namespace App\Traits;

use App\Models\AdminAuditLog;

trait LogsAdminActions
{
    /**
     * Log an admin action.
     */
    protected function logAdminAction(
        string $action,
        string $description,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        AdminAuditLog::create([
            'user_id' => auth()->user()->id, // Use the actual numeric ID, not the auth identifier
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
