<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\expediente;
use App\Models\Personal;
use App\Models\User;
use App\Notifications\AutenticacionDeCorreo;
use App\Notifications\notificacionRegistroCorreo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

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
    public function store(Request $request) //: RedirectResponse
    {
        try {
            DB::beginTransaction();
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
            $user->generarTokenVerificaCorreo();
            $user->notify(new AutenticacionDeCorreo($user->email,$user->token_email));
            $docente = Docente::create([
                //'GradoAcademico'=>'Licenciatura',
                'IdPersonal' => $personal->IdPersonal,
            ]);
            expediente::create([
                'IdDocente' => $docente->IdDocente,
            ]);
            DB::commit();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            throw $e;
        }
    }
    /**
     * El metodo permite validar unicamente el correo electronico del usuario para que el administrador pueda añadir
     * el usuario cuando este no se creo durante el registro del personal
     * 
     * @param Illuminate\Http\Request Petición HTTP con los datos a validar
     */
    public function validarUsuario(Request $request)
    {
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
    public function aniadirUsuario(Request $request)
    {
        $this->validarUsuario($request);
        $user = User::create([
            'email' => $request->email,
            'IdPersonal' => $request->IdPersonal,
        ]);
        $user->generarTokenVerificaCorreo();
        $user->notify(new AutenticacionDeCorreo($user->email, $user->token_email));
     }
}
