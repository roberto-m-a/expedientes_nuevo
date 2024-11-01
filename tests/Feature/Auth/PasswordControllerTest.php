<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\PasswordController;
use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PasswordControllerTest extends TestCase
{
    use RefreshDatabase;
    //////////////Pruebas para la funcion de la contraseña//////////////////
    public function test_actualizar_contraseña(){
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $administrador = Administrador::create([
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $user = User::create([
            'IdPersonal' => $personal->IdPersonal,
            'password' => Hash::make('passwordD@7'),
            'email' => 'roberto.m@itoaxaca.edu.mx'
        ]);

        $this->actingAs($user);

        $request = Request::create(route('password.update'), 'PUT' ,[
            'current_password' => 'passwordD@7',
            'password' => 'new-passworD@7',
            'password_confirmation' => 'new-passworD@7',
        ]);

        $controller = new PasswordController();
        $controller->update($request);
        $user = $user->fresh();
        $this->assertTrue(Hash::check('new-passworD@7', $user->password));
    }
    //Pruebas de error
    public function test_actualizar_contraseña_error_por_contraseña_actual_incorrecta(){
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $administrador = Administrador::create([
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $user = User::create([
            'IdPersonal' => $personal->IdPersonal,
            'password' => Hash::make('passwordD@7'),
            'email' => 'roberto.m@itoaxaca.edu.mx'
        ]);

        $this->actingAs($user);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('La contraseña es incorrecta');
        $request = Request::create(route('password.update'), 'PUT' ,[
            'current_password' => 'passwordD@7Incorrecta',
            'password' => 'new-passworD@7',
            'password_confirmation' => 'new-passworD@7',
        ]);
        $controller = new PasswordController();
        $controller->update($request);
    }
    public function test_actualizar_contraseña_error_por_falta_de_caracteres_en_nueva_contraseña(){
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $administrador = Administrador::create([
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $user = User::create([
            'IdPersonal' => $personal->IdPersonal,
            'password' => Hash::make('passwordD@7'),
            'email' => 'roberto.m@itoaxaca.edu.mx'
        ]);

        $this->actingAs($user);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El campo contraseña debe contener al menos 8 caracteres.');
        $request = Request::create(route('password.update'), 'PUT' ,[
            'current_password' => 'passwordD@7',
            'password' => 'Contr@6',
            'password_confirmation' => 'Contr@6',
        ]);
        $controller = new PasswordController();
        $controller->update($request);
    }
    public function test_actualizar_contraseña_error_por_confirmacion_de_nueva_contraseña_incorrecta(){
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $administrador = Administrador::create([
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $user = User::create([
            'IdPersonal' => $personal->IdPersonal,
            'password' => Hash::make('passwordD@7'),
            'email' => 'roberto.m@itoaxaca.edu.mx'
        ]);

        $this->actingAs($user);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('La confirmación de contraseña no coincide.');
        $request = Request::create(route('password.update'), 'PUT' ,[
            'current_password' => 'passwordD@7',
            'password' => 'new-passworD@7',
            'password_confirmation' => 'new-passworD@78',
        ]);
        $controller = new PasswordController();
        $controller->update($request);
    }
    public function test_actualizar_contraseña_error_por_falta_de_simbolos_en_nueva_contraseña(){
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $administrador = Administrador::create([
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $user = User::create([
            'IdPersonal' => $personal->IdPersonal,
            'password' => Hash::make('passwordD@7'),
            'email' => 'roberto.m@itoaxaca.edu.mx'
        ]);

        $this->actingAs($user);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('La contraseña debe contener al menos un símbolo.');
        $request = Request::create(route('password.update'), 'PUT' ,[
            'current_password' => 'passwordD@7',
            'password' => 'newpassworD7',
            'password_confirmation' => 'newpassworD7',
        ]);
        $controller = new PasswordController();
        $controller->update($request);
    }
    //////////////////Pruebas para la funcion de completar informacion//////////////////////////
    public function test_ingresar_datos_por_primera_vez_en_el_sistema_administrador()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => null,
            'Sexo' => null,
        ]);
        $administrador = Administrador::create([
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $user = User::create([
            'IdPersonal' => $personal->IdPersonal,
            'password' => null,
            'email' => 'roberto.m@itoaxaca.edu.mx'
        ]);

        $this->actingAs($user);

        $request = Request::create(route('completar.informacion'), 'POST' ,[
            'password' => 'new-passworD@7',
            'password_confirmation' => 'new-passworD@7',
            'Departamento' => ['IdDepartamento' => $departamento->IdDepartamento],
            'Sexo' => 'Hombre',
        ]);

        $controller = new PasswordController();
        $controller->completarInformacion($request);
        $user = $user->fresh();
        $this->assertTrue(Hash::check('new-passworD@7', $user->password));

        $this->assertDatabaseHas('personal', [
            'IdPersonal' => $personal->IdPersonal,
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
    }
    public function test_ingresar_datos_por_primera_vez_en_el_sistema_docente()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => null,
            'Sexo' => null,
        ]);
        $docente = Docente::create([
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => null,
        ]); 

        $user = User::create([
            'IdPersonal' => $personal->IdPersonal,
            'password' => null,
            'email' => 'roberto.m@itoaxaca.edu.mx'
        ]);

        $this->actingAs($user);

        $request = Request::create(route('completar.informacion'), 'POST' ,[
            'password' => 'new-passworD@7',
            'password_confirmation' => 'new-passworD@7',
            'Departamento' => ['IdDepartamento' => $departamento->IdDepartamento],
            'GradoAcademico' => ['nombreGradoAcademico' => 'Licenciatura'],
            'Sexo' => 'Hombre',
        ]);

        $controller = new PasswordController();
        $controller->completarInformacion($request);
        $user = $user->fresh();
        $this->assertTrue(Hash::check('new-passworD@7', $user->password));

        $this->assertDatabaseHas('personal', [
            'IdPersonal' => $personal->IdPersonal,
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
    }
}
