<?php
namespace App\Repositories\V1;

use App\Models\Task;
use App\Repositories\Interfaces\V1\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository implements TaskRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(
        protected Task $model
    ) {}

    /**
     * Get all tasks for a user.
     */
    public function getAllForUser(int $userId, array $filters = []): Collection
    {
        $query = $this->model->forUser($userId);

        return $this->applyFilters($query, $filters)->get();
    }

    /**
     * Get paginated tasks for a user.
     */
    public function getPaginatedForUser(int $userId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->forUser($userId);

        return $this->applyFilters($query, $filters)->paginate($perPage);
    }

    /**
     * Find a task by ID.
     */
    public function findById(int $id): ?Task
    {
        return $this->model->find($id);
    }

    /**
     * Find a task by ID for a specific user.
     */
    public function findByIdForUser(int $id, int $userId): ?Task
    {
        return $this->model
            ->forUser($userId)
            ->where('id', $id)
            ->first();
    }

    /**
     * Create a new task.
     */
    public function create(array $data): Task
    {
        return $this->model->create($data);
    }

    /**
     * Update a task.
     */
    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh();
    }

    /**
     * Delete a task.
     */
    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    /**
     * Toggle task status.
     */
    public function toggleStatus(Task $task): Task
    {
        $task->toggleStatus();

        return $task->fresh();
    }

    /**
     * Apply filters to the query.
     */
    protected function applyFilters($query, array $filters)
    {
        // Filter by status
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by priority
        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // Filter by search term
        if (! empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Filter by due date range
        if (! empty($filters['due_date_from'])) {
            $query->whereDate('due_date', '>=', $filters['due_date_from']);
        }

        if (! empty($filters['due_date_to'])) {
            $query->whereDate('due_date', '<=', $filters['due_date_to']);
        }

        // Filter overdue tasks
        if (! empty($filters['overdue']) && $filters['overdue'] === true) {
            $query->whereNotNull('due_date')
                ->whereDate('due_date', '<', now())
                ->whereNotIn('status', [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED]);
        }

        // Sorting
        $sortBy    = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $allowedSortFields = ['title', 'status', 'priority', 'due_date', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            if ($sortBy === 'priority') {
                $query->orderByPriority($sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->latest();
        }

        return $query;
    }
}
