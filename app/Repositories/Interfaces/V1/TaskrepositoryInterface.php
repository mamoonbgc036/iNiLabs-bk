<?php
namespace App\Repositories\Interfaces\V1;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    /**
     * Get all tasks for a user.
     */
    public function getAllForUser(int $userId, array $filters = []): Collection;

    /**
     * Get paginated tasks for a user.
     */
    public function getPaginatedForUser(int $userId, int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Find a task by ID.
     */
    public function findById(int $id): ?Task;

    /**
     * Find a task by ID for a specific user.
     */
    public function findByIdForUser(int $id, int $userId): ?Task;

    /**
     * Create a new task.
     */
    public function create(array $data): Task;

    /**
     * Update a task.
     */
    public function update(Task $task, array $data): Task;

    /**
     * Delete a task.
     */
    public function delete(Task $task): bool;

    /**
     * Toggle task status.
     */
    public function toggleStatus(Task $task): Task;
}
