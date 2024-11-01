<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;

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
     * Los atributos que se deben ocultar para la serialización.
     *
     * @var array< date, date>
     */
    protected $hidden = [
        'updated_at',
        'created_at',
    ];
    /**
     * Variable que valida la agregacón o edición de un docente
     */
    public static $validarDocente = [
        'GradoAcademico' => 'required|not_in:undefined',
    ];
    /**
     * Funcion estatica para validar la creacion de un docente
     */
    public static function validarDocente(Request $request){
        $request->validate(['GradoAcademico' => 'required|not_in:undefined'], ['GradoAcademico.not_in' => 'Seleccione un grado académico']);
    }
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
