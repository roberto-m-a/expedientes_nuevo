<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\document;
use App\Models\expediente;
use App\Models\PeriodoEscolar;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class expedienteController extends Controller
{
    /**
     * Obtener la vista de expedientes o de los documentos de un docente
     * 
     * El método permite renderizar la vista correspondiente dependiendo del tipo de usuario
     * Al Docente le mostrará la vista de 'MiExpediente' el cual mostrará todos sus documentos
     * Al Administrador o Secretaria se le mostrará la vista 'expedientes' con el listado de todos los expedientes
     * 
     * @return Inertia\Inertia regresa la vista correspondiente y manda los datos necesarios para ser tratados por la vista
     */
    public function index()
    {
        $user = User::find(Auth::user()->id);
        $personal = $user->personal;
        if ($personal->docente !== null) {
            $docente = $personal->docente;
            $expediente = $docente->expediente;
            $tipo_documentos = TipoDocumento::all();
            $departamentos = Departamento::all();
            $periodos_escolares = PeriodoEscolar::all()->map(function ($periodo) {
                $periodo->generalInfo = $periodo->nombre_corto . ' (' . $periodo->fechaInicio . '-' . $periodo->fechaTermino . ')';
                return $periodo;
            });
            $documentosDocente = $this->obtenerDocumentosExpedienteEspecífico($expediente->IdExpediente, $personal, $user);
            return Inertia::render('Dashboard_miExpediente', ['user' => $user, 'personal' => $personal, 'documentosDocente' => $documentosDocente, 'expediente' => $expediente, 'periodos_escolares',  'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodos_escolares]);
        } else {
            $expedientes = expediente::join('docente', 'docente.IdDocente', '=', 'expediente.IdDocente')
                ->join('personal', 'personal.IdPersonal', '=', 'docente.IdPersonal')
                ->join('departamento', 'departamento.IdDepartamento', '=', 'personal.IdDepartamento')
                ->select('personal.Nombre', 'personal.Apellidos', 'expediente.IdExpediente', 'expediente.numDocumentos', 'departamento.nombreDepartamento')
                ->get();
            $data = ['user' => $user, 'personal' => $personal, 'expedientes' => $expedientes];
            if ($personal->administrador !== null) {
                return Inertia::render('Dashboard_admin_expedientes', $data);
            }
            if ($personal->secretaria !== null) {
                return Inertia::render('Dashboard_secre_expedientes', $data);
            }
        }
    }
    /**
     * Obten el expediente específico
     * 
     * El método permite acceder a un expediente en específico y retornar todos los valores necesarios para manejarlos
     * También retorna todos los documentos relacionados al expediente específico
     * 
     * @param var $idExpediente Se necesita del id del expediente a acceder
     * 
     * @return Inertia\Inertia Renderiza la vista con los datos obtenidos
     */
    public function expedienteEspecifico($idExpediente)
    {
        $user = User::find(Auth::user()->id);
        $personal = $user->personal;
        if ($personal->docente != null)
            return Redirect::route('dashboard');
        $expediente = expediente::find($idExpediente);
        $docente = $expediente->docente;
        $expedientes = expediente::join('docente', 'docente.IdDocente', '=', 'expediente.IdDocente')
            ->join('personal', 'personal.IdPersonal', '=', 'docente.IdPersonal')
            ->join('departamento', 'departamento.IdDepartamento', '=', 'personal.IdDepartamento')
            ->select('personal.*', 'expediente.IdExpediente', 'departamento.nombreDepartamento')
            ->get()->map(function ($expediente) {
                $expediente->generalInfo = $expediente->Nombre . ' ' . $expediente->Apellidos . ' - ' . $expediente->nombreDepartamento;
                return $expediente->only(['IdExpediente', 'generalInfo']);
            });;
        $personalDocente = $docente->personal;
        $tipo_documentos = TipoDocumento::all();
        $departamentos = Departamento::all();
        $periodos_escolares = PeriodoEscolar::all()->map(function ($periodo) {
            $periodo->generalInfo = $periodo->nombre_corto . ' (' . $periodo->fechaInicio . '-' . $periodo->fechaTermino . ')';
            return $periodo;
        });
        $documentosDocente = $this->obtenerDocumentosExpedienteEspecífico($expediente->IdExpediente, $personal, $user);
        $data = [
            'user' => $user,
            'personal' => $personal,
            'personalDocente' => $personalDocente,
            'documentosDocente' => $documentosDocente,
            'expediente' => $expediente,
            'tipo_documentos' => $tipo_documentos,
            'departamentos' => $departamentos,
            'periodos_escolares' => $periodos_escolares,
            'expedientes' => $expedientes
        ];
        if ($personal->administrador !== null) {
            return Inertia::render('Dashboard_admin_expedienteEspecifico', $data);
        }
        if ($personal->secretaria !== null) {
            return Inertia::render('Dashboard_secre_expedienteEspecifico', $data);
        }
    }
    /**
     * Metodo para obtener los documentos de un expediente en específico y permisos para editar y/o entregar
     * el documento
     * 
     * @param $idExpediente Es el id del expediente al que se desea obtener todos sus documentos
     * @param $personal Es el personal del usuario con la actual sesión
     * @param $user Es el Usuario con la actual sesión
     */
    public function obtenerDocumentosExpedienteEspecífico($idExpediente, $personal, $user)
    {
        return document::where('IdExpediente', $idExpediente)
            ->join('tipo_documento', 'tipo_documento.IdTipoDocumento', '=', 'documento.IdTipoDocumento')
            ->join('periodo_escolar', 'periodo_escolar.IdPeriodoEscolar', '=', 'documento.IdPeriodoEscolar')
            ->leftJoin('departamento', 'departamento.IdDepartamento', '=', 'documento.IdDepartamento')
            ->select('documento.*', 'tipo_documento.nombreTipoDoc', 'periodo_escolar.nombre_corto', 'departamento.nombreDepartamento')
            ->get()->map(function ($documento) use ($personal, $user) {
                if ($personal->docente) {
                    $documento->entrega = false;
                    if ($documento->IdUsuario == $user->id)
                        $documento->edita = true;
                    else
                        $documento->edita = false;
                } else {
                    if ($documento->fechaEntrega == null)
                        $documento->entrega = true;
                    else
                        $documento->entrega = false;
                    if ($personal->administrador != null) {
                        $documento->edita = true;
                    } else if ($personal->secretaria != null) {
                        if ($documento->user->personal->docente != null || $documento->IdUsuario == $user->id)
                            $documento->edita = true;
                        else
                            $documento->edita = false;
                    }
                }
                return $documento;
            });
    }
}
