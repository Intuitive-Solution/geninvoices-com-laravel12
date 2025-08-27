<?php

namespace Tests\Unit;

use App\Http\Controllers\EmployeeController;
use App\Http\Requests\Employee\BulkEmployeeRequest;
use App\Repositories\EmployeeRepository;
use Tests\TestCase;

class EmployeeControllerBulkTest extends TestCase
{
    public function testBulkMethodUsesRequestValidation()
    {
        $repository = new EmployeeRepository();
        $controller = new EmployeeController($repository);
        
        // Test that the bulk method exists and uses BulkEmployeeRequest
        $this->assertTrue(
            method_exists($controller, 'bulk'),
            'EmployeeController should have bulk method'
        );
        
        $reflection = new \ReflectionMethod($controller, 'bulk');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals(BulkEmployeeRequest::class, $parameters[0]->getType()->getName());
    }

    public function testBulkEmployeeRequestValidatesCorrectActions()
    {
        $request = new BulkEmployeeRequest();
        $rules = $request->rules();
        
        // Test that only archive, restore, delete actions are allowed
        $this->assertArrayHasKey('action', $rules);
        $this->assertEquals('in:archive,restore,delete', $rules['action']);
        
        // Test that ids are required and validated
        $this->assertArrayHasKey('ids', $rules);
        $this->assertContains('required', $rules['ids']);
        $this->assertContains('array', $rules['ids']);
    }

    public function testRepositoryMethodsExist()
    {
        // Test that the repository has the required methods
        $repository = new EmployeeRepository();
        
        $this->assertTrue(
            method_exists($repository, 'archive'),
            'EmployeeRepository should have archive method from BaseRepository'
        );
        
        $this->assertTrue(
            method_exists($repository, 'restore'),
            'EmployeeRepository should have restore method from BaseRepository'
        );
        
        $this->assertTrue(
            method_exists($repository, 'delete'),
            'EmployeeRepository should have delete method from BaseRepository'
        );
    }

    public function testControllerUsesRepositoryPattern()
    {
        $repository = new EmployeeRepository();
        $controller = new EmployeeController($repository);
        
        // Test that the controller uses dependency injection for repository
        $reflection = new \ReflectionClass($controller);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();
        
        $this->assertCount(1, $parameters);
        $this->assertEquals('employee_repo', $parameters[0]->getName());
        $this->assertEquals(EmployeeRepository::class, $parameters[0]->getType()->getName());
    }

    public function testBulkMethodStructure()
    {
        $repository = new EmployeeRepository();
        $controller = new EmployeeController($repository);
        
        // Get the bulk method and check its structure
        $reflection = new \ReflectionMethod($controller, 'bulk');
        $methodContent = file_get_contents($reflection->getFileName());
        
        // Extract the bulk method content
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();
        $lines = file($reflection->getFileName());
        $methodLines = array_slice($lines, $startLine - 1, $endLine - $startLine + 1);
        $methodContent = implode('', $methodLines);
        
        // Test that the method uses repository pattern
        $this->assertStringContainsString('$this->employee_repo->{$action}($employee)', $methodContent);
        
        // Test that activate/deactivate cases are not present
        $this->assertStringNotContainsString('activate', $methodContent);
        $this->assertStringNotContainsString('deactivate', $methodContent);
        $this->assertStringNotContainsString('performAction', $methodContent);
    }
}