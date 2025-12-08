<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreTaskRequest;
use App\Http\Requests\V1\UpdateTaskRequest;
use App\Http\Resources\V1\TaskResource;
use App\Models\Task;
use App\Services\V1\Taskservice;
use App\Traits\V1\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponse;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TaskService $taskService
    ) {}

    /**
     * Display a listing of the tasks.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'status',
            'priority',
            'search',
            'due_date_from',
            'due_date_to',
            'overdue',
            'sort_by',
            'sort_order',
        ]);

        $perPage = $request->input('per_page', 15);

        $tasks = $this->taskService->getPaginatedTasks(
            $request->user(),
            min($perPage, 4), // Max 100 per page
            $filters
        );

        return response()->json([
            'success' => true,
            'message' => 'Tasks retrieved successfully',
            'data'    => TaskResource::collection($tasks->items()),
            'meta'    => [
                'total'        => $tasks->total(),
                'per_page'     => $tasks->perPage(),
                'current_page' => $tasks->currentPage(),
                'last_page'    => $tasks->lastPage(),
                'from'         => $tasks->firstItem(),
                'to'           => $tasks->lastItem(),
            ],
            'links'   => [
                'first' => $tasks->url(1),
                'last'  => $tasks->url($tasks->lastPage()),
                'prev'  => $tasks->previousPageUrl(),
                'next'  => $tasks->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created task.
     *
     * @param StoreTaskRequest $request
     * @return JsonResponse
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask(
            $request->user(),
            $request->validated()
        );

        return $this->createdResponse(
            new TaskResource($task),
            'Task created successfully'
        );
    }

    /**
     * Display the specified task.
     *
     * @param Task $task
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Task $task, Request $request): JsonResponse
    {
        // Check ownership
        if (! $this->taskService->taskBelongsToUser($task, $request->user())) {
            return $this->forbiddenResponse('You do not have access to this task');
        }

        return $this->successResponse(
            new TaskResource($task),
            'Task retrieved successfully'
        );
    }

    /**
     * Update the specified task.
     *
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return JsonResponse
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        // Check ownership
        if (! $this->taskService->taskBelongsToUser($task, $request->user())) {
            return $this->forbiddenResponse('You do not have access to this task');
        }

        $updatedTask = $this->taskService->updateTask(
            $task,
            $request->validated()
        );

        return $this->successResponse(
            new TaskResource($updatedTask),
            'Task updated successfully'
        );
    }

    /**
     * Remove the specified task.
     *
     * @param Task $task
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Task $task, Request $request): JsonResponse
    {
        // Check ownership
        if (! $this->taskService->taskBelongsToUser($task, $request->user())) {
            return $this->forbiddenResponse('You do not have access to this task');
        }

        $this->taskService->deleteTask($task);

        return $this->successResponse(null, 'Task deleted successfully');
    }

    /**
     * Toggle the status of the specified task.
     *
     * @param Task $task
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleStatus(Task $task, Request $request): JsonResponse
    {
        // Check ownership
        if (! $this->taskService->taskBelongsToUser($task, $request->user())) {
            return $this->forbiddenResponse('You do not have access to this task');
        }

        $updatedTask = $this->taskService->toggleTaskStatus($task);

        return $this->successResponse(
            new TaskResource($updatedTask),
            'Task status toggled successfully'
        );
    }
}
