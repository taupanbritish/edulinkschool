<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_PETUGAS = 'petugas';

    public const ROLE_KEPALA_SEKOLAH = 'kepala_sekolah';

    public const ROLE_WAKASEK_KURIKULUM = 'wakasek_kurikulum';

    public const ROLE_GURU = 'guru';

    public const ROLE_SISWA = 'siswa';

    public const ROLE_ORANG_TUA = 'orang_tua';

    public const PANEL_ROLES = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_PETUGAS => 'Petugas Web',
        self::ROLE_KEPALA_SEKOLAH => 'Kepala Sekolah',
        self::ROLE_WAKASEK_KURIKULUM => 'Wakasek Kurikulum',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function guardian()
    {
        return $this->hasOne(Guardian::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPetugas(): bool
    {
        return $this->role === self::ROLE_PETUGAS;
    }

    public function isKepalaSekolah(): bool
    {
        return $this->role === self::ROLE_KEPALA_SEKOLAH;
    }

    public function isWakasekKurikulum(): bool
    {
        return $this->role === self::ROLE_WAKASEK_KURIKULUM;
    }

    public function isPanelUser(): bool
    {
        return array_key_exists($this->role, self::PANEL_ROLES);
    }

    public function canManage(): bool
    {
        return $this->isAdmin() || $this->isPetugas();
    }

    public function isReadOnly(): bool
    {
        return $this->isKepalaSekolah();
    }

    public function isGuru(): bool
    {
        return $this->role === self::ROLE_GURU;
    }

    public function isSiswa(): bool
    {
        return $this->role === self::ROLE_SISWA;
    }

    public function isOrangTua(): bool
    {
        return $this->role === self::ROLE_ORANG_TUA;
    }

    public function roleLabel(): string
    {
        return self::PANEL_ROLES[$this->role] ?? match ($this->role) {
            self::ROLE_GURU => 'Guru',
            self::ROLE_SISWA => 'Siswa',
            self::ROLE_ORANG_TUA => 'Orang Tua',
            default => ucfirst($this->role),
        };
    }
}
