<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Docente extends Model
{   
    protected $table = 'docente';
    protected $primaryKey = 'IdDocente';
    use HasFactory;
    protected $fillable = [
        'GradoAcademico',
        'IdPersonal',
    ];
    /**
     * Variable que valida la agregacón o edición de un docente
     */
    public static $validarDocente = [
        'GradoAcademico' => 'required',
    ];
    /**
     * Obten el personal asociado a un docente
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function personal(): HasOne{
        return $this->hasOne(Personal::class,'IdPersonal','IdPersonal');
    }
    /**
     * Obten el expediente asociado a un docente
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function expediente(): HasOne
    {
        return $this->hasOne(expediente::class, 'IdDocente', 'IdDocente');
    }
}
