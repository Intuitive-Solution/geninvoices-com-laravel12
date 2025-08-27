<?php

namespace App\Transformers;

use App\Models\Employee;
use App\Utils\Traits\MakesHash;

/**
 * Class EmployeeTransformer.
 */
class EmployeeTransformer extends EntityTransformer
{
    use MakesHash;

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [
        'company',
        'user',
    ];

    /**
     * @param Employee $employee
     * @return array
     */
    public function transform(Employee $employee)
    {
        return [
            'id' => $this->encodePrimaryKey($employee->id),
            'name' => $employee->name ?: '',
            'emp_id' => $employee->emp_id ?: '',
            'department' => $employee->department ?: '',
            'designation' => $employee->designation ?: '',
            'email' => $employee->email ?: '',
            'is_deleted' => (bool) $employee->is_deleted,
            'user_id' => $this->encodePrimaryKey($employee->user_id),
            'created_at' => (int) $employee->created_at,
            'updated_at' => (int) $employee->updated_at,
            'archived_at' => (int) $employee->deleted_at,
        ];
    }

    public function includeCompany(Employee $employee)
    {
        $transformer = new CompanyTransformer($this->serializer);

        return $this->item($employee->company, $transformer);
    }

    public function includeUser(Employee $employee)
    {
        $transformer = new UserTransformer($this->serializer);

        return $this->item($employee->user, $transformer);
    }

}