<?php

namespace App\Models;

use App\Models\Presenters\EmployeePresenter;
use App\Utils\Traits\MakesHash;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Presenter\PresentableTrait;

/**
 * App\Models\Employee
 *
 * @property int $id
 * @property int $company_id
 * @property int $user_id
 * @property string $name
 * @property string $emp_id
 * @property string $department
 * @property string $designation
 * @property string $email
 * @property bool $is_deleted
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read mixed $hashed_id
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\User $user
 * @property-read \App\Models\User|null $assigned_user
 */
class Employee extends BaseModel
{
    use MakesHash;
    use PresentableTrait;
    use SoftDeletes;
    use Filterable;

    protected $presenter = EmployeePresenter::class;

    protected $fillable = [
        'name',
        'emp_id',
        'department',
        'designation',
        'email',
        'archived_at',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
        'archived_at' => 'timestamp',
    ];

    protected $touches = [];

    public function getEntityType()
    {
        return self::class;
    }

    public function getHashedIdAttribute()
    {
        return $this->encodePrimaryKey($this->id);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the employee is archived.
     * An employee is archived when archived_at is set and is_deleted is false.
     *
     * @return bool
     */
    public function isArchived(): bool
    {
        return !is_null($this->archived_at) && !$this->is_deleted;
    }

    /**
     * Check if the employee is active.
     * An employee is active when archived_at is null and is_deleted is false.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return is_null($this->archived_at) && !$this->is_deleted;
    }

    /**
     * Check if the employee is deleted.
     * An employee is deleted when is_deleted is true.
     *
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->is_deleted;
    }
}