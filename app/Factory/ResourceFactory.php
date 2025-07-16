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

namespace App\Factory;

use App\Models\Resource;

class ResourceFactory
{
    public static function create(int $company_id, int $user_id): Resource
    {
        $resource = new Resource();
        $resource->company_id = $company_id;
        $resource->user_id = $user_id;
        $resource->name = '';
        $resource->description = '';
        $resource->rate_per_hour = 0;
        $resource->rate_per_day = 0;
        $resource->rate_per_week = 0;
        $resource->rate_per_month = 0;
        $resource->custom_value1 = '';
        $resource->custom_value2 = '';
        $resource->custom_value3 = '';
        $resource->custom_value4 = '';
        $resource->is_deleted = false;

        return $resource;
    }
} 