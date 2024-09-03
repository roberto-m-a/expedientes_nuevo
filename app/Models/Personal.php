<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Personal extends Model
{   
    protected $table = 'personal';
    protected $primaryKey = 'IdPersonal';
    use HasFactory;
    /**
     * Los atributos que pueden ser asignables para la creacion de personal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Nombre',
        'Apellidos',
        'IdDepartamento',
        'Sexo',
    ];
    /**
     * Variable que valida la agregación de un nuevo personal al sistema mediante la vista del registro
     */
    public static $validarPersonalRegistro =[
        'name' => 'required|string|regex:/^[\pL\s]+$/u|max:20',
        'lastname' => 'required|string|max:40',
    ];
    /**
     * Variable que valida la agregación o edición de un personal
     */
    public static $validarPersonal = [
        'Nombre' => 'required|string|regex:/^[\pL\s]+$/u|max:20',
        'Apellidos' => 'required|string|regex:/^[\pL\s]+$/u|max:40',
        'Sexo' => 'required|string',
        'Departamento' => 'required',
    ];

    /**
     * Obten el usuario perteneciente al personal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo{
        return $this->belongsTo(User::class, 'IdPersonal','IdPersonal');
    }
    /**
     * Obten el departamento perteneciente al personal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function departamento():BelongsTo{
        return $this->belongsTo(Departamento::class,'IdDepartamento', 'IdDepartamento');
    }
    /**
     * Obten el docente perteneciente al personal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'IdPersonal', 'IdPersonal');
    }
    /**
     * Obten la secretaria perteneciente al personal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class, 'IdPersonal', 'IdPersonal');
    }
    /**
     * Obten el administrador perteneciente al personal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function administrador(): BelongsTo
    {
        return $this->belongsTo(Administrador::class, 'IdPersonal', 'IdPersonal');
    }
}
