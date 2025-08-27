<?php

namespace App\Http\Requests\Employee;

use App\Http\Requests\Request;
use App\Utils\Traits\MakesHash;

class StoreEmployeeRequest extends Request
{
    use MakesHash;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->user()->can('create', \App\Models\Employee::class);
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'emp_id' => 'required|string|max:255|unique:employees,emp_id,NULL,id,company_id,' . auth()->user()->company()->id,
            'department' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email,NULL,id,company_id,' . auth()->user()->company()->id,
            'status' => 'required|in:active,inactive',
        ];

        return $rules;
    }

    public function prepareForValidation()
    {
        $input = $this->all();
        $this->replace($input);
    }
}