<?php

namespace App\Services;

use App\Models\ApprovalLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    /**
     * Submit for review
     */
    public function submit(Model $model, string $notes = null): void
    {
        DB::transaction(function () use ($model, $notes) {
            $model->update([
                'status' => 'submitted',
            ]);

            $this->logAction($model, 'submitted', $notes);
        });
    }

    /**
     * Review by supervisor
     */
    public function review(Model $model, string $notes = null): void
    {
        DB::transaction(function () use ($model, $notes) {
            $model->update([
                'status' => 'reviewed',
                'reviewed_by' => auth()->id(),
            ]);

            $this->logAction($model, 'reviewed', $notes);
        });
    }

    /**
     * Final approve by manager
     */
    public function approve(Model $model, string $notes = null): void
    {
        DB::transaction(function () use ($model, $notes) {
            $model->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);

            $this->logAction($model, 'approved', $notes);
        });
    }

    /**
     * Reject back to draft
     */
    public function reject(Model $model, string $notes): void
    {
        DB::transaction(function () use ($model, $notes) {
            $model->update([
                'status' => 'rejected',
            ]);

            $this->logAction($model, 'rejected', $notes);
        });
    }

    /**
     * Force change status (For System Admin as requested)
     */
    public function forceStatus(Model $model, string $status, string $notes = 'Forced status change by System Admin'): void
    {
        DB::transaction(function () use ($model, $status, $notes) {
            $model->update(['status' => $status]);
            $this->logAction($model, "forced_to_{$status}", $notes);
        });
    }

    private function logAction(Model $model, string $action, ?string $notes): void
    {
        ApprovalLog::create([
            'approvable_type' => get_class($model),
            'approvable_id' => $model->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'notes' => $notes,
        ]);
    }
}
