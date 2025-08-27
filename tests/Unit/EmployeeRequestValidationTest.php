<?php

namespace Tests\Unit;

use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class EmployeeRequestValidationTest extends TestCase
{

    /**
     * Test that StoreEmployeeRequest does not validate status field.
     */
    public function testStoreEmployeeRequestDoesNotValidateStatus()
    {
        // Mock the auth user and company to avoid database calls
        $this->mockAuthUser();
        
        $request = new StoreEmployeeRequest();
        $rules = $request->rules();
        
        // Assert that status field is not in validation rules
        $this->assertArrayNotHasKey('status', $rules, 'Status field should not be validated in StoreEmployeeRequest');
        
        // Assert that other required fields are still validated
        $expectedFields = ['name', 'emp_id', 'department', 'designation', 'email'];
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $rules, "Field {$field} should be validated in StoreEmployeeRequest");
        }
    }

    /**
     * Mock auth user and company to avoid database dependencies.
     */
    private function mockAuthUser()
    {
        $mockCompany = $this->createMock(\App\Models\Company::class);
        $mockCompany->id = 1;
        
        $mockUser = $this->createMock(\App\Models\User::class);
        $mockUser->method('company')->willReturn($mockCompany);
        
        $this->actingAs($mockUser);
    }

    /**
     * Test that UpdateEmployeeRequest does not validate status field.
     */
    public function testUpdateEmployeeRequestDoesNotValidateStatus()
    {
        // Mock the auth user and company to avoid database calls
        $this->mockAuthUser();
        
        // Create a mock employee for the update request context
        $employee = new Employee(['id' => 1, 'company_id' => 1]);
        
        $request = new UpdateEmployeeRequest();
        $request->setRouteResolver(function () use ($employee) {
            return new class($employee) {
                private $employee;
                public function __construct($employee) { $this->employee = $employee; }
                public function parameter($key) { return $key === 'employee' ? $this->employee : null; }
            };
        });
        
        $rules = $request->rules();
        
        // Assert that status field is not in validation rules
        $this->assertArrayNotHasKey('status', $rules, 'Status field should not be validated in UpdateEmployeeRequest');
        
        // Assert that other required fields are still validated with 'sometimes' rule
        $expectedFields = ['name', 'emp_id', 'department', 'designation', 'email'];
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $rules, "Field {$field} should be validated in UpdateEmployeeRequest");
            $this->assertStringContainsString('sometimes', $rules[$field], "Field {$field} should have 'sometimes' rule in UpdateEmployeeRequest");
        }
    }

    /**
     * Test that StoreEmployeeRequest validation rules structure is correct without status field.
     */
    public function testStoreEmployeeRequestValidationRulesStructure()
    {
        // Mock the auth user and company to avoid database calls
        $this->mockAuthUser();
        
        $request = new StoreEmployeeRequest();
        $rules = $request->rules();
        
        // Test that status field is not in validation rules
        $this->assertArrayNotHasKey('status', $rules, 'Status field should not be in validation rules');
        
        // Test that required fields have proper validation rules
        $this->assertStringContainsString('required', $rules['name'], 'name should be required');
        $this->assertStringContainsString('required', $rules['emp_id'], 'emp_id should be required');
        $this->assertStringContainsString('required', $rules['department'], 'department should be required');
        $this->assertStringContainsString('required', $rules['designation'], 'designation should be required');
        $this->assertStringContainsString('required', $rules['email'], 'email should be required');
        $this->assertStringContainsString('email', $rules['email'], 'email should have email validation');
    }

    /**
     * Test that UpdateEmployeeRequest validation rules structure is correct without status field.
     */
    public function testUpdateEmployeeRequestValidationRulesStructure()
    {
        // Mock the auth user and company to avoid database calls
        $this->mockAuthUser();
        
        // Create a mock employee for the update request context
        $employee = new Employee(['id' => 1, 'company_id' => 1]);
        
        $request = new UpdateEmployeeRequest();
        $request->setRouteResolver(function () use ($employee) {
            return new class($employee) {
                private $employee;
                public function __construct($employee) { $this->employee = $employee; }
                public function parameter($key) { return $key === 'employee' ? $this->employee : null; }
            };
        });
        
        $rules = $request->rules();
        
        // Test that status field is not in validation rules
        $this->assertArrayNotHasKey('status', $rules, 'Status field should not be in validation rules');
        
        // Test that fields have proper validation rules with 'sometimes'
        $this->assertStringContainsString('sometimes', $rules['name'], 'name should have sometimes rule');
        $this->assertStringContainsString('sometimes', $rules['emp_id'], 'emp_id should have sometimes rule');
        $this->assertStringContainsString('sometimes', $rules['department'], 'department should have sometimes rule');
        $this->assertStringContainsString('sometimes', $rules['designation'], 'designation should have sometimes rule');
        $this->assertStringContainsString('sometimes', $rules['email'], 'email should have sometimes rule');
        $this->assertStringContainsString('email', $rules['email'], 'email should have email validation');
    }

    /**
     * Test that StoreEmployeeRequest ignores status field in validation rules.
     * This ensures that if someone tries to send status field, it's ignored by validation.
     */
    public function testStoreEmployeeRequestIgnoresStatusField()
    {
        // Mock the auth user and company to avoid database calls
        $this->mockAuthUser();
        
        $request = new StoreEmployeeRequest();
        $rules = $request->rules();
        
        // Test that status field is not in the validation rules at all
        $this->assertArrayNotHasKey('status', $rules, 'Status field should not be in validation rules');
        
        // Test that if status field is provided in data, it won't be validated
        // because it's not in the rules array
        $dataWithStatus = [
            'name' => 'Test Employee',
            'emp_id' => 'EMP002',
            'department' => 'Engineering',
            'designation' => 'Developer',
            'email' => 'test2@example.com',
            'status' => 'active', // This should be ignored
        ];
        
        // Since status is not in rules, it will be ignored during validation
        $fieldsToValidate = array_keys($rules);
        $this->assertNotContains('status', $fieldsToValidate, 'Status should not be in fields to validate');
    }

    /**
     * Test that UpdateEmployeeRequest ignores status field in validation rules.
     */
    public function testUpdateEmployeeRequestIgnoresStatusField()
    {
        // Mock the auth user and company to avoid database calls
        $this->mockAuthUser();
        
        // Create a mock employee for the update request context
        $employee = new Employee(['id' => 1, 'company_id' => 1]);
        
        $request = new UpdateEmployeeRequest();
        $request->setRouteResolver(function () use ($employee) {
            return new class($employee) {
                private $employee;
                public function __construct($employee) { $this->employee = $employee; }
                public function parameter($key) { return $key === 'employee' ? $this->employee : null; }
            };
        });
        
        $rules = $request->rules();
        
        // Test that status field is not in the validation rules at all
        $this->assertArrayNotHasKey('status', $rules, 'Status field should not be in validation rules');
        
        // Test that if status field is provided in data, it won't be validated
        // because it's not in the rules array
        $dataWithStatus = [
            'name' => 'Updated Employee Name',
            'department' => 'Marketing',
            'status' => 'inactive', // This should be ignored
        ];
        
        // Since status is not in rules, it will be ignored during validation
        $fieldsToValidate = array_keys($rules);
        $this->assertNotContains('status', $fieldsToValidate, 'Status should not be in fields to validate');
    }

    /**
     * Test that StoreEmployeeRequest still validates required fields properly.
     */
    public function testStoreEmployeeRequestValidatesRequiredFields()
    {
        // Mock the auth user and company to avoid database calls
        $this->mockAuthUser();
        
        $request = new StoreEmployeeRequest();
        $rules = $request->rules();
        
        // Test that all expected required fields are present in rules
        $expectedRequiredFields = ['name', 'emp_id', 'department', 'designation', 'email'];
        
        foreach ($expectedRequiredFields as $field) {
            $this->assertArrayHasKey($field, $rules, "Field {$field} should be in validation rules");
            $this->assertStringContainsString('required', $rules[$field], "Field {$field} should be required");
        }
        
        // Test that status field is not required (and not present at all)
        $this->assertArrayNotHasKey('status', $rules, 'Status field should not be in validation rules');
    }

    /**
     * Test that UpdateEmployeeRequest validates fields properly with 'sometimes' rule.
     */
    public function testUpdateEmployeeRequestValidatesFieldsWithSometimes()
    {
        // Mock the auth user and company to avoid database calls
        $this->mockAuthUser();
        
        // Create a mock employee for the update request context
        $employee = new Employee(['id' => 1, 'company_id' => 1]);
        
        $request = new UpdateEmployeeRequest();
        $request->setRouteResolver(function () use ($employee) {
            return new class($employee) {
                private $employee;
                public function __construct($employee) { $this->employee = $employee; }
                public function parameter($key) { return $key === 'employee' ? $this->employee : null; }
            };
        });
        
        $rules = $request->rules();
        
        // Test that all expected fields have 'sometimes' rule
        $expectedFields = ['name', 'emp_id', 'department', 'designation', 'email'];
        
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $rules, "Field {$field} should be in validation rules");
            $this->assertStringContainsString('sometimes', $rules[$field], "Field {$field} should have 'sometimes' rule");
        }
        
        // Test that status field is not present at all
        $this->assertArrayNotHasKey('status', $rules, 'Status field should not be in validation rules');
    }

    /**
     * Test that both request classes maintain proper authorization methods.
     */
    public function testRequestAuthorizationMethods()
    {
        $storeRequest = new StoreEmployeeRequest();
        $updateRequest = new UpdateEmployeeRequest();
        
        // Test that authorization methods exist
        $this->assertTrue(method_exists($storeRequest, 'authorize'), 'StoreEmployeeRequest should have authorize method');
        $this->assertTrue(method_exists($updateRequest, 'authorize'), 'UpdateEmployeeRequest should have authorize method');
        
        // Test that prepareForValidation methods exist
        $this->assertTrue(method_exists($storeRequest, 'prepareForValidation'), 'StoreEmployeeRequest should have prepareForValidation method');
        $this->assertTrue(method_exists($updateRequest, 'prepareForValidation'), 'UpdateEmployeeRequest should have prepareForValidation method');
    }
}