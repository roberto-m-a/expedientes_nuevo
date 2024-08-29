<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\expediente;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use App\Notifications\notificacionRegistroCorreo;
use Inertia\Inertia;

class personalController extends Controller
{
    //
    public function index()
    {
        $departamentos = Departamento::all();
        $user = User::find(Auth::user()->id);
        $personal = Personal::where('IdPersonal', Auth::user()->IdPersonal)->first();
        if (Administrador::where('IdPersonal', $personal->IdPersonal)->first() == null)
            return Redirect::route('dashboard');

        $documentos_subidos = DB::table('documento')
            ->select('documento.IdUsuario', DB::raw('count(*) as totalDocumentos'))
            ->groupBy('documento.IdUsuario');

        $personal_data = DB::table('personal')
            ->leftJoin('docente', 'docente.IdPersonal', '=', 'personal.IdPersonal')
            ->leftJoin('administrador', 'administrador.IdPersonal', '=', 'personal.IdPersonal')
            ->leftJoin('departamento', 'departamento.IdDepartamento', '=', 'personal.IdDepartamento')
            ->leftJoin('users', 'users.IdPersonal', '=', 'personal.IdPersonal')
            ->leftJoin('expediente', 'expediente.IdDocente', '=', 'docente.IdDocente')
            ->leftJoinSub($documentos_subidos, 'documentos_subidos', function ($join) {
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
            ->get();
        return Inertia::render('Dashboard_admin_personal', ['user' => $user, 'personal' => $personal, 'departamentos' => $departamentos, 'personal_data' => $personal_data]);
    }
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
                if ($request->email !== $request->email_confirmation) {
                    throw ValidationException::withMessages([
                        'email' => 'Los correos no coinciden',
                    ]);
                }
                if (strpos($request->email, '@itoaxaca.edu.mx') == false && strpos($request->email, '@oaxaca.tecnm.mx') == false) {
                    throw ValidationException::withMessages([
                        'email' => 'El dominio debe ser de la institución (@itoaxaca.edu.mx o @oaxaca.tecnm.mx)',
                    ]);
                }

                $user = User::create([
                    'email' => $request->email,
                    'IdPersonal' => $Personal->IdPersonal,
                ]);
                $user->notify(new notificacionRegistroCorreo());
            }

            DB::commit();
            return Redirect::route('personal');
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

    public function editarPersonal(Request $request)
    {
        $user = User::where('IdPersonal', $request->IdPersonal)->first();
        $personal = Personal::where('IdPersonal', $request->IdPersonal)->first();
        $personal->Nombre = $request->Nombre;
        $personal->Apellidos = $request->Apellidos;
        $personal->Sexo = $request->Sexo;
        $personal->IdDepartamento = $request->Departamento['IdDepartamento'];
        $personal->save();
        if ($request->Docente) {
            $docente = Docente::where('IdPersonal', $personal->IdPersonal)->first();

            $request->validate([
                'GradoAcademico' => 'required',
            ]);
            $docente->GradoAcademico = $request->GradoAcademico;
            $docente->save();
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

        return Redirect::route('personal');
    }

    public function validarPersonal(Request $request)
    {
        $request->validate([
            'Nombre' => 'required|string|max:50',
            'Apellidos' => 'required|string|max:100',
            'Sexo' => 'required',
            'Departamento' => 'required',
        ]);

        if ($request->Docente) {
            $request->validate([
                'GradoAcademico' => 'required',
            ]);
        }

        $personal = Personal::where('IdPersonal', $request->IdPersonal)->first();
        if ($user = User::where('IdPersonal', $request->IdPersonal)->first() != null) {
            if (strpos($request->email, '@itoaxaca.edu.mx') == false && strpos($request->email, '@oaxaca.tecnm.mx') == false) {
                throw ValidationException::withMessages([
                    'email' => 'El dominio debe ser de la institución (@itoaxaca.edu.mx o @oaxaca.tecnm.mx)',
                ]);
            }
            if ($request->email !== $request->email_confirmation) {
                throw ValidationException::withMessages([
                    'email' => 'Los correos no coinciden',
                    'email_confirmation' => 'Los correos no coinciden',
                ]);
            }
        }
    }

    public function borrarPersonal(Request $request)
    {
        $personal = Personal::where('IdPersonal', $request->IdPersonal)->first();
        $user = User::where('IdPersonal', $personal->IdPersonal)->first();
        $docente = Docente::where('IdPersonal', $personal->IdPersonal)->first();
        $administrador = Administrador::where('IdPersonal', $personal->IdPersonal)->first();

        //dd($administrador);
        $secretaria = Secretaria::where('IdPersonal', $personal->IdPersonal)->first();
        if ($user != null) {
            $user->delete();
        }
        if ($docente != null) {
            $expediente = expediente::where('IdDocente', $docente->IdDocente)->first();
            $expediente->delete();
            $docente->delete();
        }
        if ($administrador != null) {
            $administrador->delete();
        }
        if ($secretaria != null) {
            $secretaria->delete();
        }
        $personal->delete();
    }
}
