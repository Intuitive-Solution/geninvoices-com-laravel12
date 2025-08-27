<?php

namespace App\Repositories;

use App\Models\Employee;

/**
 * EmployeeRepository.
 */
class EmployeeRepository extends BaseRepository
{
    public function save(array $data, Employee $employee): ?Employee
    {
        $employee->fill($data);
        $employee->save();

        return $employee;
    }
}