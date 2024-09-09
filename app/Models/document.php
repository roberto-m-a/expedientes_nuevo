<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
     * Los atributos que se deben ocultar para la serialización.
     *
     * @var array< date, date>
     */
    protected $hidden = [
        'updated_at',
        'created_at',
    ];
    /**
     * Validar documento
     * 
     * Método estático que valida los campos de un documento a subir o editar
     * 
     * @param Illuminate\Http\Request Petición HTTP con los datos del documento, 
     * @param Boolean $requiredDoc Variable que decide si se valida o no el campo 'Archivo'
     * 
     * @throws Illuminate\Validation\ValidationException Arroja las excepciones con los errores detectados en las validaciones
     */
    public static function validarDocumento(Request $request, $requiredDoc){
        $request->validate([
            'Expediente' => 'required',
            'TipoDocumento' => 'required',
            'Titulo' => 'required|string|max:255',
            'FechaExpedicion' => 'required',
            'Region' => 'required|string|max:12',
            'PeriodoEscolar' => 'required',
        ]);
        if($requiredDoc){
            $request->validate(['Archivo' => 'required|file|max:5120']);
        }
        if ($request->Archivo != '') {
            if (strpos($request->Archivo->getClientOriginalName(), '.pdf') == false) {
                throw ValidationException::withMessages([
                    'Archivo' => 'Debes de ingresar un archivo PDF',
                ]);
            }
        }
        if ($request->Region == 'Interno') {
            $request->validate([
                'Departamento' => 'required',
                'Estatus' => 'required|',
            ]);
        } else {
            $request->validate([
                'Dependencia' => 'required|string|max:255',
            ]);
        }
        if ($request->Estatus == 'Entregado') {
            $request->validate([
                'FechaEntrega' => 'required',
            ]);
            if ($request->FechaExpedicion > $request->FechaEntrega) {
                throw ValidationException::withMessages([
                    'FechaExpedicion' => 'Las fecha de expedición no puede ser despues de la fecha de entrega',
                    'FechaEntrega' => 'Las fecha de entrega no puede ser antes de la fecha de expedición',
                ]);
            }
        }
    }
    /**
     * Validar entrega de documento
     * 
     * Método estático que valida los campos de un documento a entregar
     * 
     * @param Illuminate\Http\Request Petición HTTP con los datos del documento
     * 
     * @throws Illuminate\Validation\ValidationException Arroja las excepciones con los errores detectados en las validaciones
     */
    public static function ValidarEntregaDocumento(Request $request){
        $request->validate([
            'FechaEntrega' => 'required',
        ]);
        if ($request->FechaExpedicion > $request->FechaEntrega) {
            throw ValidationException::withMessages([
                'FechaEntrega' => 'Las fecha de entrega no puede ser antes de la fecha de expedición',
            ]);
        }
        if ($request->Archivo != '') {
            if (strpos($request->Archivo->getClientOriginalName(), '.pdf') == false) {
                throw ValidationException::withMessages([
                    'Archivo' => 'Debes de ingresar un archivo PDF',
                ]);
            }
        }
    }
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
