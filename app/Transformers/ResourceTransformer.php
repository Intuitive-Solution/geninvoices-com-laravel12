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

namespace App\Transformers;

use App\Models\Document;
use App\Models\Resource;
use App\Utils\Traits\MakesHash;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class ResourceTransformer.
 */
class ResourceTransformer extends EntityTransformer
{
    use MakesHash;
    use SoftDeletes;

    protected array $defaultIncludes = [
    ];

    /**
     * @var array
     */
    protected array $availableIncludes = [
        'company',
        'user',
        'assigned_user',
    ];

    public function includeCompany(Resource $resource): ?\League\Fractal\Resource\Item
    {
        $transformer = new CompanyTransformer($this->serializer);

        if (!$resource->company) {
            return null;
        }

        return $this->includeItem($resource->company, $transformer, \App\Models\Company::class);
    }

    public function includeUser(Resource $resource): ?\League\Fractal\Resource\Item
    {
        $transformer = new UserTransformer($this->serializer);

        if (!$resource->user) {
            return null;
        }

        return $this->includeItem($resource->user, $transformer, \App\Models\User::class);
    }

    public function includeAssignedUser(Resource $resource): ?\League\Fractal\Resource\Item
    {
        $transformer = new UserTransformer($this->serializer);

        if (!$resource->assigned_user) {
            return null;
        }

        return $this->includeItem($resource->assigned_user, $transformer, \App\Models\User::class);
    }

    /**
     * @param Resource $resource
     *
     * @return array
     */
    public function transform(Resource $resource)
    {
        return [
            'id' => $this->encodePrimaryKey($resource->id),
            'user_id' => $this->encodePrimaryKey($resource->user_id),
            'assigned_user_id' => $this->encodePrimaryKey($resource->assigned_user_id),
            'company_id' => $this->encodePrimaryKey($resource->company_id),
            'name' => $resource->name ?: '',
            'description' => $resource->description ?: '',
            'rate_per_hour' => (float) $resource->rate_per_hour ?: 0,
            'rate_per_day' => (float) $resource->rate_per_day ?: 0,
            'rate_per_week' => (float) $resource->rate_per_week ?: 0,
            'rate_per_month' => (float) $resource->rate_per_month ?: 0,
            'custom_value1' => $resource->custom_value1 ?: '',
            'custom_value2' => $resource->custom_value2 ?: '',
            'custom_value3' => $resource->custom_value3 ?: '',
            'custom_value4' => $resource->custom_value4 ?: '',
            'is_deleted' => (bool) $resource->is_deleted,
            'updated_at' => (int) $resource->updated_at,
            'archived_at' => (int) $resource->deleted_at,
            'created_at' => (int) $resource->created_at,
            'entity_type' => 'resource',
        ];
    }
} 