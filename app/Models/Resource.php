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

namespace App\Models;

use App\Utils\Traits\MakesHash;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Resource
 *
 * @property int $id
 * @property int $company_id
 * @property int $user_id
 * @property int|null $assigned_user_id
 * @property string $name
 * @property string|null $description
 * @property float $rate_per_hour
 * @property float $rate_per_day
 * @property float $rate_per_week
 * @property float $rate_per_month
 * @property string|null $custom_value1
 * @property string|null $custom_value2
 * @property string|null $custom_value3
 * @property string|null $custom_value4
 * @property bool $is_deleted
 * @property int|null $deleted_at
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property-read \App\Models\User|null $assigned_user
 * @property-read \App\Models\Company $company
 * @property-read int|null $documents_count
 * @property-read mixed $hashed_id
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @mixin \Eloquent
 */
class Resource extends BaseModel
{
    use MakesHash;
    use SoftDeletes;
    use Filterable;

    protected $fillable = [
        'name',
        'description',
        'rate_per_month',
        'rate_per_hour',
        'rate_per_day',
        'rate_per_week',
        'custom_value1',
        'custom_value2',
        'custom_value3',
        'custom_value4',
    ];

    protected $casts = [
        'updated_at' => 'timestamp',
        'created_at' => 'timestamp',
        'deleted_at' => 'timestamp',
        'rate_per_month' => 'float',
        'rate_per_hour' => 'float',
        'rate_per_day' => 'float',
        'rate_per_week' => 'float',
        'is_deleted' => 'boolean',
    ];

    protected $touches = [];

    public function getEntityType()
    {
        return self::class;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function assigned_user()
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'id')->withTrashed();
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function translate_entity()
    {
        return ctrans('texts.resource');
    }

    public function portalUrl($use_react_url): string
    {
        return $this->company->domain() . '/portal/resources/' . $this->hashed_id;
    }
} 