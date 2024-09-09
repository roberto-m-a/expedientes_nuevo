<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\document;
use App\Models\expediente;
use App\Models\modificacion;
use App\Models\PeriodoEscolar;
use App\Models\TipoDocumento;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class documentController extends Controller
{
    /**
     * Renderiza la vista de subir documento
     * 
     * El método obtiene todos los datos necesarios para la subida de archivos, luego los manda
     * a la vista dependiendo de que tipo de usuario es, el docente solo obtiene su propio
     * expediente, mientras que los otros dos usuarios obtienen los expedientes de todos los
     * docentes
     * 
     * @return Inertia\Inertia Retorna la vista con Inertia junto a las variables de los registros
     */
    public function index()
    {
        $user = User::find(Auth::user()->id);
        $personal = $user->personal;
        $departamentos = Departamento::all();
        $tiposDocumentos = TipoDocumento::all();
        $periodosEscolares = PeriodoEscolar::all()->map(function ($periodo) {
            $periodo->generalInfo = $periodo->nombre_corto . ' (' . $periodo->fechaInicio . '-' . $periodo->fechaTermino . ')';
            return $periodo->only(['IdPeriodoEscolar', 'generalInfo']);
        });
        if ($personal->docente !== null) {
            $expediente = $personal->docente->expediente->only('IdExpediente');
            return Inertia::render('Dashboard_subirDocumento', ['user' => $user, 'personal' => $personal, 'departamentos' => $departamentos, 'periodosEscolares' => $periodosEscolares, 'expediente' => $expediente, 'tiposDocumentos' => $tiposDocumentos]);
        } else {
            $expediente_data = expediente::join('docente', 'docente.IdDocente', '=', 'expediente.IdDocente')
                ->join('personal', 'personal.IdPersonal', '=', 'docente.IdPersonal')
                ->join('departamento', 'departamento.IdDepartamento', '=', 'personal.IdDepartamento')
                ->select('personal.Nombre', 'personal.apellidos', 'expediente.IdExpediente', 'departamento.nombreDepartamento')
                ->get()->map(function ($expediente) {
                    $expediente->generalInfo = $expediente->Nombre . ' ' . $expediente->Apellidos . ' - ' . $expediente->nombreDepartamento;
                    return $expediente->only(['IdExpediente', 'generalInfo']);
                });
            if ($personal->secretaria !== null) {
                return Inertia::render('Dashboard_secre_nuevoDocumento', ['user' => $user, 'personal' => $personal, 'departamentos' => $departamentos, 'expediente_data' => $expediente_data, 'periodosEscolares' => $periodosEscolares, 'tiposDocumentos' => $tiposDocumentos]);
            }
            if ($personal->administrador !== null) {
                return Inertia::render('Dashboard_admin_nuevoDocumento', ['user' => $user, 'personal' => $personal, 'departamentos' => $departamentos, 'expediente_data' => $expediente_data, 'periodosEscolares' => $periodosEscolares, 'tiposDocumentos' => $tiposDocumentos]);
            }
        }
    }
    /**
     * Renderiza la vista de documentos
     * 
     * El método permite obtener la vista de todos los documentos en el sistema, asimismo tambien
     * se obtienen todos los datos necesarios a traves del ORM. Después renderiza la vista dependiendo
     * del tipo de usuario a traves de Inertia con los datos recabados
     * 
     * @return Inertia\Inertia Renderiza la vista ingresada y los datos necesarios para ser manejados en la vista
     */
    public function documentsIndex()
    {
        $user = User::find(Auth::user()->id);
        $personal = $user->personal;
        if ($personal->docente != null)
            return Redirect::route('dashboard');
        $departamentos = Departamento::all();
        $tipo_documentos = TipoDocumento::all();
        $periodos_escolares = PeriodoEscolar::all()->map(function ($periodo) {
            $periodo->generalInfo = $periodo->nombre_corto . ' (' . $periodo->fechaInicio . '-' . $periodo->fechaTermino . ')';
            return $periodo->only(['IdPeriodoEscolar', 'generalInfo']);
        });
        $expediente_data = expediente::join('docente', 'docente.IdDocente', '=', 'expediente.IdDocente')
            ->join('personal', 'personal.IdPersonal', '=', 'docente.IdPersonal')
            ->join('departamento', 'departamento.IdDepartamento', '=', 'personal.IdDepartamento')
            ->select('personal.*', 'expediente.IdExpediente', 'departamento.nombreDepartamento')
            ->get()->map(function ($expediente) {
                $expediente->generalInfo = $expediente->Nombre . ' ' . $expediente->Apellidos . ' - ' . $expediente->nombreDepartamento;
                return $expediente->only(['IdExpediente', 'generalInfo']);
            });
        $documentos = document::join('tipo_documento', 'tipo_documento.IdTipoDocumento', '=', 'documento.IdTipoDocumento')
            ->join('periodo_escolar', 'periodo_escolar.IdPeriodoEscolar', '=', 'documento.IdPeriodoEscolar')
            ->join('expediente', 'expediente.IdExpediente', '=', 'documento.IdExpediente')
            ->join('docente', 'docente.IdDocente', '=', 'expediente.IdDocente')
            ->join('personal', 'personal.IdPersonal', '=', 'docente.IdPersonal')
            ->leftJoin('departamento', 'departamento.IdDepartamento', '=', 'documento.IdDepartamento')
            ->select('documento.*', 'tipo_documento.nombreTipoDoc', 'periodo_escolar.nombre_corto', 'departamento.nombreDepartamento', 'personal.Nombre', 'personal.Apellidos')
            ->get()->map(function($documento) use ($personal, $user){
                if($documento->fechaEntrega == null)
                        $documento->entrega = true;
                    else
                        $documento->entrega = false;
                if($personal->administrador != null){
                    $documento->edita = true;
                }else if($personal->secretaria != null){
                    if($documento->user->personal->docente !=null || $documento->IdUsuario == $user->id)
                        $documento->edita = true;
                    else{
                        $documento->edita = false;
                    }
                }
               return $documento;
            });
        if ($personal->secretaria !== null) {
            return Inertia::render('Dashboard_secre_documentos', ['user' => $user, 'personal' => $personal, 'documentos' => $documentos, 'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodos_escolares, 'expedientes' => $expediente_data]);
        }
        if ($personal->administrador !== null) {
            return Inertia::render('Dashboard_admin_documentos', ['user' => $user, 'personal' => $personal, 'documentos' => $documentos, 'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodos_escolares, 'expedientes' => $expediente_data]);
        }
    }
    /**
     * Crear un nuevo documento
     * 
     * Este método valida primero los campos del documento en la petición para luego renombrar el archivo
     * con datos de la fecha de subida. Una vez hecho lo anterior de guarda el archivo en el almacenamiento
     * si se ha guardado correctamente se procede a crear el documento con los datos de los campos ingresados
     * y despues actualiza la cantidad de documentos del expediente al que se ingresará el documento
     * 
     * @param Illuminate\Http\Request Peticion HTTP que contiene los campos del documento a agregar
     * 
     * @return Illuminate\Support\Facades\Redirect Redirecciona a la vista de subida de documento con un mensáje de éxito
     */
    public function nuevoDocumento(Request $request)
    {
        document::validarDocumento($request, true);
        $fecha = new DateTime(); // Obtener la fecha actual como objeto DateTime
        $archivo = $request->Archivo;
        $nombreArchivo = date_format($fecha, 'Y-m-d_H_i_s') . '_' . $archivo->getClientOriginalName();
        $path = Storage::putFileAs('public/documents', $archivo, $nombreArchivo);
        if (Storage::exists($path)) {
            document::create([
                'Titulo' => $request->Titulo,
                'fechaExpedicion' => $request->FechaExpedicion,
                'fechaEntrega' => ($request->Estatus == 'Entregado') ? $request->FechaEntrega : null,
                'Estatus' => ($request->Region == 'Interno') ? $request->Estatus : 'Entregado',
                'region' => $request->Region,
                'IdTipoDocumento' => $request->TipoDocumento['IdTipoDocumento'],
                'IdPeriodoEscolar' => $request->PeriodoEscolar['IdPeriodoEscolar'],
                'IdExpediente' => $request->Expediente['IdExpediente'],
                'IdDepartamento' => ($request->Region == 'Externo') ? null : $request->Departamento['IdDepartamento'],
                'IdUsuario' => Auth::user()->id,
                'URL' => asset('storage/documents/' . $nombreArchivo),
                'Dependencia' => ($request->Region == 'Interno') ? '' : $request->Dependencia,
            ]);
            $expediente = expediente::find($request->Expediente['IdExpediente']);
            $expediente->update(['numDocumentos' => $expediente->numDocumentos + 1]);
        }
        return Redirect::route('nuevoDocumento')->with('creacionCorrecta', 'Se ha registrado el documento con éxito.');
    }
    /**
     * Editar Documento
     * 
     * Edita la información del documento asi como la de reemplazar el archivo ligado a este por uno nuevo
     * El archivo anterior será borrado permanentemente. También registra los cambios en la tabla modificacion
     * de la base de datos, con todos los cambios detectados
     * 
     * @param Illuminate\Http\Request Peticion HTTP que contiene los campos del documento a editar
     * 
     * @return Illuminate\Support\Facades\Redirect Redirecciona a la vista de subida de documento con un mensáje de éxito 
     */
    public function editarDocumento(Request $request)
    {
        $documento = document::find($request->IdDocumento);
        $expediente = $documento->expediente;
        $documentoOriginal = clone $documento; // Clonar el documento original
        $cambioExpediente = '';
        if ($request->Archivo != '') {
            $nombreArchivo = explode('documents/', $documento->URL);
            if (Storage::exists('public/documents/' . $nombreArchivo[1])) {
                Storage::delete('public/documents/' . $nombreArchivo[1]);
            }
            $fecha = new DateTime(); // Obtener la fecha actual como objeto DateTime
            $archivo = $request->Archivo;
            $nombreArchivo = date_format($fecha, 'Y-m-d_H_i_s') . '_' . $archivo->getClientOriginalName();
            $path = Storage::putFileAs('public/documents', $archivo, $nombreArchivo);
            if (Storage::exists($path)) {
                $documento->URL = asset('storage/documents/' . $nombreArchivo);
            }
        }
        if ($expediente->IdExpediente != $request->Expediente['IdExpediente']) {
            $expediente->update([
                'numDocumentos' => $expediente->numDocumentos - 1,
            ]);
            $nuevoExpediente = expediente::find($request->Expediente['IdExpediente']);
            $nuevoExpediente->update([
                'numDocumentos' => $nuevoExpediente->numDocumentos + 1,
            ]);
            $cambioExpediente = $cambioExpediente . ' ,Expediente: ' .
                $expediente->docente->personal->Nombre . ' ' . $expediente->docente->personal->Apellidos . ' => ' .
                $nuevoExpediente->docente->personal->Nombre . ' ' . $nuevoExpediente->docente->personal->Apellidos;
        }
        $documento->Titulo = $request->Titulo;
        $documento->FechaExpedicion = $request->FechaExpedicion;
        $documento->FechaEntrega = ($request->Estatus == 'Entregado') ? $request->FechaEntrega : null;
        $documento->Estatus = ($request->Region == 'Interno') ? $request->Estatus : 'Entregado';
        $documento->region = $request->Region;
        $documento->IdTipoDocumento = $request->TipoDocumento['IdTipoDocumento'];
        $documento->IdPeriodoEscolar = $request->PeriodoEscolar['IdPeriodoEscolar'];
        $documento->IdExpediente = $request->Expediente['IdExpediente'];
        $documento->IdDepartamento = ($request->Region == 'Externo') ? null : $request->Departamento['IdDepartamento'];
        $documento->Dependencia = ($request->Region == 'Interno') ? '' : $request->Dependencia;

        $documento->save();

        // Detectar cambios comparando con el documento original
        $cambios = [];
        foreach ($documentoOriginal->getAttributes() as $key => $value) {
            if ($documento->$key != $value) {
                $cambios[$key] = ['original' => $value, 'nuevo' => $documento->$key];
            }
        }
        // Aquí se muestran los cambios detectados
        $arrayAsString = $this->arrayToString($cambios) . $cambioExpediente;
        $user = User::find(Auth::user()->id);
        modificacion::create([
            'Nombre' => $user->personal->Nombre,
            'Apellidos' => $user->personal->Apellidos,
            'Titulo' => $request->Titulo,
            'Modificaciones' => $arrayAsString,
            'IdDocumento' => $documento->IdDocumento,
            'IdUsuario' => $user->id,
        ]);
        return back()->with('actualizacionCorrecta', 'Se ha editado el documento correctamente');
    }
    /**
     * método que permite transformar un arreglo bidimensional en una cadena de caracteres para el
     * registro de la modificación
     */
    function arrayToString($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = implode('=> ', $value);
            }
            $result[] = "$key: $value";
        }
        return implode(', ', $result);
    }
    /**
     * Llama al método estático para validar el documento y se pone un false para decir que el documento no
     * es necesariamente requerido ya que este método es específicamente para validar antes de editar el
     * documento
     * 
     * @param Illuminate\Http\Request Peticion HTTP que contiene los campos del documento
     */
    public function validarDocumento(Request  $request)
    {
        document::validarDocumento($request, false);
    }
    /**
     * Entrega de documento
     * 
     * El método busca el documento al cual se le hará la modificación de entrega para despues 
     * checar si hay un archivo en la variable Archivo de la petición, si es el caso entonces 
     * borra el anterior documento y guarda el nuevo documento para despues asignar al registro
     * del documento la nueva URL para acceder al nuevo documento. Tambien actualiza la fecha de
     * entrega y el estatus del documento
     * 
     * @param Illuminate\Http\Request Peticion HTTP que contiene los campos del documento
     * 
     * @return Illuminate\Support\Facades\Redirect Redirecciona a la vista de subida de documento con un mensáje de éxito
     */
    public function entregarDocumento(Request $request)
    {
        $documento = document::find($request->IdDocumento);
        if ($request->Archivo != '') {
            $nombreArchivo = explode('documents/', $documento->URL);
            if (Storage::exists('public/documents/' . $nombreArchivo[1])) {
                Storage::delete('public/documents/' . $nombreArchivo[1]);
            }
            $fecha = new DateTime(); // Obtener la fecha actual como objeto DateTime
            $archivo = $request->Archivo;
            $nombreArchivo = date_format($fecha, 'Y-m-d_H_i_s') . '_' . $archivo->getClientOriginalName();
            $path = Storage::putFileAs('public/documents', $archivo, $nombreArchivo);
            if (Storage::exists($path)) {
                $documento->URL = asset('storage/documents/' . $nombreArchivo);
            }
        }
        $documento->FechaEntrega = $request->FechaEntrega;
        $documento->Estatus = 'Entregado';
        $documento->save();
        return back()->with('creacionCorrecta', 'Se ha entregado el documento correctamente');
    }
    /**
     * Llama al método estático en el modelo para validar la entrega de un documento
     */
    public function validarEntrega(Request $request)
    {
        document::ValidarEntregaDocumento($request);
    }
}
