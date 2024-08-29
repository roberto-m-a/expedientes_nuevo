<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodoEscolar extends Model
{
    use HasFactory;
    protected $table = 'periodo_escolar';
    protected $primaryKey = 'IdPeriodoEscolar';
    protected $fillable = [
        'fechaInicio',
        'fechaTermino',
        'nombre_corto',
    ];
    /** 
     * Variable que valida la agregación o edición de un período escolar 
     */
    public static $validacionPeriodoEscolar = [
        'fechaInicio' => 'required|string|max:50',
        'fechaTermino' => 'required|string|after:fechaInicio|max:50',
        'nombre_corto' => 'required|string|max:50|unique:' . PeriodoEscolar::class,
    ];
    /**
     * Obten todos los documentos de un periodo escolar
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documento(): HasMany
    {
        return $this->hasMany(document::class, 'IdPeriodoEscolar', 'IdPeriodoEscolar');
    }
}
