<?php

namespace App\Filters;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

/**
 * EmployeeFilters.
 */
class EmployeeFilters extends QueryFilters
{
    /**
     * Filter by name.
     *
     * @param string $name
     * @return Builder
     */
    public function name(string $name = ''): Builder
    {
        if (strlen($name) == 0) {
            return $this->builder;
        }

        return $this->builder->where('name', 'like', '%'.$name.'%');
    }

    /**
     * Filter by employee ID.
     *
     * @param string $emp_id
     * @return Builder
     */
    public function emp_id(string $emp_id = ''): Builder
    {
        if (strlen($emp_id) == 0) {
            return $this->builder;
        }

        return $this->builder->where('emp_id', 'like', '%'.$emp_id.'%');
    }

    /**
     * Filter by department.
     *
     * @param string $department
     * @return Builder
     */
    public function department(string $department = ''): Builder
    {
        if (strlen($department) == 0) {
            return $this->builder;
        }

        return $this->builder->where('department', 'like', '%'.$department.'%');
    }

    /**
     * Filter by designation.
     *
     * @param string $designation
     * @return Builder
     */
    public function designation(string $designation = ''): Builder
    {
        if (strlen($designation) == 0) {
            return $this->builder;
        }

        return $this->builder->where('designation', 'like', '%'.$designation.'%');
    }

    /**
     * Filters the list based on the status
     * archived, active, deleted.
     * Overrides parent method to work with Employee model structure.
     *
     * @param string $filter
     * @return Builder
     */
    public function status(string $filter = ''): Builder
    {
        if (strlen($filter) == 0) {
            return $this->builder;
        }

        $filters = explode(',', $filter);

        return $this->builder->where(function ($query) use ($filters) {
            if (in_array(self::STATUS_ACTIVE, $filters)) {
                $query = $query->orWhere(function ($q) {
                    $q->whereNull('deleted_at')->where('is_deleted', 0);
                });
            }

            if (in_array(self::STATUS_ARCHIVED, $filters)) {
                $query = $query->orWhere(function ($q) {
                    $q->whereNotNull('deleted_at')->where('is_deleted', 0);
                });
            }

            if (in_array(self::STATUS_DELETED, $filters)) {
                $query = $query->orWhere('is_deleted', 1);
            }
        });
    }

    /**
     * Filter by email.
     *
     * @param string $email
     * @return Builder
     */
    public function email(string $email = ''): Builder
    {
        if (strlen($email) == 0) {
            return $this->builder;
        }

        return $this->builder->where('email', 'like', '%'.$email.'%');
    }

    /**
     * Sorts the list based on $sort.
     *
     * @param string $sort formatted as column|asc
     * @return Builder
     */
    public function sort(string $sort = ''): Builder
    {
        $sort_col = explode('|', $sort);

        if (!is_array($sort_col) || count($sort_col) != 2) {
            return $this->builder;
        }

        $sort_dir = ($sort_col[1] == 'asc') ? 'asc' : 'desc';

        if ($sort_col[0] == 'name') {
            return $this->builder->orderBy('name', $sort_dir);
        }

        return $this->builder->orderBy($sort_col[0], $sort_dir);
    }

    /**
     * Filters the query by the users company ID.
     *
     * @return Builder
     */
    public function entityFilter(): Builder
    {
        return $this->builder->company();
    }
}