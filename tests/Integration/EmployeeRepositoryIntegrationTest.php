<?php

namespace Tests\Integration;

use App\Events\Employee\EmployeeWasArchived;
use App\Events\Employee\EmployeeWasDeleted;
use App\Events\Employee\EmployeeWasRestored;
use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Mockery;

class EmployeeRepositoryIntegrationTest extends TestCase
{
    private EmployeeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = new EmployeeRepository();
    }

    /**
     * Test that archive method works with Employee model.
     */
    public function testArchiveMethodWithEmployeeModel()
    {
        Event::fake();

        // Create a mock employee that behaves like a real Employee
        $employee = Mockery::mock(Employee::class);
        $employee->shouldReceive('getAttribute')->with('archived_at')->andReturn(null);
        $employee->shouldReceive('setAttribute')->with('archived_at', Mockery::any())->once();
        $employee->shouldReceive('save')->once()->andReturn(true);
        
        // Mock company relationship
        $company = Mockery::mock(\App\Models\Company::class);
        $employee->shouldReceive('getAttribute')->with('company')->andReturn($company);

        // Call archive method
        $this->repository->archive($employee);

        // Verify the employee was modified
        $employee->shouldHaveReceived('setAttribute')->with('archived_at', Mockery::any());
        $employee->shouldHaveReceived('save');
    }

    /**
     * Test that restore method works with Employee model.
     */
    public function testRestoreMethodWithEmployeeModel()
    {
        Event::fake();

        // Create a mock employee that behaves like a real Employee
        $employee = Mockery::mock(Employee::class);
        $employee->shouldReceive('getAttribute')->with('is_deleted')->andReturn(false);
        $employee->shouldReceive('setAttribute')->with('archived_at', null)->once();
        $employee->shouldReceive('save')->once()->andReturn(true);
        $employee->shouldReceive('trashed')->andReturn(false);
        
        // Mock company relationship
        $company = Mockery::mock(\App\Models\Company::class);
        $employee->shouldReceive('getAttribute')->with('company')->andReturn($company);

        // Call restore method
        $this->repository->restore($employee);

        // Verify the employee was modified
        $employee->shouldHaveReceived('setAttribute')->with('archived_at', null);
        $employee->shouldHaveReceived('save');
    }

    /**
     * Test that delete method works with Employee model.
     */
    public function testDeleteMethodWithEmployeeModel()
    {
        Event::fake();

        // Create a mock employee that behaves like a real Employee
        $employee = Mockery::mock(Employee::class);
        $employee->shouldReceive('getAttribute')->with('is_deleted')->andReturn(false);
        $employee->shouldReceive('setAttribute')->with('is_deleted', true)->once();
        $employee->shouldReceive('save')->once()->andReturn(true);
        $employee->shouldReceive('delete')->once(); // Soft delete
        
        // Mock company relationship
        $company = Mockery::mock(\App\Models\Company::class);
        $employee->shouldReceive('getAttribute')->with('company')->andReturn($company);

        // Call delete method
        $this->repository->delete($employee);

        // Verify the employee was modified
        $employee->shouldHaveReceived('setAttribute')->with('is_deleted', true);
        $employee->shouldHaveReceived('save');
        $employee->shouldHaveReceived('delete');
    }

    /**
     * Test that archive method skips already archived employees.
     */
    public function testArchiveMethodSkipsAlreadyArchivedEmployees()
    {
        // Create a mock employee that is already archived
        $employee = Mockery::mock(Employee::class);
        $employee->shouldReceive('getAttribute')->with('archived_at')->andReturn(now());
        $employee->shouldNotReceive('save'); // Should not be called

        // Call archive method
        $this->repository->archive($employee);

        // Verify save was not called
        $employee->shouldNotHaveReceived('save');
    }

    /**
     * Test that delete method skips already deleted employees.
     */
    public function testDeleteMethodSkipsAlreadyDeletedEmployees()
    {
        // Create a mock employee that is already deleted
        $employee = Mockery::mock(Employee::class);
        $employee->shouldReceive('getAttribute')->with('is_deleted')->andReturn(true);
        $employee->shouldNotReceive('save'); // Should not be called

        // Call delete method
        $this->repository->delete($employee);

        // Verify save was not called
        $employee->shouldNotHaveReceived('save');
    }

    /**
     * Test that restore method handles deleted employees correctly.
     */
    public function testRestoreMethodHandlesDeletedEmployees()
    {
        Event::fake();

        // Create a mock employee that is deleted
        $employee = Mockery::mock(Employee::class);
        $employee->shouldReceive('getAttribute')->with('is_deleted')->andReturn(true);
        $employee->shouldReceive('setAttribute')->with('is_deleted', false)->once();
        $employee->shouldReceive('setAttribute')->with('archived_at', null)->once();
        $employee->shouldReceive('save')->once()->andReturn(true);
        $employee->shouldReceive('trashed')->andReturn(true);
        $employee->shouldReceive('restore')->once(); // Restore from soft delete
        
        // Mock company relationship
        $company = Mockery::mock(\App\Models\Company::class);
        $employee->shouldReceive('getAttribute')->with('company')->andReturn($company);

        // Call restore method
        $this->repository->restore($employee);

        // Verify the employee was modified correctly
        $employee->shouldHaveReceived('setAttribute')->with('is_deleted', false);
        $employee->shouldHaveReceived('setAttribute')->with('archived_at', null);
        $employee->shouldHaveReceived('save');
        $employee->shouldHaveReceived('restore');
    }

    /**
     * Test that event class names are constructed correctly.
     */
    public function testEventClassNamesAreConstructedCorrectly()
    {
        // Use reflection to access the private getEventClass method
        $reflection = new \ReflectionClass($this->repository);
        $method = $reflection->getMethod('getEventClass');
        $method->setAccessible(true);

        $employee = new Employee();

        // Test event class name construction
        $archivedClass = $method->invoke($this->repository, $employee, 'Archived');
        $restoredClass = $method->invoke($this->repository, $employee, 'Restored');
        $deletedClass = $method->invoke($this->repository, $employee, 'Deleted');

        $this->assertEquals('App\Events\Employee\EmployeeWasArchived', $archivedClass);
        $this->assertEquals('App\Events\Employee\EmployeeWasRestored', $restoredClass);
        $this->assertEquals('App\Events\Employee\EmployeeWasDeleted', $deletedClass);
    }

    /**
     * Test that the created event classes exist.
     */
    public function testCreatedEventClassesExist()
    {
        $this->assertTrue(class_exists('App\Events\Employee\EmployeeWasArchived'));
        $this->assertTrue(class_exists('App\Events\Employee\EmployeeWasRestored'));
        $this->assertTrue(class_exists('App\Events\Employee\EmployeeWasDeleted'));
    }
}