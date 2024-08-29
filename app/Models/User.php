<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'IdPersonal',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    /**
     * Variable que valida el correo
     */
    public static $validarCorreo =[
        'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
        'email_confirmation' => 'required|string|lowercase|equal:email|email|max:255'
    ];
    /**
     * Obten el personal perteneciente al usuario
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function personal(): BelongsTo{
        return $this->belongsTo(Personal::class,'IdPersonal','IdPersonal');
    }

    /**
     * Obten todos los documentos asociados a un usuario
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documento(): HasMany
    {
        return $this->hasMany(document::class, 'IdUsuario', 'id');
    }
}
