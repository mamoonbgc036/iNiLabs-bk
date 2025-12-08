<?php
namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'description'        => $this->description,
            'status'             => $this->status,
            'status_label'       => $this->getStatusLabel(),
            'priority'           => $this->priority,
            'priority_label'     => $this->getPriorityLabel(),
            'due_date'           => $this->due_date?->format('Y-m-d'),
            'due_date_formatted' => $this->due_date?->format('M d, Y'),
            'is_completed'       => $this->isCompleted(),
            'is_overdue'         => $this->isOverdue(),
            'created_at'         => $this->created_at->toIso8601String(),
            'updated_at'         => $this->updated_at->toIso8601String(),
        ];
    }

    /**
     * Get human-readable status label.
     */
    protected function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending'     => 'Pending',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled',
            default       => ucfirst($this->status),
        };
    }

    /**
     * Get human-readable priority label.
     */
    protected function getPriorityLabel(): string
    {
        return match ($this->priority) {
            'low'    => 'Low',
            'medium' => 'Medium',
            'high'   => 'High',
            'urgent' => 'Urgent',
            default  => ucfirst($this->priority),
        };
    }
}
