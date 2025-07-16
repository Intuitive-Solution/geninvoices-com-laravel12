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

namespace App\Events\Resource;

use App\Models\Company;
use App\Models\Resource;
use Illuminate\Queue\SerializesModels;

/**
 * Class ResourceWasCreated.
 */
class ResourceWasCreated
{
    use SerializesModels;

    /**
     * @var Resource
     */
    public $resource;

    /**
     * @var Company
     */
    public $company;

    public $event_vars;

    /**
     * Create a new event instance.
     *
     * @param Resource $resource
     * @param Company $company
     * @param array $event_vars
     */
    public function __construct(Resource $resource, Company $company, array $event_vars)
    {
        $this->resource = $resource;
        $this->company = $company;
        $this->event_vars = $event_vars;
    }
} 