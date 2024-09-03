<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Actualizar la contraseña
     * 
     * El método permite actualizar la contraseña del usuario autenticado siempre y cuando pase las validaciones
     * del modelo, despues actualiza la contraseña de forma encriptada en la base de datos
     * 
     * @param Illuminate\Http\Request Petición HTTP que contiene los datos de la nueva contraseña y de la actual
     * 
     * @return Illuminate\Http\RedirectResponse Redirecciona a la misma página con un mensaje de la acción realizada
     */
    public function update(Request $request): RedirectResponse
    {
        $user = User::find(Auth::user()->id);
        $validated = $request->validate(User::getValidacionesActualizarContrasenia());
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);
        return back()->with('actualizacionCorrecta', 'Haz actualizado tu contraseña correctamente');
    }
    /**
     * Actualiza los campos por primera vez
     * 
     * El método permite que el usuario creado por el administrador pueda ingresar sus datos generales
     * asi como el de crear su contraseña para acceder al sistema siempre y cuando este contenga al
     * menos: una minúscula, una mayúscula, un símbolo, un número y 8 caracteres
     */
    public function firstPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate(User::getValidacionesNuevoUsuarioSinContrasenia());
        $user = User::find(Auth::user()->id);
        $user->personal->update([
            'IdDepartamento' => $request->Departamento['IdDepartamento'],
            'Sexo' => $request->Sexo,
        ]);
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);
        return back()->with('actualizacionCorrecta', 'Haz actualizado tus datos correctamente por primera vez');
    }
}
