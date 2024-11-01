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
     * Los atributos que se deben ocultar para la serialización.
     *
     * @var array< date, date>
     */
    protected $hidden = [
        'updated_at',
        'created_at',
    ];
    /** 
     * Variable que valida la agregación o edición de un período escolar 
     */
    public static $validacionPeriodoEscolar = [
        'fechaInicio' => 'required|string|max:20',
        'fechaTermino' => 'required|string|after:fechaInicio|max:20',
        'nombre_corto' => 'required|string|max:20|unique:' . PeriodoEscolar::class,
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
