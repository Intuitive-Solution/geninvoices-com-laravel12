<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

/**
 * Class EmployeePolicy.
 */
class EmployeePolicy extends EntityPolicy
{
    /**
     * Checks if the user has permission to view any employees.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('view_employee') || $user->hasPermission('view_all');
    }

    /**
     * Checks if the user has permission to view the employee.
     *
     * @param User $user
     * @param $entity
     * @return bool
     */
    public function view(User $user, $entity): bool
    {
        return ($user->isAdmin() && $entity->company_id == $user->companyId())
            || ($user->hasPermission('view_employee') && $entity->company_id == $user->companyId())
            || ($user->hasPermission('view_all') && $entity->company_id == $user->companyId())
            || ($user->owns($entity) && $entity->company_id == $user->companyId());
    }

    /**
     * Checks if the user has permission to create employees.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('create_employee') || $user->hasPermission('create_all');
    }

    /**
     * Checks if the user has permission to edit the employee.
     *
     * @param User $user
     * @param $entity
     * @return bool
     */
    public function edit(User $user, $entity): bool
    {
        return ($user->isAdmin() && $entity->company_id == $user->companyId())
            || ($user->hasPermission('edit_employee') && $entity->company_id == $user->companyId())
            || ($user->hasPermission('edit_all') && $entity->company_id == $user->companyId())
            || ($user->owns($entity) && $entity->company_id == $user->companyId());
    }
}