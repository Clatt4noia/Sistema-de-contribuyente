<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    public const ROLE_ADMIN = UserRole::ADMIN->value;
    public const ROLE_LOGISTICS_MANAGER = UserRole::LOGISTICS_MANAGER->value;
    public const ROLE_FLEET_MANAGER = UserRole::FLEET_MANAGER->value;
    public const ROLE_FINANCE_MANAGER = UserRole::FINANCE_MANAGER->value;
    public const ROLE_FINANCE_ANALYST = UserRole::FINANCE_ANALYST->value;
    public const ROLE_CLIENT = UserRole::CLIENT->value;

    /** @var array<int, string> */
    public const LOGISTICS_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_LOGISTICS_MANAGER,
        self::ROLE_FLEET_MANAGER,
    ];

    /** @var array<int, string> */
    public const FLEET_MANAGEMENT_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_FLEET_MANAGER,
    ];

    /** @var array<int, string> */
    public const FINANCE_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_FINANCE_MANAGER,
        self::ROLE_FINANCE_ANALYST,
    ];

    /** @var array<int, string> */
    public const BILLING_ROLES = self::FINANCE_ROLES;

    /** @var array<int, string> */
    public const CLIENT_PORTAL_ROLES = [
        self::ROLE_CLIENT,
    ];

    /** @var array<int, string> */
    public const REGISTRATION_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_LOGISTICS_MANAGER,
        self::ROLE_FINANCE_MANAGER,
    ];

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function hasRole(UserRole|string $role): bool
    {
        if (! $this->role instanceof UserRole) {
            return false;
        }

        try {
            $role = $role instanceof UserRole ? $role : UserRole::from($role);
        } catch (\ValueError) {
            return false;
        }

        return $this->role === $role;
    }

    /**
     * Determine if the user has any of the provided roles.
     *
     * @param  string|array<int, string>  $roles
     */
    public function hasAnyRole(UserRole|string|array $roles): bool
    {
        $roles = Collection::wrap($roles)
            ->map(function (string|UserRole $role): ?UserRole {
                try {
                    return $role instanceof UserRole ? $role : UserRole::from($role);
                } catch (\ValueError) {
                    return null;
                }
            })
            ->filter()
            ->all();

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::ADMIN);
    }

    /**
     * Obtener las opciones de rol disponibles para formularios de selección.
     *
     * @param  array<int, UserRole>|null  $roles
     * @return array<string, string>
     */
    public static function roleOptions(?array $roles = null): array
    {
        $roles ??= UserRole::cases();

        return collect($roles)
            ->mapWithKeys(fn (UserRole $role) => [$role->value => $role->label()])
            ->all();
    }
}
