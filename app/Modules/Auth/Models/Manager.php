<?php

namespace App\Modules\Auth\Models;

use App\Modules\Auth\database\factories\ManagerFactory;
use App\Modules\Auth\Enums\RoleName;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Base\Concerns\HasDeviceTokens;
use App\Modules\Base\Concerns\HasImages;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Contracts\Team;
use Laratrust\Traits\HasRolesAndPermissions;

/**
 * @property int $id
 * @property int|null $country_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $whatsapp
 * @property string $password
 * @property string|null $avatar
 * @property UserStatus $status
 * @property Carbon|null $last_login_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $image_url
 * @property-read Collection<int, Role> $roles
 * @property-read Country|null $country
 */
class Manager extends Authenticatable implements LaratrustUser
{
    /** @use HasFactory<ManagerFactory> */
    use HasDeviceTokens, HasFactory, HasImages, Notifiable;

    use HasRolesAndPermissions {
        hasPermission as laratrustHasPermission;
    }

    /** @var string */
    protected $guard = 'manager';

    protected $fillable = [
        'country_id',
        'name',
        'email',
        'phone',
        'whatsapp',
        'password',
        'avatar',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => UserStatus::class,
            'last_login_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ManagerFactory
    {
        return ManagerFactory::new();
    }

    /**
     * @return MorphToMany<Role, $this>
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            Role::class,
            'model',
            'model_has_roles',
            'model_id',
            'role_id',
        );
    }

    /** @return MorphToMany<Permission, $this> */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(
            Permission::class,
            'model',
            'model_has_permissions',
            'model_id',
            'permission_id',
        );
    }

    /** @return BelongsTo<Country, $this> */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleName::Admin->value)
            || $this->hasRole(RoleName::SuperAdmin->value);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RoleName::SuperAdmin->value);
    }

    /**
     * The highest authority rank across this manager's roles (0 when roleless).
     */
    public function highestRank(): int
    {
        return (int) $this->roles->max(fn (Role $role): int => RoleName::rankFor($role->name));
    }

    /**
     * Whether this manager may administer $target within the RBAC hierarchy:
     * a super admin may manage anyone; everyone else only managers strictly
     * below their own rank.
     */
    public function canManage(self $target): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->highestRank() > $target->highestRank();
    }

    /**
     * @param  string|array<int, string>|\BackedEnum  $permission
     */
    public function hasPermission(
        string|array|\BackedEnum $permission,
        mixed $team = null,
        bool $requireAll = false,
    ): bool {
        if ($this->isAdmin()) {
            return true;
        }

        if ($team instanceof Model && ! $team instanceof Team) {
            return $this->can($permission, $team);
        }

        return $this->laratrustHasPermission($permission, $team, $requireAll);
    }

    public function isActiveAccount(): bool
    {
        return $this->status === UserStatus::Active;
    }
}
