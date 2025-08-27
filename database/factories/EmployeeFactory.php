<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'emp_id' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'department' => $this->faker->randomElement(['Engineering', 'Marketing', 'Sales', 'HR', 'Finance']),
            'designation' => $this->faker->randomElement(['Developer', 'Manager', 'Analyst', 'Coordinator', 'Specialist']),
            'email' => $this->faker->unique()->safeEmail(),
            'is_deleted' => false,
        ];
    }

    public function deleted()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_deleted' => true,
            ];
        });
    }
}