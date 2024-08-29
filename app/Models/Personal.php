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
    protected $fillable = [
        'Nombre',
        'Apellidos',
        'IdDepartamento',
        'Sexo',
    ];
    /**
     * Variable que valida la agregación o edición de un personal
     */
    public static $validarPersonal = [
        'Nombre' => 'required|string|max:50',
        'Apellidos' => 'required|string|max:100',
        'Sexo' => 'required',
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
}
