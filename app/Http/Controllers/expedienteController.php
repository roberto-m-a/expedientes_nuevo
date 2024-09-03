<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\document;
use App\Models\expediente;
use App\Models\PeriodoEscolar;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class expedienteController extends Controller
{
    //
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
            $documentosDocente = document::where('IdExpediente', '=', $expediente->IdExpediente)
                ->join('tipo_documento', 'tipo_documento.IdTipoDocumento', '=', 'documento.IdTipoDocumento')
                ->join('periodo_escolar', 'periodo_escolar.IdPeriodoEscolar', '=', 'documento.IdPeriodoEscolar')
                ->leftJoin('departamento', 'departamento.IdDepartamento', '=', 'documento.IdDepartamento')
                ->select('documento.*', 'tipo_documento.nombreTipoDoc', 'periodo_escolar.nombre_corto', 'departamento.nombreDepartamento')
                ->get();
            return Inertia::render('Dashboard_miExpediente', ['user' => $user, 'personal' => $personal, 'documentosDocente' => $documentosDocente, 'expediente' => $expediente, 'periodos_escolares',  'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodos_escolares]);
        } else {
            $expedientes = expediente::join('docente', 'docente.IdDocente', '=', 'expediente.IdDocente')
                ->join('personal', 'personal.IdPersonal', '=', 'docente.IdPersonal')
                ->join('departamento', 'departamento.IdDepartamento', '=', 'personal.IdDepartamento')
                ->select('personal.Nombre', 'personal.Apellidos', 'expediente.IdExpediente', 'expediente.numDocumentos', 'departamento.nombreDepartamento')
                ->get();
            if (Administrador::where('IdPersonal', $personal->IdPersonal)->first() !== null) {
                return Inertia::render('Dashboard_admin_expedientes', ['user' => $user, 'personal' => $personal, 'expedientes' => $expedientes]);
            }
            if (Secretaria::where('IdPersonal', Auth::user()->IdPersonal)->first() !== null) {
                return Inertia::render('Dashboard_secre_expedientes', ['user' => $user, 'personal' => $personal, 'expedientes' => $expedientes]);
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
        $personalDocente = $docente->personal;

        $tipo_documentos = TipoDocumento::all();
        $departamentos = Departamento::all();
        $periodos_escolares = PeriodoEscolar::all()->map(function ($periodo) {
            $periodo->generalInfo = $periodo->nombre_corto . ' (' . $periodo->fechaInicio . '-' . $periodo->fechaTermino . ')';
            return $periodo;
        });
        $documentosDocente = document::where('IdExpediente', $expediente->IdExpediente)
            ->join('tipo_documento', 'tipo_documento.IdTipoDocumento', '=', 'documento.IdTipoDocumento')
            ->join('periodo_escolar', 'periodo_escolar.IdPeriodoEscolar', '=', 'documento.IdPeriodoEscolar')
            ->leftJoin('departamento', 'departamento.IdDepartamento', '=', 'documento.IdDepartamento')
            ->select('documento.*', 'tipo_documento.nombreTipoDoc', 'periodo_escolar.nombre_corto', 'departamento.nombreDepartamento')
            ->get();

        if (Administrador::where('IdPersonal', $personal->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_admin_expedienteEspecifico', ['user' => $user, 'personal' => $personal, 'personalDocente' => $personalDocente, 'documentosDocente' => $documentosDocente, 'expediente' => $expediente, 'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodos_escolares]);
        }
        if (Secretaria::where('IdPersonal', Auth::user()->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_secre_expedienteEspecifico', ['user' => $user, 'personal' => $personal, 'personalDocente' => $personalDocente, 'documentosDocente' => $documentosDocente, 'expediente' => $expediente, 'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodos_escolares]);
        }
    }
}
