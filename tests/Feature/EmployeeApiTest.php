<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Transformers\EmployeeTransformer;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
{

    /**
     * Test that Employee transformer (used by API) does not include status field.
     * This test verifies the transformer behavior without requiring full API setup.
     */
    public function testEmployeeTransformerDoesNotIncludeStatusField()
    {
        // Create a mock Employee instance with all required attributes
        $employee = new Employee([
            'id' => 1,
            'name' => 'Test Employee',
            'emp_id' => 'EMP001',
            'department' => 'Engineering',
            'designation' => 'Developer',
            'email' => 'test@example.com',
            'is_deleted' => false,
            'user_id' => 1,
        ]);
        
        // Set timestamps manually since we're not using the database
        $employee->created_at = now();
        $employee->updated_at = now();
        $employee->deleted_at = null;

        // Use the transformer directly (this is what the API uses)
        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Assert that status field is not present in the transformer result
        $this->assertArrayNotHasKey('status', $result, 'Status field should not be present in Employee transformer output (used by API)');
        
        // Assert that other required fields are present
        $expectedFields = [
            'id',
            'name',
            'emp_id',
            'department',
            'designation',
            'email',
            'is_deleted',
            'user_id',
            'created_at',
            'updated_at',
            'archived_at',
        ];
        
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $result, "Field {$field} should be present in Employee transformer output (used by API)");
        }
    }

    /**
     * Test that Employee transformer handles multiple employees correctly.
     * This simulates what happens when the API returns a list of employees.
     */
    public function testEmployeeTransformerHandlesMultipleEmployees()
    {
        // Create multiple mock Employee instances
        $employees = [
            new Employee([
                'id' => 1,
                'name' => 'Employee One',
                'emp_id' => 'EMP001',
                'department' => 'Engineering',
                'designation' => 'Developer',
                'email' => 'emp1@example.com',
                'is_deleted' => false,
                'user_id' => 1,
            ]),
            new Employee([
                'id' => 2,
                'name' => 'Employee Two',
                'emp_id' => 'EMP002',
                'department' => 'Marketing',
                'designation' => 'Manager',
                'email' => 'emp2@example.com',
                'is_deleted' => false,
                'user_id' => 2,
            ]),
        ];
        
        // Set timestamps for each employee
        foreach ($employees as $employee) {
            $employee->created_at = now();
            $employee->updated_at = now();
            $employee->deleted_at = null;
        }

        $transformer = new EmployeeTransformer();
        
        // Transform each employee and verify status field is not present
        foreach ($employees as $employee) {
            $result = $transformer->transform($employee);
            
            // Assert that status field is not present
            $this->assertArrayNotHasKey('status', $result, 'Status field should not be present in any Employee transformer output');
            
            // Assert that required fields are present
            $this->assertArrayHasKey('name', $result);
            $this->assertArrayHasKey('emp_id', $result);
            $this->assertArrayHasKey('department', $result);
            $this->assertArrayHasKey('designation', $result);
            $this->assertArrayHasKey('email', $result);
        }
    }

    /**
     * Test that Employee transformer works correctly with newly created employee data.
     * This simulates what happens when the API creates an employee and returns the response.
     */
    public function testEmployeeTransformerWithNewEmployeeData()
    {
        // Simulate data that would come from an API creation request
        $employeeData = [
            'id' => 999,
            'name' => 'New Employee',
            'emp_id' => 'EMP999',
            'department' => 'Marketing',
            'designation' => 'Manager',
            'email' => 'new@example.com',
            'is_deleted' => false,
            'user_id' => 1,
            // Note: no status field should be processed
        ];

        $employee = new Employee($employeeData);
        $employee->created_at = now();
        $employee->updated_at = now();
        $employee->deleted_at = null;

        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Assert that status field is not present in the response
        $this->assertArrayNotHasKey('status', $result, 'Status field should not be present in Employee creation transformer output');
        
        // Verify the employee data is correctly transformed (excluding status)
        $this->assertEquals('New Employee', $result['name']);
        $this->assertEquals('EMP999', $result['emp_id']);
        $this->assertEquals('Marketing', $result['department']);
        $this->assertEquals('Manager', $result['designation']);
        $this->assertEquals('new@example.com', $result['email']);
        $this->assertFalse($result['is_deleted']);
    }

    /**
     * Test that Employee transformer works correctly with updated employee data.
     * This simulates what happens when the API updates an employee and returns the response.
     */
    public function testEmployeeTransformerWithUpdatedEmployeeData()
    {
        // Simulate an employee with updated data
        $employee = new Employee([
            'id' => 2,
            'name' => 'Updated Name',
            'emp_id' => 'EMP002',
            'department' => 'Sales',
            'designation' => 'Developer',
            'email' => 'original@example.com',
            'is_deleted' => false,
            'user_id' => 1,
            // Note: no status field should be processed even if it was in the update request
        ]);

        $employee->created_at = now()->subDays(30);
        $employee->updated_at = now();
        $employee->deleted_at = null;

        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Assert that status field is not present in the response
        $this->assertArrayNotHasKey('status', $result, 'Status field should not be present in Employee update transformer output');
        
        // Verify the employee was updated with the correct data (excluding status)
        $this->assertEquals('Updated Name', $result['name']);
        $this->assertEquals('Sales', $result['department']);
        $this->assertEquals('EMP002', $result['emp_id']);
        $this->assertEquals('original@example.com', $result['email']);
        $this->assertFalse($result['is_deleted']);
        
        // Verify timestamps are properly handled
        $this->assertIsInt($result['created_at']);
        $this->assertIsInt($result['updated_at']);
        $this->assertIsInt($result['archived_at']);
    }
}