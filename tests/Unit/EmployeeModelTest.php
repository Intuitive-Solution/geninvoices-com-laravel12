<?php

namespace Tests\Unit;

use App\Models\Employee;
use Tests\TestCase;

class EmployeeModelTest extends TestCase
{
    /**
     * Test that status-related methods have been removed from Employee model.
     */
    public function testStatusMethodsRemoved()
    {
        $employee = new Employee();
        
        // Test that status-related methods no longer exist
        $this->assertFalse(method_exists($employee, 'scopeActive'), 'scopeActive method should be removed');
        $this->assertFalse(method_exists($employee, 'scopeInactive'), 'scopeInactive method should be removed');
        $this->assertFalse(method_exists($employee, 'isActive'), 'isActive method should be removed');
        $this->assertFalse(method_exists($employee, 'isInactive'), 'isInactive method should be removed');
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
        
        $expectedFields = ['name', 'emp_id', 'department', 'designation', 'email'];
        
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
}