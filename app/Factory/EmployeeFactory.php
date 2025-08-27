<?php

namespace App\Factory;

use App\Models\Employee;

class EmployeeFactory
{
    public static function create(int $company_id, int $user_id): Employee
    {
        $employee = new Employee();
        $employee->company_id = $company_id;
        $employee->user_id = $user_id;
        $employee->name = '';
        $employee->emp_id = '';
        $employee->department = '';
        $employee->designation = '';
        $employee->email = '';
        $employee->status = 'active';
        $employee->is_deleted = false;

        return $employee;
    }
}