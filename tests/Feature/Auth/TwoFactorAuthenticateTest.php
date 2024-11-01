<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\TwoFactorAutenticateController;
use App\Models\Departamento;
use App\Models\Personal;
use App\Models\User;
use App\Notifications\AutenticacionDeCorreo;
use App\Notifications\ContraseniaTemporal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Validation\ValidationException;
use Inertia\Response as InertiaResponse;

class TwoFactorAuthenticateTest extends TestCase
{
    use RefreshDatabase;
    ///////////////Pruebas para el metodo de verificar correo///////////////////////
    public function test_verificar_correo()
    {
        // Falsea las notificaciones para cersiorarse que se envian
        Notification::fake();

        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'token_email' => 'token123',
            'token_email_expire_at' => now()->addDay(), // token expira en 1 dia
            'email_verified_at' => null,
            'password' => null, // Forzar que no tenga contraseña para probar OTP
            'IdPersonal' => $personal->IdPersonal,
        ]);

        // Crear instancia del controlador y ejecutar el método verificarCorreo
        $twoFactorController = new TwoFactorAutenticateController();
        $response = $twoFactorController->verificarCorreo($user->email, $user->token_email);

        // Refrescar el usuario desde la base de datos para obtener los cambios
        $user->refresh();

        // Asertar que el email fue verificado
        $this->assertNotNull($user->email_verified_at);

        // Asertar que los campos de token_email y token_email_expire_at son nulos
        $this->assertNull($user->token_email);
        $this->assertNull($user->token_email_expire_at);

        // Asertar que se generó un OTP y se envió la notificación
        $this->assertNotNull($user->OTP_token);
        Notification::assertSentTo($user, ContraseniaTemporal::class);
    }
    public function test_verificar_correo_caso_con_contraseña()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'token_email' => 'token123',
            'token_email_expire_at' => now()->addDay(), // token expira en 1 dia
            'email_verified_at' => null,
            'password' => 'contraseña12313D%',
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $response = $this->get('/verificar-correo/roberto.manuel@oaxaca.tecnm.mx/token123');

        // Refrescar el usuario desde la base de datos para obtener los cambios
        $user->refresh();

        // Asertar que el email fue verificado
        $this->assertNotNull($user->email_verified_at);

        // Asertar que los campos de token_email y token_email_expire_at son nulos
        $this->assertNull($user->token_email);
        $this->assertNull($user->token_email_expire_at);

        $response->assertInertia(
            fn($page) =>
            $page->component('Auth/Login')
                ->where('canResetPassword', true)
                ->where('status', 'Correo confirmado')
        );
    }
    //Pruebas de error
    public function test_verificar_correo_redireccion_login_por_usuario_invalido()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'token_email' => 'token123',
            'token_email_expire_at' => now()->addDay(), // token expira en 1 dia
            'email_verified_at' => null,
            'password' => null, // Forzar que no tenga contraseña para probar OTP
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $token = 'faketoken123';
        // Realizamos la solicitud a la acción 'verificarCorreo'
        $response = $this->get('/verificar-correo/' . $user->email . '/' . $token);

        // Verificamos que la respuesta redirige a la ruta de login
        $response->assertRedirect(route('login'));
    }
    public function test_verificar_correo_redireccion_el_reenvio_de_correo_por_token_expirado()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'token_email' => 'token123',
            'token_email_expire_at' => now()->subMinute(), // token expira en 1 dia
            'email_verified_at' => null,
            'password' => null, // Forzar que no tenga contraseña para probar OTP
            'IdPersonal' => $personal->IdPersonal,
        ]);
        // Realizamos la solicitud a la acción 'verificarCorreo'
        $response = $this->get('/verificar-correo/' . $user->email . '/' . $user->token_email);

        $response->assertInertia(
            fn($page) =>
            $page->component('Auth/ResendEmailVerification')
                ->where('tokenExpirado', 'token123')
        );
    }
    public function test_verificar_correo_redireccion_login_por_token_invalido()
    {
        // Definimos valores de prueba
        $email = 'inexistente@oaxaca.tecnm.mx';
        $token = 'faketoken123';
        // Realizamos la solicitud a la acción 'verificarCorreo'
        $response = $this->get('/verificar-correo/' . $email . '/' . $token);

        // Verificamos que la respuesta redirige a la ruta de login
        $response->assertRedirect(route('login'));
    }
    /////////////////// Pruebas para el renderizado de la vista de login con otp ///////////////
    public function test_verificar_renderizado_de_la_vista_de_otp_Password()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
            'OTPassword_expire_at' => now()->addDay(), // token expira en 1 dia
            'OTP_token' => 'token234543',
            'email_verified_at' => now(),
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);

        // Crear instancia del controlador y ejecutar el método verificarOTPassword
        $twoFactorController = new TwoFactorAutenticateController();
        $response = $twoFactorController->verificarOTPassword($user->email, $user->OTP_token);

        // Refrescar el usuario desde la base de datos para obtener los cambios
        $user->refresh();

        // Verificar si es una respuesta de inertia
        $this->assertInstanceOf(InertiaResponse::class, $response);
    }
    //Pruebas de error
    public function test_verificar_renderizado_de_la_vista_de_otp_Password_redireccion_al_login_por_usuario_no_encontrado()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
            'OTPassword_expire_at' => now()->addDay(), // token expira en 1 dia
            'OTP_token' => 'token234543',
            'email_verified_at' => now(),
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $response = $this->get('/verificar-otp/email.incorrecto@oaxaca.tecnm.mx/' . $user->OTP_token);

        // Verificamos que la respuesta redirige a la ruta de login
        $response->assertRedirect(route('login'));
    }
    public function test_verificar_renderizado_de_la_vista_de_otp_Password_redireccion_al_login_por_token_incorrecto()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
            'OTPassword_expire_at' => now()->addDay(), // token expira en 1 dia
            'OTP_token' => 'token234543',
            'email_verified_at' => now(),
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $response = $this->get('/verificar-otp/roberto.manuel@oaxaca.tecnm.mx/tokenIncorrecto12314124');

        // Verificamos que la respuesta redirige a la ruta de login
        $response->assertRedirect(route('login'));
    }
    public function test_verificar_renderizado_de_la_vista_de_otp_Password_redireccion_al_reenvio_de_token_por_token_expirado()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
            'OTPassword_expire_at' => now()->subMinute(), // token expira en 1 dia
            'OTP_token' => 'token234543',
            'email_verified_at' => now(),
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $response = $this->get('/verificar-otp/roberto.manuel@oaxaca.tecnm.mx/token234543');

        $response->assertInertia(
            fn($page) =>
            $page->component('Auth/ResendOTP')
                ->where('tokenExpirado', 'token234543')
        );
    }
    /////////////////////Pruebas de login con otp////////////////////
    public function test_login_con_el_otp_password()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
            'OTPassword_expire_at' => now()->addDay(), // token expira en 1 dia
            'OTP_token' => 'token234543',
            'email_verified_at' => now(),
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $request = Request::create(route('login.otp'), 'POST', [
            'token' => 'token234543',
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
        ]);
        // Crear instancia del controlador y ejecutar el método verificarOTPassword
        $twoFactorController = new TwoFactorAutenticateController();
        $response = $twoFactorController->loginConOTPassword($request);

        // Refrescar el usuario desde la base de datos para obtener los cambios
        $user->refresh();
        $this->assertNull($user->OTPassword);
        $this->assertNull($user->OTP_token);
        $this->assertNull($user->OTPassword_token_expire_at);
    }
    //Pruebas de error
    public function test_login_con_el_otp_password_error_usuario_no_encontrado()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
            'OTPassword_expire_at' => now()->addDay(), // token expira en 1 dia
            'OTP_token' => 'token234543',
            'email_verified_at' => now(),
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $request = Request::create(route('login.otp'), 'POST', [
            'token' => 'token234543',
            'email' => 'roberto.manuel.lopez@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No hay ningún usuario con el correo ingresado');
        // Crear instancia del controlador y ejecutar el método loginOTPPassword
        $twoFactorController = new TwoFactorAutenticateController();
        $response = $twoFactorController->loginConOTPassword($request);
    }
    public function test_login_con_el_otp_password_error_otp_incorrecto()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
            'OTPassword_expire_at' => now()->addDay(), // token expira en 1 dia
            'OTP_token' => 'token234543',
            'email_verified_at' => now(),
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $request = Request::create(route('login.otp'), 'POST', [
            'token' => 'token234543',
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlkasur87768',
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Contraseña única incorrecta');
        // Crear instancia del controlador y ejecutar el método verificarOTPassword
        $twoFactorController = new TwoFactorAutenticateController();
        $response = $twoFactorController->loginConOTPassword($request);
    }
    public function test_login_con_el_otp_password_error_otp_token_incorrecto()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
            'OTPassword_expire_at' => now()->addDay(), // token expira en 1 dia
            'OTP_token' => 'token234543',
            'email_verified_at' => now(),
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $request = Request::create(route('login.otp'), 'POST', [
            'token' => 'tokenincorrectoa12347862340',
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El token no coincide con nuestros registros');
        // Crear instancia del controlador y ejecutar el método verificarOTPassword
        $twoFactorController = new TwoFactorAutenticateController();
        $response = $twoFactorController->loginConOTPassword($request);
    }
    ////////////////Pruebas de la funcion de reenviar correo de verificacion////////////////
    public function test_reenviar_correo_de_verificacion(): void
    {
        // Falsea las notificaciones para sersiorarse que se envian.
        Notification::fake();

        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'token_email' => 'token123',
            'token_email_expire_at' => now(), // token expirado
            'email_verified_at' => null,
            'password' => null, // Forzar que no tenga contraseña para probar OTP
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $request = Request::create(route('reenviar.verificacionemail'), 'POST', [
            'tokenExpirado' => 'token123',
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
        ]);
        // Crear instancia del controlador y ejecutar el método verificarCorreo
        $twoFactorController = new TwoFactorAutenticateController();
        $response = $twoFactorController->regenerarTokenVerificarEmail($request);
        $token_antiguo = $user->token_email;
        $fecha_expiracion_antigua = $user->token_email_expire_at;
        // Refrescar el usuario desde la base de datos para obtener los cambios
        $user->refresh();

        // verificar que se generó un nuevo token
        $this->assertNotEquals($user->token_email, $token_antiguo);
        // verificar que se generó una nueva fecha de expiración
        $this->assertNotEquals($user->token_email_expire_at, $fecha_expiracion_antigua);
        //verifica que la notificacion se haya enviado
        Notification::assertSentTo($user, AutenticacionDeCorreo::class);
    }
    //pruebas de error
    public function test_reenviar_correo_de_verificacion_error_por_token_invalido(): void
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'token_email' => 'token123',
            'token_email_expire_at' => now(), // token expirado
            'email_verified_at' => null,
            'password' => null, // Forzar que no tenga contraseña para probar OTP
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $request = Request::create(route('reenviar.verificacionemail'), 'POST', [
            'tokenExpirado' => 'token123Incorrecto',
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El token no coincide con nuestros registros');
        // Crear instancia del controlador y ejecutar el método verificarCorreo
        $twoFactorController = new TwoFactorAutenticateController();
        $response = $twoFactorController->regenerarTokenVerificarEmail($request);
    }
    public function test_reenviar_correo_de_verificacion_error_por_email_no_encontrado(): void
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'token_email' => 'token123',
            'token_email_expire_at' => now(), // token expirado
            'email_verified_at' => null,
            'password' => null, // Forzar que no tenga contraseña para probar OTP
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $request = Request::create(route('reenviar.verificacionemail'), 'POST', [
            'tokenExpirado' => 'token123',
            'email' => 'roberto.manuel.lopez@oaxaca.tecnm.mx',
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No hay ningún usuario con el correo ingresado');
        // Crear instancia del controlador y ejecutar el método verificarCorreo
        $twoFactorController = new TwoFactorAutenticateController();
        $response = $twoFactorController->regenerarTokenVerificarEmail($request);
    }
    /////////////////Pruebas de la función de reenviarOTP/////////////////
    public function test_reenviar_otp_password_por_expiracion()
    {
        // Falsea las notificaciones para sersiorarse que se envian.
        Notification::fake();
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
            'OTPassword_expire_at' => now(), // token expirado
            'OTP_token' => 'token234543',
            'email_verified_at' => now(),
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $request = Request::create(route('reenviar.otp'), 'POST', [
            'token' => 'token234543',
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
        ]);
        $OTP_antiguo = $user->OTPassword;
        $OTP_expired_antiguo = $user->OTPassword_expire_at;
        $OTP_token_antiguo = $user->OTP_token;
        // Crear instancia del controlador y ejecutar el método verificarOTPassword
        $twoFactorController = new TwoFactorAutenticateController();
        $response = $twoFactorController->regenerarOTP($request);

        // Refrescar el usuario desde la base de datos para obtener los cambios
        $user->refresh();
        //Asegurarse que la contraseña de uso unico cambió
        $this->assertNotEquals($user->OTPassword, $OTP_antiguo);
        //Asegurarse que la expiracion de la contraseña cambió
        $this->assertNotEquals($user->OTPassword_expire_at, $OTP_expired_antiguo);
        //Asegurarse que el token cambió
        $this->assertNotEquals($user->OTP_token, $OTP_token_antiguo);
        //verifica que la notificacion se haya enviado
        Notification::assertSentTo($user, ContraseniaTemporal::class);
    }
    //Pruebas de error
    public function test_reenviar_otp_password_por_expiracion_error_por_token_invalido()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
            'OTPassword_expire_at' => now(), // token expirado
            'OTP_token' => 'token234543',
            'email_verified_at' => now(),
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $request = Request::create(route('reenviar.otp'), 'POST', [
            'token' => 'token234543Invalido',
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El token no coincide con nuestros registros');
        // Crear instancia del controlador y ejecutar el método verificarOTPassword
        $twoFactorController = new TwoFactorAutenticateController();
        $response = $twoFactorController->regenerarOTP($request);
    }
    public function test_reenviar_otp_password_por_expiracion_error_por_email_invalido()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
            'OTPassword_expire_at' => now(), // token expirado
            'OTP_token' => 'token234543',
            'email_verified_at' => now(),
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $request = Request::create(route('reenviar.otp'), 'POST', [
            'token' => 'token234543',
            'email' => 'roberto.manuel.lopez@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No hay ningún usuario con el correo ingresado');
        // Crear instancia del controlador y ejecutar el método verificarOTPassword
        $twoFactorController = new TwoFactorAutenticateController();
        $response = $twoFactorController->regenerarOTP($request);
    }
    public function test_reenviar_otp_password_por_expiracion_error_por_otp_invalido()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel Abelino',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);
        // Crear el usuario en la base de datos con los campos necesarios
        $user = User::create([
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7',
            'OTPassword_expire_at' => now(), // token expirado
            'OTP_token' => 'token234543',
            'email_verified_at' => now(),
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $request = Request::create(route('reenviar.otp'), 'POST', [
            'token' => 'token234543',
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'OTPassword' => 'asdlhfula7Incorrecto',
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Contraseña única incorrecta');
        // Crear instancia del controlador y ejecutar el método verificarOTPassword
        $twoFactorController = new TwoFactorAutenticateController();
        $response = $twoFactorController->regenerarOTP($request);
    }
}
