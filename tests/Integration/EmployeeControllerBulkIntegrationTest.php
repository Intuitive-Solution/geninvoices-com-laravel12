<?php

namespace Tests\Integration;

use App\Http\Controllers\EmployeeController;
use App\Http\Requests\Employee\BulkEmployeeRequest;
use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Tests\TestCase;
use Illuminate\Http\Request;
use Mockery;

class EmployeeControllerBulkIntegrationTest extends TestCase
{
    public function testBulkMethodImplementation()
    {
        $repository = new EmployeeRepository();
        $controller = new EmployeeController($repository);
        
        // Test that the bulk method exists and has the right signature
        $this->assertTrue(method_exists($controller, 'bulk'));
        
        $reflection = new \ReflectionMethod($controller, 'bulk');
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        
        // Test that it uses BulkEmployeeRequest
        $parameters = $reflection->getParameters();
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals(BulkEmployeeRequest::class, $parameters[0]->getType()->getName());
    }

    public function testBulkMethodSupportsValidActions()
    {
        $repository = new EmployeeRepository();
        
        // Test that repository has the expected methods for bulk actions
        $validActions = ['archive', 'restore', 'delete'];
        
        foreach ($validActions as $action) {
            $this->assertTrue(
                method_exists($repository, $action),
                "Repository should have {$action} method for bulk action"
            );
        }
        
        // Test that repository doesn't have the old status-related methods
        $invalidActions = ['activate', 'deactivate'];
        
        foreach ($invalidActions as $action) {
            $this->assertFalse(
                method_exists($repository, $action),
                "Repository should not have {$action} method"
            );
        }
    }

    public function testBulkEmployeeRequestValidation()
    {
        $request = new BulkEmployeeRequest();
        
        // Test authorization
        $this->assertTrue($request->authorize());
        
        // Test validation rules
        $rules = $request->rules();
        $this->assertArrayHasKey('ids', $rules);
        $this->assertArrayHasKey('action', $rules);
        
        // Test that only valid actions are allowed
        $this->assertEquals('in:archive,restore,delete', $rules['action']);
        
        // Test that activate/deactivate are not allowed
        $this->assertStringNotContainsString('activate', $rules['action']);
        $this->assertStringNotContainsString('deactivate', $rules['action']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}