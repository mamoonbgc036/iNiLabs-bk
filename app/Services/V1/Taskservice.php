<?php
namespace App\Services\V1;

use App\Models\Task;
use App\Models\User;
use App\Repositories\Interfaces\V1\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class TaskService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected TaskRepositoryInterface $taskRepository
    ) {}

    /**
     * Get all tasks for a user.
     */
    public function getAllTasks(User $user, array $filters = []): Collection
    {
        return $this->taskRepository->getAllForUser($user->id, $filters);
    }

    /**
     * Get paginated tasks for a user.
     */
    public function getPaginatedTasks(User $user, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->taskRepository->getPaginatedForUser($user->id, $perPage, $filters);
    }

    /**
     * Find a task by ID for a user.
     */
    public function findTask(int $taskId, User $user): ?Task
    {
        return $this->taskRepository->findByIdForUser($taskId, $user->id);
    }

    /**
     * Create a new task for a user.
     */
    public function createTask(User $user, array $data): Task
    {
        $taskData = array_merge($data, [
            'user_id'  => $user->id,
            'status'   => $data['status'] ?? Task::STATUS_PENDING,
            'priority' => $data['priority'] ?? Task::PRIORITY_MEDIUM,
        ]);

        $task = $this->taskRepository->create($taskData);

        Log::info('Task created', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'title'   => $task->title,
        ]);

        return $task;
    }

    /**
     * Update a task.
     */
    public function updateTask(Task $task, array $data): Task
    {
        $updatedTask = $this->taskRepository->update($task, $data);

        Log::info('Task updated', [
            'task_id' => $task->id,
            'user_id' => $task->user_id,
        ]);

        return $updatedTask;
    }

    /**
     * Delete a task.
     */
    public function deleteTask(Task $task): bool
    {
        $taskId = $task->id;
        $userId = $task->user_id;

        $result = $this->taskRepository->delete($task);

        if ($result) {
            Log::info('Task deleted', [
                'task_id' => $taskId,
                'user_id' => $userId,
            ]);
        }

        return $result;
    }

    /**
     * Toggle task status.
     */
    public function toggleTaskStatus(Task $task): Task
    {
        $previousStatus = $task->status;
        $updatedTask    = $this->taskRepository->toggleStatus($task);

        Log::info('Task status toggled', [
            'task_id'         => $task->id,
            'user_id'         => $task->user_id,
            'previous_status' => $previousStatus,
            'new_status'      => $updatedTask->status,
        ]);

        return $updatedTask;
    }

    /**
     * Get task statistics for a user.
     */
    public function getTaskStatistics(User $user): array
    {
        $tasks = $this->taskRepository->getAllForUser($user->id);

        $total      = $tasks->count();
        $pending    = $tasks->where('status', Task::STATUS_PENDING)->count();
        $inProgress = $tasks->where('status', Task::STATUS_IN_PROGRESS)->count();
        $completed  = $tasks->where('status', Task::STATUS_COMPLETED)->count();
        $cancelled  = $tasks->where('status', Task::STATUS_CANCELLED)->count();

        $overdue = $tasks->filter(function ($task) {
            return $task->isOverdue();
        })->count();

        return [
            'total'           => $total,
            'pending'         => $pending,
            'in_progress'     => $inProgress,
            'completed'       => $completed,
            'cancelled'       => $cancelled,
            'overdue'         => $overdue,
            'completion_rate' => $total > 0
                ? round(($completed / $total) * 100, 2)
                : 0,
        ];
    }

    /**
     * Check if task belongs to user.
     */
    public function taskBelongsToUser(Task $task, User $user): bool
    {
        return $task->user_id === $user->id;
    }
}
