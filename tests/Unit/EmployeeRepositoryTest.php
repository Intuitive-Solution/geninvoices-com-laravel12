<?php

namespace Tests\Unit;

use App\Repositories\EmployeeRepository;
use Tests\TestCase;

class EmployeeRepositoryTest extends TestCase
{
    private EmployeeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = new EmployeeRepository();
    }

    /**
     * Test that EmployeeRepository has the required action methods.
     */
    public function testRepositoryMethodsExist()
    {
        $this->assertTrue(
            method_exists($this->repository, 'archive'),
            'EmployeeRepository should have archive method'
        );

        $this->assertTrue(
            method_exists($this->repository, 'restore'),
            'EmployeeRepository should have restore method'
        );

        $this->assertTrue(
            method_exists($this->repository, 'delete'),
            'EmployeeRepository should have delete method'
        );
    }

    /**
     * Test that EmployeeRepository extends BaseRepository.
     */
    public function testRepositoryExtendsBaseRepository()
    {
        $this->assertInstanceOf(
            \App\Repositories\BaseRepository::class,
            $this->repository,
            'EmployeeRepository should extend BaseRepository'
        );
    }

    /**
     * Test that EmployeeRepository has the save method.
     */
    public function testRepositoryHasSaveMethod()
    {
        $this->assertTrue(
            method_exists($this->repository, 'save'),
            'EmployeeRepository should have save method'
        );
    }

    /**
     * Test that archive method signature is correct.
     */
    public function testArchiveMethodSignature()
    {
        $reflection = new \ReflectionMethod($this->repository, 'archive');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(1, $parameters, 'Archive method should have 1 parameter');
        $this->assertEquals('employee', $parameters[0]->getName(), 'Archive method parameter should be named employee');
    }

    /**
     * Test that restore method signature is correct.
     */
    public function testRestoreMethodSignature()
    {
        $reflection = new \ReflectionMethod($this->repository, 'restore');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(1, $parameters, 'Restore method should have 1 parameter');
        $this->assertEquals('employee', $parameters[0]->getName(), 'Restore method parameter should be named employee');
    }

    /**
     * Test that delete method signature is correct.
     */
    public function testDeleteMethodSignature()
    {
        $reflection = new \ReflectionMethod($this->repository, 'delete');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(1, $parameters, 'Delete method should have 1 parameter');
        $this->assertEquals('employee', $parameters[0]->getName(), 'Delete method parameter should be named employee');
    }

    /**
     * Test that the repository can be instantiated.
     */
    public function testRepositoryCanBeInstantiated()
    {
        $repository = new EmployeeRepository();
        $this->assertInstanceOf(EmployeeRepository::class, $repository);
    }
}