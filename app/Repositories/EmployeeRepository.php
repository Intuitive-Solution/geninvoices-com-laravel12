<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Utils\Ninja;

/**
 * EmployeeRepository.
 */
class EmployeeRepository extends BaseRepository
{
    public function save(array $data, Employee $employee): ?Employee
    {
        $employee->fill($data);
        $employee->save();

        return $employee;
    }

    /**
     * Archive an employee by setting archived_at timestamp.
     *
     * @param Employee $employee
     * @return void
     */
    public function archive($employee)
    {
        if ($employee->archived_at) {
            return;
        }

        $employee->archived_at = now();
        $employee->save();

        // Fire archived event if exists
        $className = $this->getEventClass($employee, 'Archived');
        if (class_exists($className)) {
            event(new $className($employee, $employee->company, Ninja::eventVars(auth()->guard('api')->user() ? auth()->guard('api')->user()->id : null)));
        }
    }

    /**
     * Restore an employee by clearing archived_at and is_deleted flags.
     *
     * @param Employee $employee
     * @return void
     */
    public function restore($employee)
    {
        $fromDeleted = false;

        if ($employee->is_deleted) {
            $fromDeleted = true;
            $employee->is_deleted = false;
        }

        $employee->archived_at = null;
        $employee->save();

        if ($employee->trashed()) {
            $employee->restore();
        }

        // Fire restored event if exists
        $className = $this->getEventClass($employee, 'Restored');
        if (class_exists($className)) {
            event(new $className($employee, $fromDeleted, $employee->company, Ninja::eventVars(auth()->guard('api')->user() ? auth()->guard('api')->user()->id : null)));
        }
    }

    /**
     * Delete an employee by setting is_deleted flag and soft deleting.
     *
     * @param Employee $employee
     * @return void
     */
    public function delete($employee)
    {
        if ($employee->is_deleted) {
            return;
        }

        $employee->is_deleted = true;
        $employee->save();
        $employee->delete(); // Soft delete

        // Fire deleted event if exists
        $className = $this->getEventClass($employee, 'Deleted');
        if (class_exists($className)) {
            event(new $className($employee, $employee->company, Ninja::eventVars(auth()->guard('api')->user() ? auth()->guard('api')->user()->id : null)));
        }
    }
}