<?php

namespace App\Models\Presenters;

/**
 * Class EmployeePresenter.
 */
class EmployeePresenter extends EntityPresenter
{
    /**
     * @return string
     */
    public function name()
    {
        return $this->entity->name ?: '';
    }

    public function status()
    {
        return ucfirst($this->entity->status);
    }

    public function statusBadge()
    {
        $status = $this->entity->status;
        $class = $status === 'active' ? 'success' : 'secondary';
        
        return "<span class=\"badge badge-{$class}\">" . ucfirst($status) . "</span>";
    }
}