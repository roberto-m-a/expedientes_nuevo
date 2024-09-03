<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\document;
use App\Models\expediente;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Notifications\notificacionRegistroCorreo;
use Inertia\Inertia;

class personalController extends Controller
{
    //
    public function index()
    {
        $user = User::find(Auth::user()->id);
        $personal = $user->personal;
        if ($personal->administrador == null)
            return Redirect::route('dashboard');
        $departamentos = Departamento::all();
        $documentosSubidos = document::select('IdUsuario', DB::raw('count(*) as totalDocumentos'))
            ->groupBy('IdUsuario');
        
        $personalData = Personal::leftJoin('docente', 'docente.IdPersonal', '=', 'personal.IdPersonal')
            ->leftJoin('administrador', 'administrador.IdPersonal', '=', 'personal.IdPersonal')
            ->leftJoin('departamento', 'departamento.IdDepartamento', '=', 'personal.IdDepartamento')
            ->leftJoin('users', 'users.IdPersonal', '=', 'personal.IdPersonal')
            ->leftJoin('expediente', 'expediente.IdDocente', '=', 'docente.IdDocente')
            ->leftJoinSub($documentosSubidos, 'documentos_subidos', function ($join) {
                $join->on('users.id', '=', 'documentos_subidos.IdUsuario');
            })
            ->select(
                'users.id',
                'personal.IdPersonal',
                'personal.Nombre',
                'personal.Apellidos',
                'personal.Sexo',
                'departamento.nombreDepartamento',
                'docente.IdDocente',
                'expediente.numDocumentos',
                'administrador.IdAdministrador',
                'docente.GradoAcademico',
                'users.email',
                'documentos_subidos.totalDocumentos'
            )
            ->get()->map(function($personal){
                $personal->has_no_relations = ($personal->totalDocumentos <= 0 || $personal->totalDocumentos == null) && 
                ($personal->numDocumentos <=0 || $personal->numDocumentos == null);
                return $personal;
            });
            
        return Inertia::render('Dashboard_admin_personal', ['user' => $user, 'personal' => $personal, 'departamentos' => $departamentos, 'personal_data' => $personalData]);
    }
    /**
     * Crear un personal
     *
     * Valida los datos ingresados en la solicitud, si es exitosa, se creará en la base de datos
     * el nuevo registro en la tabla de personal, en la tabla del tipo de usuario correspondiente
     * , el expediente si es un docente y creará un usuario si es que se especificó en la vista.
     * Se hace uso de BeginTransaction para poder deshacer los registros si se encontraron errores
     * al momento de validar los campos.
     * Despues redirecciona a la vista de personal con un mensaje flash de la acción realizada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del nuevo personal.
     * 
     * @return Illuminate\Support\Facades\Redirect  Redirige a la vista de personal con un mensaje de éxito.
     */
    public function nuevoPersonal(Request $request)
    {
        $request->validate(Personal::$validarPersonal);
        try {
            DB::beginTransaction();
            $Personal = Personal::create([
                'Nombre' => $request->Nombre,
                'Apellidos' => $request->Apellidos,
                'Sexo' => $request->Sexo,
                'IdDepartamento' => $request->Departamento,
            ]);

            if ($request->tipoUsuario == 'Docente') {
                $request->validate(Docente::$validarDocente);
                $Docente = Docente::create([
                    'IdPersonal' => $Personal->IdPersonal,
                    'GradoAcademico' => $request->GradoAcademico,
                ]);
                expediente::create([
                    'IdDocente' => $Docente->IdDocente,
                ]);
            }

            if ($request->tipoUsuario == 'Administrador') {
                Administrador::create(['IdPersonal' => $Personal->IdPersonal,]);
            }
            if ($request->tipoUsuario == 'Secretaria') {
                Secretaria::create(['IdPersonal' => $Personal->IdPersonal,]);
            }
            if ($request->crearUsuario) {
                $request->validate(User::$validarCorreo);
                User::validarDominioCorreo($request->email);
                $user = User::create([
                    'email' => $request->email,
                    'IdPersonal' => $Personal->IdPersonal,
                ]);
                $user->notify(new notificacionRegistroCorreo());
            }
            DB::commit();
            return Redirect::route('personal')->with('creacionCorrecta', 'Se ha creado al personal' . (($request->crearUsuario) ? ' y usuario' : '') . ' correctamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            throw $e;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
    /**
     * Edita un personal
     *
     * Busca el personal con el id que se proporciona en la solicitud para despues actualizar
     * los campos del registro en la base de datos y en las demas tablas que tengan relacion con el personal. 
     * Después redirecciona a la vista de personal con un mensaje flash de la acción realizada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del personal a editar.
     * 
     * @return Illuminate\Support\Facades\Redirect  Redirige a la vista de personal con un mensaje de éxito.
     */
    public function editarPersonal(Request $request)
    {
        $user = User::where('IdPersonal', $request->IdPersonal)->first();
        Personal::find($request->IdPersonal)->update([
            'Nombre' => $request->Nombre,
            'Apellidos' => $request->Apellidos,
            'Sexo' => $request->Sexo,
            'IdDepartamento' => $request->Departamento['IdDepartamento'],
        ]);
        if ($request->Docente) {
            $request->validate(Docente::$validarDocente);
            Docente::where('IdPersonal',$request->IdPersonal)->first()->update([
                'GradoAcademico' => $request->GradoAcademico,
            ]);
        }
        if ($user != null) {
            $correoInicial = $user->email;
            if ($correoInicial != $request->email) {
                $user->email = $request->email;
                $user->email_verified_at = null;
            }
            $user->save();
            if ($correoInicial != $request->email) {
                $user->notify(new notificacionRegistroCorreo());
            }
        }
        return Redirect::route('personal')->with('actualizacionCorrecta', 'Se han actualizado los datos del personal correctamente');
    }
    /**
     * Valida un personal
     *
     * Valida el personal a través del modelo y si la validación no es correcta se retornarán los errores
     * en la solicitud mandada.
     * Este metodo sólo es utilizado para hacer una validación antes de realizar la edición del personal.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del personal a validar.
     */
    public function validarPersonal(Request $request)
    {
        $request->validate(Personal::$validarPersonal);
        if ($request->Docente) {
            $request->validate(Docente::$validarDocente);
        }
        $user = User::find($request->IdPersonal);
        if ($user != null) {
            User::validarCorreo_UnicoYNoRepetido($request->email, $user->id);
            User::validarDominioCorreo($request->email);
        }
    }
    /**
     * Borra un personal
     *
     * Busca el personal con el id que se proporciona en la solicitud para despues borrar
     * el registro en la base de datos y los registros de otras tablas relacionados a el. 
     * Despues redirecciona a la vista de personal con un mensaje flash de la acción realizada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del personal a borrar.
     * 
     * @return Illuminate\Support\Facades\Redirect  Redirige a la vista de personals con un mensaje de éxito.
     */
    public function borrarPersonal(Request $request)
    {
        $personal = Personal::find($request->IdPersonal);
        if ($personal->user != null) {
            $personal->user->delete();
        }
        if ($personal->docente != null) {
            $personal->docente->expediente->delete();
            $personal->docente->delete();
        }
        if ($personal->administrador != null) {
            $personal->administrador->delete();
        }
        if ($personal->secretaria != null) {
            $personal->secretaria->delete();
        }
        $personal->delete();
        return Redirect::route('personal')->with('borradoCorrecto','Se ha eliminado al personal correctamente');
    }
}