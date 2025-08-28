<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Transformers\EmployeeTransformer;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
{

    /**
     * Test that Employee transformer (used by API) uses entity state pattern.
     * This test verifies the transformer behavior without requiring full API setup.
     */
    public function testEmployeeTransformerUsesEntityStatePattern()
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
        $employee->archived_at = null;
        
        // Ensure is_deleted is properly set as boolean
        $employee->is_deleted = false;

        // Use the transformer directly (this is what the API uses)
        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Assert that status field is not present (we use entity state pattern like vendors)
        $this->assertArrayNotHasKey('status', $result, 'Status field should not be present in Employee transformer output (using entity state pattern)');
        
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
            $employee->archived_at = null;
        }

        $transformer = new EmployeeTransformer();
        
        // Transform each employee and verify status field is not present
        foreach ($employees as $employee) {
            $result = $transformer->transform($employee);
            
            // Assert that status field is not present (using entity state pattern)
            $this->assertArrayNotHasKey('status', $result, 'Status field should not be present in Employee transformer output');
            
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
        $employee->archived_at = null;

        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Assert that status field is not present (using entity state pattern)
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
        $employee->archived_at = null;

        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Assert that status field is not present (using entity state pattern)
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

    /**
     * Test that archived_at field is properly formatted in API responses when null.
     */
    public function testArchivedAtFieldFormattingInApiResponseWhenNull()
    {
        // Create employee with null archived_at (active employee)
        $employee = new Employee([
            'id' => 1,
            'name' => 'Active Employee',
            'emp_id' => 'EMP001',
            'department' => 'Engineering',
            'designation' => 'Developer',
            'email' => 'active@example.com',
            'is_deleted' => false,
            'user_id' => 1,
        ]);
        
        $employee->created_at = now();
        $employee->updated_at = now();
        $employee->deleted_at = null;
        $employee->archived_at = null;

        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Verify archived_at is properly formatted as 0 when null
        $this->assertArrayHasKey('archived_at', $result, 'archived_at field should be present in API response');
        $this->assertEquals(0, $result['archived_at'], 'archived_at should be 0 when null (active employee)');
        $this->assertIsInt($result['archived_at'], 'archived_at should be an integer in API response');
    }

    /**
     * Test that archived_at field is properly formatted in API responses when set.
     */
    public function testArchivedAtFieldFormattingInApiResponseWhenSet()
    {
        // Create employee with set archived_at (archived employee)
        $employee = new Employee([
            'id' => 2,
            'name' => 'Archived Employee',
            'emp_id' => 'EMP002',
            'department' => 'Marketing',
            'designation' => 'Manager',
            'email' => 'archived@example.com',
            'is_deleted' => false,
            'user_id' => 2,
        ]);
        
        $archivedTime = now()->subDays(7);
        $employee->created_at = now()->subDays(30);
        $employee->updated_at = now();
        $employee->deleted_at = null;
        $employee->archived_at = $archivedTime;

        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Verify archived_at is properly formatted as timestamp when set
        $this->assertArrayHasKey('archived_at', $result, 'archived_at field should be present in API response');
        $this->assertEquals($archivedTime->timestamp, $result['archived_at'], 'archived_at should be timestamp when set (archived employee)');
        $this->assertIsInt($result['archived_at'], 'archived_at should be an integer in API response');
        $this->assertGreaterThan(0, $result['archived_at'], 'archived_at should be greater than 0 when set');
    }

    /**
     * Test entity state pattern in API responses for different employee states.
     */
    public function testEntityStatePatternInApiResponseForDifferentStates()
    {
        // Create employees with different states
        $activeEmployee = new Employee([
            'id' => 1,
            'name' => 'Active Employee',
            'emp_id' => 'EMP001',
            'department' => 'Engineering',
            'designation' => 'Developer',
            'email' => 'active@example.com',
            'is_deleted' => false,
            'user_id' => 1,
        ]);
        $activeEmployee->created_at = now();
        $activeEmployee->updated_at = now();
        $activeEmployee->deleted_at = null;
        $activeEmployee->archived_at = null;
        $activeEmployee->is_deleted = false;

        $archivedEmployee = new Employee([
            'id' => 2,
            'name' => 'Archived Employee',
            'emp_id' => 'EMP002',
            'department' => 'Marketing',
            'designation' => 'Manager',
            'email' => 'archived@example.com',
            'is_deleted' => false,
            'user_id' => 2,
        ]);
        $archivedEmployee->created_at = now()->subDays(30);
        $archivedEmployee->updated_at = now();
        $archivedEmployee->deleted_at = null;
        $archivedEmployee->archived_at = now()->subDays(7);
        $archivedEmployee->is_deleted = false;

        $transformer = new EmployeeTransformer();
        
        // Test active employee
        $activeResult = $transformer->transform($activeEmployee);
        $this->assertArrayNotHasKey('status', $activeResult, 'Status field should not be present in API response (using entity state pattern)');
        $this->assertArrayHasKey('archived_at', $activeResult, 'archived_at field should be present in API response for active employee');
        $this->assertArrayHasKey('is_deleted', $activeResult, 'is_deleted field should be present in API response for active employee');
        
        // Test archived employee
        $archivedResult = $transformer->transform($archivedEmployee);
        $this->assertArrayNotHasKey('status', $archivedResult, 'Status field should not be present in API response (using entity state pattern)');
        $this->assertArrayHasKey('archived_at', $archivedResult, 'archived_at field should be present in API response for archived employee');
        $this->assertArrayHasKey('is_deleted', $archivedResult, 'is_deleted field should be present in API response for archived employee');
        
        // Verify archived_at values are different (this is how entity state is determined)
        $this->assertEquals(0, $activeResult['archived_at'], 'Active employee should have archived_at = 0');
        $this->assertGreaterThan(0, $archivedResult['archived_at'], 'Archived employee should have archived_at > 0');
    }
}