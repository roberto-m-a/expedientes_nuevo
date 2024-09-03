<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\expediente;
use App\Models\Personal;
use App\Models\User;
use App\Notifications\notificacionRegistroCorreo;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Muestra la vista para registrarse.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Crear un usuario en el sistema
     * 
     * El método valida los datos de la petición para después crear los registros necesarios para la agregación
     * de un usuario del tipo docente
     * 
     * @param Illuminate\Http\Request Petición HTTP con los datos necesarios para crear un usuario de tipo Docente
     * 
     * @return Illuminate\Support\Facades\Redirect redirecciona a la vista HOME para mostrar un aviso de que 
     * es necesario verificar su correo electrónico y despues de eso
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(Personal::$validarPersonalRegistro);
        $request->validate(User::$validarCorreo);
        User::validarDominioCorreo($request->email);
        $personal = Personal::create([
            'Nombre' => $request->name, 
            'Apellidos' => $request->lastname,
        ]);
        $user = User::create([
            'email' => $request->email,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        event(new Registered($user));
        $docente = Docente::create([
            'GradoAcademico'=>'Licenciatura',
            'IdPersonal' => $personal ->IdPersonal,
        ]);
        expediente::create([
            'IdDocente' => $docente->IdDocente, 
        ]);        
        Auth::login($user);
        return redirect(RouteServiceProvider::HOME);
    }
    /**
     * El metodo permite validar unicamente el correo electronico del usuario para que el administrador pueda añadir
     * el usuario cuando este no se creo durante el registro del personal
     * 
     * @param Illuminate\Http\Request Petición HTTP con los datos a validar
     */
    public function validarUsuario(Request $request){
        $request->validate(User::$validarCorreo);
        User::validarDominioCorreo($request->email);
    }
    /**
     * Crea al usuario previamente validado y manda una notificacion al correo del usuario para que este verifique su correo.
     * 
     * @param Illuminate\Http\Request Peticion HTTP con los datos necesarios para la creación de un nuevo usaurio
     * 
     * @return Illuminate\Support\Facades\Redirect Redirecciona a la página de personal con un mensaje flash de la acción realizada
     */
    public function aniadirUsuario(Request $request){
        $user = User::create([
            'email' => $request->email,
            'IdPersonal' => $request->IdPersonal,
        ]);
        $user->notify(new notificacionRegistroCorreo());
        return Redirect::route('personal')->with('creacionCorrecta','Se ha creado el usuario correctamente');
    }
}
