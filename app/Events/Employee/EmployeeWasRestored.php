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

namespace App\Events\Employee;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Queue\SerializesModels;

/**
 * Class EmployeeWasRestored.
 */
class EmployeeWasRestored
{
    use SerializesModels;

    /**
     * @var Employee
     */
    public $employee;

    public $company;

    public $event_vars;

    public $fromDeleted;

    /**
     * Create a new event instance.
     *
     * @param Employee $employee
     * @param bool $fromDeleted
     * @param Company $company
     * @param array $event_vars
     */
    public function __construct(Employee $employee, $fromDeleted, Company $company, array $event_vars)
    {
        $this->employee = $employee;
        $this->fromDeleted = $fromDeleted;
        $this->company = $company;
        $this->event_vars = $event_vars;
    }
}