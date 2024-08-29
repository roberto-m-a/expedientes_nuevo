<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class document extends Model
{
    use HasFactory;
    protected $table = 'documento';
    protected $primaryKey = 'IdDocumento';
    protected $fillable = [
        'Titulo',
        'fechaExpedicion',
        'fechaEntrega',
        'Estatus',
        'region',
        'IdTipoDocumento',
        'IdPeriodoEscolar',
        'IdExpediente',
        'IdDepartamento',
        'IdUsuario',
        'URL',
        'Dependencia',
    ];
    /**
     * Obten el usuario asociado a un documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'IdUsuario');
    }
    /**
     * Obten el expediente asociado a un documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function expediente(): HasOne
    {
        return $this->hasOne(expediente::class,'IdExpediente','IdExpediente');
    }
    /**
     * Obten el periodo escolar asociado a un documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function periodoEscolar(): HasOne
    {
        return $this->hasOne(PeriodoEscolar::class, 'IdPeriodoEscolar', 'IdPeriodoEscolar');
    }

    /**
     * Obten el tipo de documento asociado a un documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tipoDocumento(): HasOne
    {
        return $this->hasOne(TipoDocumento::class, 'IdTipoDocumento', 'IdTipoDocumento');
    }

    /**
     * Obten el departamento asociado a un documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function departamento(): HasOne
    {
        return $this->hasOne(Departamento::class, 'IdDepartamento', 'IdDepartamento');
    }
}
