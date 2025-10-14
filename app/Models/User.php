<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Course> $courses
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany courses()
 * @mixin IdeHelperUser
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany courses()
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Toplu atamaya izin verilen alanlar.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Gizlenecek alanlar.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Alan tipleri.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Kullanıcının kayıtlı olduğu kurslar.
     *
     * Pivot tablo: course_user
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_user', 'user_id', 'course_id')
                    ->withTimestamps();
    }

    /**
     *  Kullanıcının rol kontrolü (Admin / Student)
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
    
    public function sendPasswordResetNotification($token)
    {
        $url = url(route('password.reset', ['token' => $token], false))
             . '?email=' . urlencode($this->getEmailForPasswordReset());
        if (config('app.expose_reset_link')) {
            request()->session()->flash('demo_reset_link', $url);
        }
        $this->notify(new ResetPassword($token));
    }
    
}
