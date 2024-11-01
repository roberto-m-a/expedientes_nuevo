<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AutenticacionDeCorreo;
use App\Notifications\ContraseniaTemporal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TwoFactorAutenticateController extends Controller
{
    //
    /**
     * Metodo para verificar el correo
     * El método busca el usuario con el email a verificar, si lo encuentra, verifica si el token que se manda al método es el mismo
     * que se registró en la base de datos, después checa si la expiración de dicho token no ha pasado para poder modificar los datos
     * del usuario, la fecha de verificacion se coloca con la fecha actual, los datos del token se vuelven nulos y se procede a 
     * generar el codigo temporal y el token de dicho codigo a una notificacón al correo electrónico para despues renderizar el login
     * con uso de contraseña única.
     * 
     * @param $email Email del usuario a verificar
     * @param $token Token de validacion de correo electrónico
     * @return Inertia\Inertia retorna la vista del login con contraseña de uso único o bien puede retornar a otras vistas
     */
    public function verificarCorreo($email, $token)
    {
        $user = User::where('email', $email)->first();
        if ($user)
            if ($user->token_email == $token)
                if ($user->token_email_expire_at->isPast())
                    return Inertia::render('Auth/ResendEmailVerification', ['tokenExpirado' => $user->token_email]);
                else {
                    $user->update([
                        'email_verified_at' => now(),
                        'token_email' => null,
                        'token_email_expire_at' => null,
                    ]);
                    if ($user->password == null) {
                        $user->generarCodigoTemporalYToken();
                        $user->notify(new ContraseniaTemporal($user->email, $user->OTPassword, $user->OTP_token));
                        return Inertia::render('Auth/LoginOTP', ['token' => $user->OTP_token]);
                    }else{
                        return Inertia::render('Auth/Login', ['canResetPassword'=> true, 'status' => 'Correo confirmado']);
                    }
                }
            else
                return redirect(route('login'));
        else
            return redirect(route('login'));
    }
    /**
     * Verificar el OTPassword
     * 
     * El método encuentra el usuario al que pertenece el email, luego si existe verifica que el token enviado
     * al método sea el mismo que el token que se registró, luego se procede a checar si el token ha expirado
     * luego se renderiza la vista para loguearse con la contraseña de uso único
     * 
     * @param $email Email del usuario que desea ingresar con la contraseña de uso único
     * @param $token Token de la contraseña de uso único
     * @return Inertia\Inertia retorna la vista del login con contraseña de uso único o bien puede retornar otras vistas
     */
    public function verificarOTPassword($email, $token)
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            if ($user->OTP_token == $token) {
                if ($user->OTPassword_expire_at->isPast()) {
                    return Inertia::render('Auth/ResendOTP', ['tokenExpirado' => $token]);
                } else {
                    return Inertia::render('Auth/LoginOTP', ['token' => $token]);
                }
            } else
                return redirect(route('login'));
        } else
            return redirect(route('login'));
    }
    /**
     * Login con OTPassword
     * 
     * El metodo valida el dominio del correo, los datos de los campos de correo y contraseña de uso único
     * luego hace las validaciones pertinentes para checar los datos del token y contraseña de uso único
     * desde el modelo, si se obtiene el usuario significa que ha pasado por todas las validaciones y se
     * procede a actualizar los datos de la contraseña de uso único para despues autenticar al usuario y
     * este pueda acceder al sistema
     * 
     * @param Illuminate\Http\Request Petición HTTP con los datos necesarios para loguearse con la contraseña de úso único
     */
    public function loginConOTPassword(Request $request)
    {
        User::validarDominioCorreo($request->email);
        $request->validate(User::$validarLoginOTP);
        $user = User::validarLoginConOTPassword($request);
        $user->update([
            'OTPassword' => null,
            'OTPassword_expire_at' => null,
            'OTP_token' => null,
        ]);
        Auth::login($user);
    }
    /**
     * Metodo que regenera el token de verificacion de email cuando el token expiró
     * 
     * @param Illuminate\Http\Request Petición HTTP con los datos necesarios para el método
     */
    public function regenerarTokenVerificarEmail(Request $request)
    {
        User::validarDominioCorreo($request->email);
        $user = User::validarReenvioDeCorreoDeVerificación($request);
        $user->generarTokenVerificaCorreo();
        $user->notify(new AutenticacionDeCorreo($user->email, $user->token_email));
    }
    /**
     * Metodo que regenera la contraseña de uso único y el token para ingresar con dicha contraseña
     * 
     * @param Illuminate\Http\Request Petición HTTP con los datos necesarios para el método
     * @return Inertia\Inertia retorna la vista del login con contraseña de uso único
     */
    public function regenerarOTP(Request $request)
    {
        User::validarDominioCorreo($request->email);
        $user = User::validarLoginConOTPassword($request);
        $user->generarCodigoTemporalYToken();
        $user->notify(new ContraseniaTemporal($user->email, $user->OTPassword, $user->OTP_token));
        return Inertia::render('Auth/LoginOTP', ['token' => $user->OTP_token]);
    }
}
