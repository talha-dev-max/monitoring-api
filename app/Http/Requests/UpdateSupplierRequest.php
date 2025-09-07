<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplierId = $this->route('supplier')->id;
        
        return [
            'name' => "sometimes|required|string|max:255|unique:suppliers,name,$supplierId",
            'base_url' => 'sometimes|required|url|max:500',
            'health_endpoint' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:active,inactive',
            'timeout_seconds' => 'sometimes|required|integer|min:1|max:300',
            'retry_count' => 'sometimes|required|integer|min:0|max:10',
            'headers' => 'sometimes|nullable|array',
            'is_backup' => 'sometimes|boolean',
            'priority' => 'sometimes|nullable|integer|min:1|max:100'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The supplier name is required.',
            'name.unique' => 'A supplier with this name already exists.',
            'base_url.required' => 'The base URL is required.',
            'base_url.url' => 'The base URL must be a valid URL.',
            'health_endpoint.required' => 'The health endpoint is required.',
            'status.required' => 'The supplier status is required.',
            'status.in' => 'The status must be either active or inactive.',
            'timeout_seconds.required' => 'The timeout seconds is required.',
            'timeout_seconds.integer' => 'The timeout seconds must be an integer.',
            'timeout_seconds.min' => 'The timeout seconds must be at least 1.',
            'timeout_seconds.max' => 'The timeout seconds cannot exceed 300.',
            'retry_count.required' => 'The retry count is required.',
            'retry_count.integer' => 'The retry count must be an integer.',
            'retry_count.min' => 'The retry count cannot be negative.',
            'retry_count.max' => 'The retry count cannot exceed 10.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}