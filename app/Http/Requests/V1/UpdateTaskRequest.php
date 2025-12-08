<?php
namespace App\Http\Requests\V1;

use App\Models\Task;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status'      => ['sometimes', 'string', 'in:' . implode(',', Task::getStatuses())],
            'priority'    => ['sometimes', 'string', 'in:' . implode(',', Task::getPriorities())],
            'due_date'    => ['nullable', 'date'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title'       => 'task title',
            'description' => 'task description',
            'status'      => 'task status',
            'priority'    => 'task priority',
            'due_date'    => 'due date',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'  => 'Please provide a title for the task.',
            'title.max'       => 'The task title cannot exceed 255 characters.',
            'description.max' => 'The task description cannot exceed 5000 characters.',
            'status.in'       => 'The selected status is invalid. Valid options are: ' . implode(', ', Task::getStatuses()),
            'priority.in'     => 'The selected priority is invalid. Valid options are: ' . implode(', ', Task::getPriorities()),
            'due_date.date'   => 'Please provide a valid date.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
