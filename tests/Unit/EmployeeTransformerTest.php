<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Transformers\EmployeeTransformer;
use Tests\TestCase;

class EmployeeTransformerTest extends TestCase
{
    /**
     * Test that status field is not included in transformer output (using entity state pattern).
     */
    public function testStatusFieldNotIncludedInTransform()
    {
        // Create a mock Employee instance
        $employee = new Employee([
            'id' => 1,
            'name' => 'John Doe',
            'emp_id' => 'EMP001',
            'department' => 'Engineering',
            'designation' => 'Developer',
            'email' => 'john@example.com',
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
        
        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Assert that status field is not present (we use entity state pattern like vendors)
        $this->assertArrayNotHasKey('status', $result, 'Status field should not be present in transformer output');
    }

    /**
     * Test that all other required fields are still included in transformer output.
     */
    public function testRequiredFieldsIncludedInTransform()
    {
        // Create a mock Employee instance
        $employee = new Employee([
            'id' => 1,
            'name' => 'John Doe',
            'emp_id' => 'EMP001',
            'department' => 'Engineering',
            'designation' => 'Developer',
            'email' => 'john@example.com',
            'is_deleted' => false,
            'user_id' => 1,
        ]);
        
        // Set timestamps manually since we're not using the database
        $employee->created_at = now();
        $employee->updated_at = now();
        $employee->deleted_at = null;
        
        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Assert that all required fields are present
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
            $this->assertArrayHasKey($field, $result, "Field {$field} should be present in transformer output");
        }
    }

    /**
     * Test that transformer output structure matches expected format.
     */
    public function testTransformerOutputStructure()
    {
        // Create a mock Employee instance
        $employee = new Employee([
            'id' => 1,
            'name' => 'John Doe',
            'emp_id' => 'EMP001',
            'department' => 'Engineering',
            'designation' => 'Developer',
            'email' => 'john@example.com',
            'is_deleted' => false,
            'user_id' => 1,
        ]);
        
        // Set timestamps manually since we're not using the database
        $employee->created_at = now();
        $employee->updated_at = now();
        $employee->deleted_at = null;
        $employee->archived_at = null;
        
        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Test data types and structure
        $this->assertIsString($result['id'], 'ID should be a string (encoded)');
        $this->assertIsString($result['name'], 'Name should be a string');
        $this->assertIsString($result['emp_id'], 'Employee ID should be a string');
        $this->assertIsString($result['department'], 'Department should be a string');
        $this->assertIsString($result['designation'], 'Designation should be a string');
        $this->assertIsString($result['email'], 'Email should be a string');
        $this->assertIsBool($result['is_deleted'], 'is_deleted should be a boolean');
        $this->assertIsString($result['user_id'], 'User ID should be a string (encoded)');
        $this->assertIsInt($result['created_at'], 'created_at should be an integer timestamp');
        $this->assertIsInt($result['updated_at'], 'updated_at should be an integer timestamp');
        $this->assertIsInt($result['archived_at'], 'archived_at should be an integer timestamp');
    }

    /**
     * Test that transformer handles empty/null values correctly.
     */
    public function testTransformerHandlesEmptyValues()
    {
        // Create a mock Employee instance with empty values
        $employee = new Employee([
            'id' => 1,
            'name' => '',
            'emp_id' => null,
            'department' => '',
            'designation' => null,
            'email' => '',
            'is_deleted' => false,
            'user_id' => 1,
        ]);
        
        // Set timestamps manually since we're not using the database
        $employee->created_at = now();
        $employee->updated_at = now();
        $employee->deleted_at = null;
        $employee->archived_at = null;
        
        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Test that empty values are handled correctly (converted to empty strings)
        $this->assertEquals('', $result['name'], 'Empty name should be empty string');
        $this->assertEquals('', $result['emp_id'], 'Null emp_id should be empty string');
        $this->assertEquals('', $result['department'], 'Empty department should be empty string');
        $this->assertEquals('', $result['designation'], 'Null designation should be empty string');
        $this->assertEquals('', $result['email'], 'Empty email should be empty string');
        
        // Status field should not be present (we use entity state pattern)
        $this->assertArrayNotHasKey('status', $result, 'Status field should not be present');
    }

    /**
     * Test that archived_at field is properly formatted when null.
     */
    public function testArchivedAtFieldFormattingWhenNull()
    {
        // Create a mock Employee instance with null archived_at
        $employee = new Employee([
            'id' => 1,
            'name' => 'John Doe',
            'emp_id' => 'EMP001',
            'department' => 'Engineering',
            'designation' => 'Developer',
            'email' => 'john@example.com',
            'is_deleted' => false,
            'user_id' => 1,
        ]);
        
        // Set timestamps manually since we're not using the database
        $employee->created_at = now();
        $employee->updated_at = now();
        $employee->deleted_at = null;
        $employee->archived_at = null;
        
        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Test that null archived_at is converted to 0
        $this->assertEquals(0, $result['archived_at'], 'Null archived_at should be converted to 0');
        $this->assertIsInt($result['archived_at'], 'archived_at should be an integer');
    }

    /**
     * Test that archived_at field is properly formatted when set.
     */
    public function testArchivedAtFieldFormattingWhenSet()
    {
        // Create a mock Employee instance with set archived_at
        $employee = new Employee([
            'id' => 1,
            'name' => 'John Doe',
            'emp_id' => 'EMP001',
            'department' => 'Engineering',
            'designation' => 'Developer',
            'email' => 'john@example.com',
            'is_deleted' => false,
            'user_id' => 1,
        ]);
        
        // Set timestamps manually since we're not using the database
        $archivedTime = now();
        $employee->created_at = now();
        $employee->updated_at = now();
        $employee->deleted_at = null;
        $employee->archived_at = $archivedTime;
        
        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Test that archived_at timestamp is properly converted to integer
        $this->assertEquals($archivedTime->timestamp, $result['archived_at'], 'archived_at should be converted to integer timestamp');
        $this->assertIsInt($result['archived_at'], 'archived_at should be an integer');
        $this->assertGreaterThan(0, $result['archived_at'], 'archived_at should be greater than 0 when set');
    }

    /**
     * Test entity state for archived employee.
     */
    public function testEntityStateForArchivedEmployee()
    {
        // Create a mock Employee instance
        $employee = new Employee([
            'id' => 1,
            'name' => 'John Doe',
            'emp_id' => 'EMP001',
            'department' => 'Engineering',
            'designation' => 'Developer',
            'email' => 'john@example.com',
            'is_deleted' => false,
            'user_id' => 1,
        ]);
        
        // Set timestamps manually since we're not using the database
        $employee->created_at = now();
        $employee->updated_at = now();
        $employee->deleted_at = null;
        $employee->archived_at = now();
        
        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Ensure status field is not included (we use entity state pattern)
        $this->assertArrayNotHasKey('status', $result, 'Status field should not be included in API response');
        
        // archived_at should be included for entity state calculation
        $this->assertArrayHasKey('archived_at', $result, 'archived_at field should be included in API response');
        $this->assertGreaterThan(0, $result['archived_at'], 'Archived employee should have archived_at > 0');
    }

    /**
     * Test entity state for deleted employee.
     */
    public function testEntityStateForDeletedEmployee()
    {
        // Create a mock Employee instance
        $employee = new Employee([
            'id' => 1,
            'name' => 'John Doe',
            'emp_id' => 'EMP001',
            'department' => 'Engineering',
            'designation' => 'Developer',
            'email' => 'john@example.com',
            'is_deleted' => true,
            'user_id' => 1,
        ]);
        
        // Set timestamps manually since we're not using the database
        $employee->created_at = now();
        $employee->updated_at = now();
        $employee->deleted_at = now();
        $employee->archived_at = null;
        
        // Ensure is_deleted is properly set as boolean
        $employee->is_deleted = true;
        
        $transformer = new EmployeeTransformer();
        $result = $transformer->transform($employee);
        
        // Ensure status field is not included (we use entity state pattern)
        $this->assertArrayNotHasKey('status', $result, 'Status field should not be included in API response');
        
        // is_deleted should be included for entity state calculation
        $this->assertTrue($result['is_deleted'], 'Deleted employee should have is_deleted = true');
    }
}