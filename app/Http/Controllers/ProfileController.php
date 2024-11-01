<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Renderiza la vista con la información del perfil en los formularios.
     * 
     * @param \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del perfil a editar.
     * 
     * @return Inertia\Inertia Renderiza la vista correspondiente al tipo de usuario.
     */
    public function edit(Request $request): Response
    {
        $user = User::find(Auth::user()->id);
        $departamentos = Departamento::all();
        $personal = Personal::where('IdPersonal', Auth::user()->IdPersonal)->first();
        $data = [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' =>$user,
            'personal'=>$personal,
            'departamentos'=>$departamentos,
        ];
        if(Secretaria::where('IdPersonal', Auth::user()->IdPersonal)->first()!==null){
            return Inertia::render('Profile/Edit_secretaria', $data);  
        }
        if(Administrador::where('IdPersonal',Auth::user()->IdPersonal)->first()!==null){
            return Inertia::render('Profile/Edit_admin', $data);    
        }
        return Inertia::render('Profile/Edit', $data);
    }

    /**
     * Actualizar la información de perfil de usuario
     * 
     * Se validan los campos correspondientes para despues actualizar los datos del perfil
     * Se verifica si el correo electronico cambió para tambien actualizar los datos correspondientes al usuario
     * 
     * @param App\Http\Requests\ProfileUpdateRequest Solicitud HTTP que contiene los datos a editar
     * 
     * @return Illuminate\Support\Facades\Redirect Redirecciona a la vista del perfil con los datos actualizados
     */
    public function update(ProfileUpdateRequest $request)
    {
        $request->user()->fill($request->validated());
        User::validarDominioCorreo($request->email);

        Personal::find(Auth::user()->IdPersonal)->update([
            'Nombre' => $request->name,
            'Apellidos' => $request ->lastname,
            'IdDepartamento' => $request->Departamento['IdDepartamento'],
            'Sexo' => $request->Sexo,
        ]);   
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
            $request->user()->save();
        }
        //return Redirect::route('profile.edit')->with('actualizacionCorrecta', 'Se han actualizado los datos en tu perfil');
    }
}