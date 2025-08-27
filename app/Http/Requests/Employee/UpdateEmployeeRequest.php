<?php

namespace App\Http\Requests\Employee;

use App\Http\Requests\Request;
use App\Utils\Traits\MakesHash;

class UpdateEmployeeRequest extends Request
{
    use MakesHash;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->user()->can('edit', $this->employee);
    }

    public function rules()
    {
        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'emp_id' => 'sometimes|required|string|max:255|unique:employees,emp_id,' . $this->employee->id . ',id,company_id,' . auth()->user()->company()->id,
            'department' => 'sometimes|required|string|max:255',
            'designation' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:employees,email,' . $this->employee->id . ',id,company_id,' . auth()->user()->company()->id,
        ];

        return $rules;
    }

    public function prepareForValidation()
    {
        $input = $this->all();
        $this->replace($input);
    }
}