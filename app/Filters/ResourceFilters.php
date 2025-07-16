<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2024. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * ResourceFilters.
 */
class ResourceFilters extends QueryFilters
{
    /**
     * Filter based on search text.
     *
     * @param string $filter
     * @return Builder
     * @deprecated
     */
    public function filter(string $filter = ''): Builder
    {
        if (strlen($filter) == 0) {
            return $this->builder;
        }

        return  $this->builder->where(function ($query) use ($filter) {
            $query->where('name', 'like', '%'.$filter.'%')
                ->orWhere('description', 'like', '%'.$filter.'%')
                ->orWhere('rate', 'like', '%'.$filter.'%')
                ->orWhere('custom_value1', 'like', '%'.$filter.'%')
                ->orWhere('custom_value2', 'like', '%'.$filter.'%')
                ->orWhere('custom_value3', 'like', '%'.$filter.'%')
                ->orWhere('custom_value4', 'like', '%'.$filter.'%');
        });
    }

    public function name(string $name = ''): Builder
    {
        if (strlen($name) == 0) {
            return $this->builder;
        }

        return $this->builder->where('name', 'like', '%'.$name.'%');
    }

    public function rate(string $rate = ''): Builder
    {
        if (strlen($rate) == 0) {
            return $this->builder;
        }

        return $this->builder->where('rate', $rate);
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

        if ($sort_col[0] == 'rate') {
            return $this->builder->orderBy('rate', $sort_dir);
        }

        if ($sort_col[0] == 'created_at') {
            return $this->builder->orderBy('created_at', $sort_dir);
        }

        if ($sort_col[0] == 'updated_at') {
            return $this->builder->orderBy('updated_at', $sort_dir);
        }

        return $this->builder;
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