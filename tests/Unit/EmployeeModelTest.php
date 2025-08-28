<?php

namespace Tests\Unit;

use App\Models\Employee;
use Tests\TestCase;

class EmployeeModelTest extends TestCase
{
    /**
     * Test that EntityState helper methods exist on Employee model.
     */
    public function testEntityStateMethodsExist()
    {
        $employee = new Employee();
        
        // Test that EntityState helper methods exist
        $this->assertTrue(method_exists($employee, 'isActive'), 'isActive method should exist');
        $this->assertTrue(method_exists($employee, 'isArchived'), 'isArchived method should exist');
        $this->assertTrue(method_exists($employee, 'isDeleted'), 'isDeleted method should exist');
    }

    /**
     * Test that status field is not in fillable array.
     */
    public function testStatusNotInFillable()
    {
        $employee = new Employee();
        $fillable = $employee->getFillable();
        
        $this->assertNotContains('status', $fillable, 'Status field should not be in fillable array');
    }

    /**
     * Test that required fields are still in fillable array.
     */
    public function testRequiredFieldsInFillable()
    {
        $employee = new Employee();
        $fillable = $employee->getFillable();
        
        $expectedFields = ['name', 'emp_id', 'department', 'designation', 'email', 'archived_at'];
        
        foreach ($expectedFields as $field) {
            $this->assertContains($field, $fillable, "Field {$field} should be in fillable array");
        }
    }

    /**
     * Test that Employee model still uses required traits.
     */
    public function testRequiredTraits()
    {
        $employee = new Employee();
        
        $this->assertTrue(method_exists($employee, 'getHashedIdAttribute'), 'MakesHash trait should be present');
        $this->assertTrue(method_exists($employee, 'present'), 'PresentableTrait should be present');
        $this->assertTrue(method_exists($employee, 'trashed'), 'SoftDeletes trait should be present');
        $this->assertTrue(method_exists($employee, 'scopeFilter'), 'Filterable trait should be present');
    }

    /**
     * Test isActive method returns true for active employees.
     */
    public function testIsActiveReturnsTrueForActiveEmployees()
    {
        $employee = new Employee();
        $employee->archived_at = null;
        $employee->is_deleted = false;
        
        $this->assertTrue($employee->isActive(), 'Employee should be active when archived_at is null and is_deleted is false');
    }

    /**
     * Test isActive method returns false for archived employees.
     */
    public function testIsActiveReturnsFalseForArchivedEmployees()
    {
        $employee = new Employee();
        $employee->archived_at = now();
        $employee->is_deleted = false;
        
        $this->assertFalse($employee->isActive(), 'Employee should not be active when archived_at is set');
    }

    /**
     * Test isActive method returns false for deleted employees.
     */
    public function testIsActiveReturnsFalseForDeletedEmployees()
    {
        $employee = new Employee();
        $employee->archived_at = null;
        $employee->is_deleted = true;
        
        $this->assertFalse($employee->isActive(), 'Employee should not be active when is_deleted is true');
    }

    /**
     * Test isArchived method returns true for archived employees.
     */
    public function testIsArchivedReturnsTrueForArchivedEmployees()
    {
        $employee = new Employee();
        $employee->archived_at = now();
        $employee->is_deleted = false;
        
        $this->assertTrue($employee->isArchived(), 'Employee should be archived when archived_at is set and is_deleted is false');
    }

    /**
     * Test isArchived method returns false for active employees.
     */
    public function testIsArchivedReturnsFalseForActiveEmployees()
    {
        $employee = new Employee();
        $employee->archived_at = null;
        $employee->is_deleted = false;
        
        $this->assertFalse($employee->isArchived(), 'Employee should not be archived when archived_at is null');
    }

    /**
     * Test isArchived method returns false for deleted employees.
     */
    public function testIsArchivedReturnsFalseForDeletedEmployees()
    {
        $employee = new Employee();
        $employee->archived_at = now();
        $employee->is_deleted = true;
        
        $this->assertFalse($employee->isArchived(), 'Employee should not be archived when is_deleted is true');
    }

    /**
     * Test isDeleted method returns true for deleted employees.
     */
    public function testIsDeletedReturnsTrueForDeletedEmployees()
    {
        $employee = new Employee();
        $employee->is_deleted = true;
        
        $this->assertTrue($employee->isDeleted(), 'Employee should be deleted when is_deleted is true');
    }

    /**
     * Test isDeleted method returns false for active employees.
     */
    public function testIsDeletedReturnsFalseForActiveEmployees()
    {
        $employee = new Employee();
        $employee->archived_at = null;
        $employee->is_deleted = false;
        
        $this->assertFalse($employee->isDeleted(), 'Employee should not be deleted when is_deleted is false');
    }

    /**
     * Test isDeleted method returns false for archived employees.
     */
    public function testIsDeletedReturnsFalseForArchivedEmployees()
    {
        $employee = new Employee();
        $employee->archived_at = now();
        $employee->is_deleted = false;
        
        $this->assertFalse($employee->isDeleted(), 'Employee should not be deleted when is_deleted is false');
    }

    /**
     * Test that archived_at is properly cast as timestamp.
     */
    public function testArchivedAtCastAsTimestamp()
    {
        $employee = new Employee();
        $casts = $employee->getCasts();
        
        $this->assertArrayHasKey('archived_at', $casts, 'archived_at should be in casts array');
        $this->assertEquals('timestamp', $casts['archived_at'], 'archived_at should be cast as timestamp');
    }
}