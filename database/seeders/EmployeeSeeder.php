<?php

namespace Database\Seeders;

use App\Factory\EmployeeFactory;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::all()->each(function ($company) {
            $user = $company->owner();
            
            // Create sample employees with unique identifiers
            $employees = [
                [
                    'name' => 'John Doe',
                    'emp_id' => 'EMP001-' . $company->id,
                    'department' => 'Engineering',
                    'designation' => 'Senior Developer',
                    'email' => 'john.doe@company' . $company->id . '.com',
                    'status' => 'active',
                ],
                [
                    'name' => 'Jane Smith',
                    'emp_id' => 'EMP002-' . $company->id,
                    'department' => 'Marketing',
                    'designation' => 'Marketing Manager',
                    'email' => 'jane.smith@company' . $company->id . '.com',
                    'status' => 'active',
                ],
                [
                    'name' => 'Bob Johnson',
                    'emp_id' => 'EMP003-' . $company->id,
                    'department' => 'Sales',
                    'designation' => 'Sales Representative',
                    'email' => 'bob.johnson@company' . $company->id . '.com',
                    'status' => 'inactive',
                ],
            ];

            foreach ($employees as $employeeData) {
                // Check if employee with this emp_id already exists
                $existingEmployee = $company->employees()->where('emp_id', $employeeData['emp_id'])->first();
                
                if (!$existingEmployee) {
                    $employee = EmployeeFactory::create($company->id, $user->id);
                    $employee->fill($employeeData);
                    $employee->save();
                }
            }
        });
    }
}