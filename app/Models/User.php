<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Los atributos que pueden ser asignables para la creacion de usuarios.
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
     * Los atributos que se deben ocultar para la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que se deben convertir.
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
        'email' => 'required|string|lowercase|email|max:255|unique:' . User::class.'|confirmed',
    ];
    /**
     * Variable que valida el correo cuando se va a editar
     */
    public static $validarCorreoEdit = [
        'email' => 'required|string|lowercase|email|max:255|confirmed',
    ];

    /**
     * Metodo estatico que permite validar el dominio de la institución
     * 
     * @param $email El parametro necesario para ejecutar el metodo es el email que se validará
     * 
     * @throws Illuminate\Validation\ValidationException Se arrojará un mensaje de validación si se detecta
     */
    public static function validarDominioCorreo($email){
        if (strpos($email, '@itoaxaca.edu.mx') == false && strpos($email, '@oaxaca.tecnm.mx') == false) {
            throw ValidationException::withMessages([
                'email' => 'El dominio debe ser de la institución (@itoaxaca.edu.mx o @oaxaca.tecnm.mx)',
            ]);
        }
    }
    /**
     * Metodo estatico que permite validar que el correo electrónico sea único exceptuando al ya registrado
     * al del usuario a editar
     * 
     * @param $email El parámetro necesario para ejecutar el metodo es el email que se validará
     * 
     * @throws Illuminate\Validation\ValidationException Se arrojará un mensaje de validación si se detecta
     */
    public static function validarCorreo_UnicoYNoRepetido($email, $IdUsuario) {
        $existingUser = User::where('email', $email)
                        ->where('id', '!=', $IdUsuario)
                        ->first();
        if ($existingUser){
            throw ValidationException::withMessages([
                'email' => 'El campo correo electrónico ya ha sido tomado'
            ]);
        }
    }
    /**
     * Metodo estatico que retorna un arreglo que contiene las validaciones para cada campo de la contraseña
     * Tambien contiene la regla de la contraseña en la que se necesita ingresar minúsculas y mayúsculas, el
     * uso de letras, números y símbolos para que la validacion sea exitosa
     * 
     * @return array Retorna el array con las validaciones pertinentes
     */
    public static function getValidacionesActualizarContrasenia(){
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required',  Password::min(8)->mixedCase()->letters()->numbers()->symbols(), 'confirmed'],
        ];
    }
    /**
     * Metodo estatico que retorna un arreglo que contiene las validaciones para cada campo de la contraseña
     * Tambien contiene la regla de la contraseña en la que se necesita ingresar minúsculas y mayúsculas, el
     * uso de letras, números y símbolos para que la validacion sea exitosa. Tambien regresa las validaciones
     * para la actualizacion del personal
     * 
     * @return array Retorna el array con las validaciones pertinentes
     */
    public static function getValidacionesNuevoUsuarioSinContrasenia(){
        return [
            'password' => ['required',  Password::min(8)->mixedCase()->letters()->numbers()->symbols(), 'confirmed'],
            'Departamento' => 'required',
            'Sexo' => 'required',
        ];
    }
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
