<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class departamentoController extends Controller
{
    /**
     * 
     */
    public function index()
    {
        $user = User::find(Auth::user()->id);
        $personal = $user->personal;
        if (Docente::where('IdPersonal', $personal->IdPersonal)->first() != null)
            return Redirect::route('dashboard');
        $departamentos = Departamento::with('personal', 'documento')
            ->withCount('personal as numPersonal')
            ->withCount('documento as numDocumentos')
            ->get();
        if (Secretaria::where('IdPersonal', Auth::user()->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_secre_departamento', ['user' => $user, 'personal' => $personal, 'departamentos' => $departamentos]);
        }
        if (Administrador::where('IdPersonal', Auth::user()->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_admin_departamento', ['user' => $user, 'personal' => $personal, 'departamentos' => $departamentos]);
        }
    }
    /**
     * Crear un departamento
     *
     * Valida los datos ingresados en la solicitud, si es exitosa, se creará en la base de datos
     * el nuevo registro en la tabla de departamento. Despues redirecciona a la vista de departamento
     * con un mensaje flash de la acción realizada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del nuevo departamento.
     * 
     * @return Illuminate\Support\Facades\Redirect  Redirige a la vista de departamentos con un mensaje de éxito.
     */
    public function nuevoDepartamento(Request $request)
    {
        $this->validarDepartamento($request);
        Departamento::create([
            "nombreDepartamento" => $request->nombreDepartamento,
        ]);
        return Redirect::route('departamento')->with('creacionCorrecta', 'Departamento creado correctamente');
    }
    /**
     * Edita un departamento
     *
     * Busca el departamento con el id que se proporciona en la solicitud para despues actualizar
     * los campos del registro en la base de datos. Despues redirecciona a la vista de departamento
     * con un mensaje flash de la acción realizada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del departamento a editar.
     * 
     * @return Illuminate\Support\Facades\Redirect  Redirige a la vista de departamentos con un mensaje de éxito.
     */
    public function editarDepartamento(Request $request)
    {
        Departamento::find($request->idDepartamento)
            ->update(['nombreDepartamento' => $request->nombreDepartamento]);
        return Redirect::route('departamento')->with('actualizacionCorrecta', 'Departamento actualizado correctamente');
    }
    /**
     * Valida un departamento
     *
     * Valida el departamento a través del modelo y si la validación no es correcta se retornarán los errores
     * en la solicitud mandada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del departamento a validar.
     */
    public function validarDepartamento(Request $request)
    {
        $request->validate(Departamento::$departamentoValidacion);
    }
    /**
     * Borra un departamento
     *
     * Busca el departamento con el id que se proporciona en la solicitud para despues borrar
     * el registro en la base de datos. Despues redirecciona a la vista de departamento con
     * un mensaje flash de la acción realizada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del departamento a borrar.
     * 
     * @return Illuminate\Support\Facades\Redirect  Redirige a la vista de departamentos con un mensaje de éxito.
     */
    public function borrarDepartamento(Request $request)
    {
        Departamento::find($request->idDepartamento)->delete();
        return Redirect::route('departamento')->with('borradoCorrecto', 'Departamento borrado correctamente');
    }
}