<?php

namespace Tests\Unit;

use App\Http\Controllers\personalController;
use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\expediente;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Inertia\Response as InertiaResponse;

class PersonalTest extends TestCase
{
    use RefreshDatabase;
    public function test_personal_tiene_relacion_con_usuario_y_departamento()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $this->assertInstanceOf(User::class, $personal->user);
        $this->assertEquals($user->IdPersonal, $personal->IdPersonal);
        $this->assertInstanceOf(Departamento::class, $personal->departamento);
        $this->assertEquals($user->IdPersonal, $personal->IdPersonal);
    }

    public function test_retorna_a_la_vista_de_dashboard_por_ser_docente()
    {
        $departamento = Departamento::create([
            'IdDepartamento' => 1234,
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'IdPersonal' => 777,
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo',
        ]);
        $user = User::create([
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $docente = Docente::create([
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);
        Auth::login($user);

        $controller = new personalController();

        // Simular una solicitud
        $request = Request::create('/personal', 'GET');
        $response = $controller->index($request);
        // Verificar que la respuesta es una redirección
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        // Verificar la URL de redirección
        $this->assertEquals(url('/dashboard'), $response->getTargetUrl());
    }

    public function crea_y_loguea_usuario_admin()
    {
        DB::table('departamento')->insert([
            ['IdDepartamento' => 1, 'nombreDepartamento' => 'Departamento 1'],
            ['IdDepartamento' => 2, 'nombreDepartamento' => 'Departamento 2'],
        ]);
        DB::table('tipo_documento')->insert([
            ['IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo 1'],
            ['IdTipoDocumento' => 2, 'nombreTipoDoc' => 'Tipo 2'],
        ]);

        DB::table('personal')->insert([
            ['IdPersonal' => 1, 'Nombre' => 'Usuario Prueba 1', 'Apellidos' => 'Apellidos 1', 'Sexo' => 'Hombre', 'IdDepartamento' => 1],
            ['IdPersonal' => 2, 'Nombre' => 'Usuario Prueba 2', 'Apellidos' => 'Apellidos 2', 'Sexo' => 'Mujer', 'IdDepartamento' => 2],
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'email' => 'prueba@example.com',
            'password' => bcrypt('password'),
            'IdPersonal' => 1
        ]);
        DB::table('administrador')->insert(
            ['IdAdministrador' => 100, 'IdPersonal' => 1],
        );
        DB::table('docente')->insert([
            ['IdDocente' => 2, 'IdPersonal' => 2, 'GradoAcademico' => 'Licenciatura'],
        ]);

        DB::table('expediente')->insert([
            ['IdExpediente' => 2, 'IdDocente' => 2],
        ]);

        DB::table('periodo_escolar')->insert([
            ['IdPeriodoEscolar' => 1, 'nombre_corto' => '2023-2024', 'fechaInicio' => '2023-01-01', 'fechaTermino' => '2024-01-01'],
            ['IdPeriodoEscolar' => 2, 'nombre_corto' => '2022-2023', 'fechaInicio' => '2022-01-01', 'fechaTermino' => '2023-01-01'],
        ]);

        DB::table('documento')->insert([
            [
                'Titulo' => 'Document Title',
                'fechaExpedicion' => '2024-08-06',
                'fechaEntrega' => '2024-08-07',
                'Estatus' => 'Entregado',
                'region' => 'Externo',
                'IdTipoDocumento' => 1,
                'IdPeriodoEscolar' => 1,
                'IdExpediente' => 2,
                'IdDepartamento' => null,
                'IdUsuario' => 1,
                'URL' => 'http://example.com',
                'Dependencia' => 'Dependencia 1',
            ],
            [
                'Titulo' => 'Document Title',
                'fechaExpedicion' => '2024-08-06',
                'fechaEntrega' => '2024-08-07',
                'Estatus' => 'En proceso',
                'region' => 'Interno',
                'IdTipoDocumento' => 1,
                'IdPeriodoEscolar' => 1,
                'IdExpediente' => 2,
                'IdDepartamento' => 1,
                'IdUsuario' => 1,
                'URL' => 'http://example.com',
                'Dependencia' => '',
            ]
        ]);
        // Autenticar el usuario
        $user = User::find(1);
        $this->actingAs($user);
    }
    public function test_renderiza_correctamente_la_vista_del_personal()
    {
        $this->crea_y_loguea_usuario_admin();
        $controller = new personalController();

        // Simular una solicitud
        $request = Request::create('/personal', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);

        // Verificar contenido de la respuesta para el rol de Secretaría
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_admin_personal', $responseContent);
    }
    /////////////////Pruebas de la funcion de nuevo personal///////////////////
    public function test_Nuevo_Personal_docente_con_exito_sin_correo()
    {
        Event::fake();
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);

        // Crear una solicitud simulada
        $request = Request::create('/personal', 'PUT', [
            'Nombre' => 'Armando',
            'Apellidos' => 'Mendoza',
            'Sexo' => 'Hombre',
            'Departamento' => ['IdDepartamento' => $departamento->IdDepartamento],
            'tipoUsuario' => 'Docente',
            'GradoAcademico' => ['nombreGradoAcademico' => 'Licenciatura'],
            'crearUsuario' => false,
            'email' => '',
            'email_confirmation' => '',
        ]);

        $controller = new personalController();

        $controller->nuevoPersonal($request);

        $this->assertDatabaseHas('personal', [
            'Nombre' => 'Armando',
            'Apellidos' => 'Mendoza',
            'Sexo' => 'Hombre',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);

        $personal = Personal::where('Nombre', 'Armando')->first();

        $this->assertDatabaseHas('docente', [
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);

        $docente = Docente::where('IdPersonal', $personal->IdPersonal)->first();

        $this->assertDatabaseHas('expediente', [
            'IdDocente' => $docente->IdDocente,
        ]);
    }
    public function test_Nuevo_Personal_secretaria_con_exito_sin_correo()
    {
        Event::fake();
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);

        // Crear una solicitud simulada
        $request = Request::create('/personal', 'PUT', [
            'Nombre' => 'Armando',
            'Apellidos' => 'Mendoza',
            'Sexo' => 'Hombre',
            'Departamento' => ['IdDepartamento' => $departamento->IdDepartamento],
            'tipoUsuario' => 'Secretaria',
            'GradoAcademico' => '',
            'crearUsuario' => false,
            'email' => '',
            'email_confirmation' => '',
        ]);

        $controller = new personalController();

        $controller->nuevoPersonal($request);

        $this->assertDatabaseHas('personal', [
            'Nombre' => 'Armando',
            'Apellidos' => 'Mendoza',
            'Sexo' => 'Hombre',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);

        $personal = Personal::where('Nombre', 'Armando')->first();

        $this->assertDatabaseHas('secretaria', [
            'IdPersonal' => $personal->IdPersonal,
        ]);
    }
    public function test_Nuevo_Personal_administrador_con_exito_sin_correo()
    {
        Event::fake();
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);

        // Crear una solicitud simulada
        $request = Request::create('/personal', 'PUT', [
            'Nombre' => 'Armando',
            'Apellidos' => 'Mendoza',
            'Sexo' => 'Hombre',
            'Departamento' => ['IdDepartamento' => $departamento->IdDepartamento],
            'tipoUsuario' => 'Administrador',
            'GradoAcademico' => '',
            'crearUsuario' => false,
            'email' => '',
            'email_confirmation' => '',
        ]);

        $controller = new personalController();

        $controller->nuevoPersonal($request);

        $this->assertDatabaseHas('personal', [
            'Nombre' => 'Armando',
            'Apellidos' => 'Mendoza',
            'Sexo' => 'Hombre',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);

        $personal = Personal::where('Nombre', 'Armando')->first();

        $this->assertDatabaseHas('administrador', [
            'IdPersonal' => $personal->IdPersonal,
        ]);
    }

    public function test_Nuevo_personal_con_correo()
    {
        Event::fake();
        Notification::fake();
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);

        // Crear una solicitud simulada
        $request = Request::create('/personal', 'PUT', [
            'Nombre' => 'Armando',
            'Apellidos' => 'Mendoza',
            'Sexo' => 'Hombre',
            'Departamento' => ['IdDepartamento' => $departamento->IdDepartamento],
            'tipoUsuario' => 'Administrador',
            'GradoAcademico' => '',
            'crearUsuario' => true,
            'email' => 'armando.mendoza@oaxaca.tecnm.mx',
            'email_confirmation' => 'armando.mendoza@oaxaca.tecnm.mx',
        ]);

        $controller = new personalController();

        $controller->nuevoPersonal($request);

        $this->assertDatabaseHas('personal', [
            'Nombre' => 'Armando',
            'Apellidos' => 'Mendoza',
            'Sexo' => 'Hombre',
            'IdDepartamento' => $departamento->IdDepartamento,
        ]);

        $personal = Personal::where('Nombre', 'Armando')->first();

        $this->assertDatabaseHas('administrador', [
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $this->assertDatabaseHas('users', [
            'IdPersonal' => $personal->IdPersonal,
            'email' => 'armando.mendoza@oaxaca.tecnm.mx',
        ]);

        $user = User::where('IdPersonal', $personal->IdPersonal)->first();

        $this->assertFalse(Auth::check()); //Se comprueba que sea falso debido a que este personal con correo se crea desde el administrador por lo que no deberia de iniciar la sesion con el usuario creado
    }
    //Pruebas de error
    public function test_Nuevo_personal_falla_por_no_colocar_nombre()
    {
        $this->expectExceptionMessage('El campo nombre es obligatorio');
        $this->expectException(ValidationException::class);
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);

        // Crear una solicitud simulada
        $request = Request::create('/personal', 'PUT', [
            'Nombre' => '',
            'Apellidos' => 'Lopez',
            'Sexo' => 'Hombre',
            'Departamento' => $departamento->IdDepartamento,
            'tipoUsuario' => 'Administrador',
            'GradoAcademico' => '',
            'crearUsuario' => true,
            'email' => 'armando.mendoza@oaxaca.tecnm.mx',
            'email_confirmation' => 'armando.mendoza@oaxaca.tecnm.mx',
        ]);

        $controller = new personalController();

        $controller->nuevoPersonal($request);
    }

    public function test_Nuevo_personal_falla_por_no_colocar_apellidos()
    {
        $this->expectExceptionMessage('El campo apellidos es obligatorio');
        $this->expectException(ValidationException::class);
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);

        // Crear una solicitud simulada
        $request = Request::create('/personal', 'PUT', [
            'Nombre' => 'Juan',
            'Apellidos' => '',
            'Sexo' => 'Hombre',
            'Departamento' => $departamento->IdDepartamento,
            'tipoUsuario' => 'Administrador',
            'GradoAcademico' => '',
            'crearUsuario' => true,
            'email' => 'armando.mendoza@oaxaca.tecnm.mx',
            'email_confirmation' => 'armando.mendoza@oaxaca.tecnm.mx',
        ]);

        $controller = new personalController();

        $controller->nuevoPersonal($request);
    }
    public function test_Nuevo_personal_falla_por_correos_diferentes()
    {
        $this->expectExceptionMessage('La confirmación de correo electrónico no coincide.');
        $this->expectException(ValidationException::class);
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);

        // Crear una solicitud simulada
        $request = Request::create('/personal', 'PUT', [
            'Nombre' => 'Juan',
            'Apellidos' => 'Lopez',
            'Sexo' => 'Hombre',
            'Departamento' => ['IdDepartamento' => $departamento->IdDepartamento],
            'tipoUsuario' => 'Administrador',
            'GradoAcademico' => '',
            'crearUsuario' => true,
            'email' => 'armando.mendoza7@oaxaca.tecnm.mx',
            'email_confirmation' => 'armando.mendoza@oaxaca.tecnm.mx',
        ]);

        $controller = new personalController();

        $controller->nuevoPersonal($request);
    }
    public function test_Nuevo_personal_falla_por_correos_no_institucionales()
    {
        $this->expectExceptionMessage('El dominio debe ser de la institución (@itoaxaca.edu.mx o @oaxaca.tecnm.mx)');
        $this->expectException(ValidationException::class);
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);

        // Crear una solicitud simulada
        $request = Request::create('/personal', 'PUT', [
            'Nombre' => 'Juan',
            'Apellidos' => 'Lopez',
            'Sexo' => 'Hombre',
            'Departamento' => ['IdDepartamento' => $departamento->IdDepartamento],
            'tipoUsuario' => 'Administrador',
            'GradoAcademico' => '',
            'crearUsuario' => true,
            'email' => 'armando.mendoza@gmail.com',
            'email_confirmation' => 'armando.mendoza@gmail.com',
        ]);

        $controller = new personalController();

        $controller->nuevoPersonal($request);
    }
    public function test_Nuevo_personal_falla_por_correos_tomado_por_otro_usuario()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $docente = Docente::create([
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => '2024-01-02'
        ]);
        $this->expectExceptionMessage('El campo correo electrónico ya ha sido registrado.');
        $this->expectException(ValidationException::class);
        // Crear una solicitud simulada
        $request = Request::create('/personal', 'PUT', [
            'Nombre' => 'Juan',
            'Apellidos' => 'Lopez',
            'Sexo' => 'Hombre',
            'Departamento' => ['IdDepartamento' => $departamento->IdDepartamento],
            'tipoUsuario' => 'Administrador',
            'GradoAcademico' => '',
            'crearUsuario' => true,
            'email' => 'test@itoaxaca.edu.mx',
            'email_confirmation' => 'test@itoaxaca.edu.mx',
        ]);

        $controller = new personalController();

        $controller->nuevoPersonal($request);
    }
    /////////////////Pruebas de la funcion de editar personal//////////////////
    public function test_editar_personal_docente_correctamente()
    {
        Notification::fake();
        $departamentoNuevo = Departamento::create([
            'nombreDepartamento' => 'Quimica',
        ]);
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $docente = Docente::create([
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => '2024-01-02'
        ]);

        $request = Request::create(route('personal.editar'), 'POST', [
            'IdPersonal' => $personal->IdPersonal,
            'Nombre' => 'Ruby',
            'Apellidos' => 'Lopez',
            'Sexo' => 'Mujer',
            'Departamento' => ['IdDepartamento' => $departamentoNuevo->IdDepartamento],
            'Docente' => true,
            'GradoAcademico' => ['nombreGradoAcademico' => 'Doctorado'],
            'email' => $user->email,
            'email_confirmation' => $user->email,
        ]);

        $controller = new personalController();
        $controller->editarPersonal($request);

        $this->assertDatabaseHas('personal', [
            'IdPersonal' => $personal->IdPersonal,
            'Nombre' => 'Ruby',
            'Apellidos' => 'Lopez',
            'Sexo' => 'Mujer',
            'IdDepartamento' => $departamentoNuevo->IdDepartamento,
        ]);
        $this->assertDatabaseHas('docente', [
            'IdDocente' => $docente->IdDocente,
            'GradoAcademico' => 'Doctorado',
        ]);
    }
    public function test_editar_personal_secretaria_o_admin_con_correo_distinto_correctamente()
    {
        Notification::fake();
        $departamentoNuevo = Departamento::create([
            'nombreDepartamento' => 'Quimica',
        ]);
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
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => '2024-01-02'
        ]);

        $request = Request::create(route('personal.editar'), 'POST', [
            'IdPersonal' => $personal->IdPersonal,
            'Nombre' => 'Ruby',
            'Apellidos' => 'Lopez',
            'Sexo' => 'Mujer',
            'Departamento' => ['IdDepartamento' => $departamentoNuevo->IdDepartamento],
            'Docente' => false,
            'email' => 'nuevo.correo@oaxaca.tecnm.mx',
            'email_confirmation' => 'nuevo.correo@oaxaca.tecnm.mx',
        ]);

        $controller = new personalController();
        $controller->editarPersonal($request);

        $this->assertDatabaseHas('personal', [
            'IdPersonal' => $personal->IdPersonal,
            'Nombre' => 'Ruby',
            'Apellidos' => 'Lopez',
            'Sexo' => 'Mujer',
            'IdDepartamento' => $departamentoNuevo->IdDepartamento,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email_verified_at' => null,
        ]);
    }
    /////////////Pruebas de la funcion de validar personal ///////////////////////
    /**La funcion es usada por la funcion de editar personal, por lo que ya no es necesario realizar
     * pruebas de error para la anterior función ya que son las siguientes
     */
    public function test_validar_personal_falla_por_no_colocar_nombre()
    {
        $departamentoNuevo = Departamento::create([
            'nombreDepartamento' => 'Quimica',
        ]);
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => '2024-01-02'
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El campo nombre es obligatorio.');
        $request = Request::create(route('personal.editar'), 'POST', [
            'IdPersonal' => $personal->IdPersonal,
            'Nombre' => '',
            'Apellidos' => 'Lopez',
            'Sexo' => 'Mujer',
            'Departamento' => ['IdDepartamento' => $departamentoNuevo->IdDepartamento],
            'Docente' => true,
            'GradoAcademico' => 'Licenciatura',
            'email' => 'nuevo.correo2@oaxaca.tecnm.mx',
            'email_confirmation' => 'nuevo.correo2@oaxaca.tecnm.mx',
        ]);

        $controller = new personalController();
        $controller->validarPersonal($request);
    }
    public function test_validar_personal_falla_por_no_colocar_apellidos()
    {
        $departamentoNuevo = Departamento::create([
            'nombreDepartamento' => 'Quimica',
        ]);
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => '2024-01-02'
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El campo apellidos es obligatorio.');
        $request = Request::create(route('personal.editar'), 'POST', [
            'IdPersonal' => $personal->IdPersonal,
            'Nombre' => 'Karla',
            'Apellidos' => '',
            'Sexo' => 'Mujer',
            'Departamento' => ['IdDepartamento' => $departamentoNuevo->IdDepartamento],
            'Docente' => true,
            'GradoAcademico' => 'Licenciatura',
            'email' => 'nuevo.correo2@oaxaca.tecnm.mx',
            'email_confirmation' => 'nuevo.correo2@oaxaca.tecnm.mx',
        ]);

        $controller = new personalController();
        $controller->validarPersonal($request);
    }
    public function test_validar_personal_falla_por_no_colocar_grado_academico()
    {
        $departamentoNuevo = Departamento::create([
            'nombreDepartamento' => 'Quimica',
        ]);
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => '2024-01-02'
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El campo grado academico es obligatorio.');
        $request = Request::create(route('personal.editar'), 'POST', [
            'IdPersonal' => $personal->IdPersonal,
            'Nombre' => 'Karla',
            'Apellidos' => 'Guzman',
            'Sexo' => 'Mujer',
            'Departamento' => ['IdDepartamento' => $departamentoNuevo->IdDepartamento],
            'Docente' => true,
            'GradoAcademico' => '',
            'email' => 'nuevo.correo2@oaxaca.tecnm.mx',
            'email_confirmation' => 'nuevo.correo2@oaxaca.tecnm.mx',
        ]);

        $controller = new personalController();
        $controller->validarPersonal($request);
    }
    public function test_validar_personal_falla_por_correos_no_institucionales()
    {
        $departamentoNuevo = Departamento::create([
            'nombreDepartamento' => 'Quimica',
        ]);
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => '2024-01-02'
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El dominio debe ser de la institución (@itoaxaca.edu.mx o @oaxaca.tecnm.mx)');
        $request = Request::create(route('personal.editar'), 'POST', [
            'IdPersonal' => $personal->IdPersonal,
            'Nombre' => 'Ruby',
            'Apellidos' => 'Lopez',
            'Sexo' => 'Mujer',
            'Departamento' => ['IdDepartamento' => $departamentoNuevo->IdDepartamento],
            'Docente' => true,
            'GradoAcademico' => 'Licenciatura',
            'email' => 'nuevo.correo2@gmail.com',
            'email_confirmation' => 'nuevo.correo2@gmail.com',
        ]);

        $controller = new personalController();
        $controller->validarPersonal($request);
    }
    public function test_validar_personal_falla_por_confirmacion_de_correo_erronea()
    {
        $departamentoNuevo = Departamento::create([
            'nombreDepartamento' => 'Quimica',
        ]);
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => '2024-01-02'
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('La confirmación de correo electrónico no coincide.');
        $request = Request::create(route('personal.editar'), 'POST', [
            'IdPersonal' => $personal->IdPersonal,
            'Nombre' => 'Ruby',
            'Apellidos' => 'Lopez',
            'Sexo' => 'Mujer',
            'Departamento' => ['IdDepartamento' => $departamentoNuevo->IdDepartamento],
            'Docente' => false,
            'email' => 'nuevo.correo@oaxaca.tecnm.mx',
            'email_confirmation' => 'nuevo.correo7@oaxaca.tecnm.mx',
        ]);

        $controller = new personalController();
        $controller->validarPersonal($request);
    }
    public function test_validar_personal_falla_por_correo_ocupado_por_otro_usuario()
    {
        $departamentoNuevo = Departamento::create([
            'nombreDepartamento' => 'Quimica',
        ]);
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => '2024-01-02'
        ]);
        //Crear otro usuario para lanzar la validación
        $personal2 = Personal::create([
            'Nombre' => 'Jahir',
            'Apellidos' => 'Lopez',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user2 = User::create([
            'email' => 'jahir.lopez@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal2->IdPersonal,
            'email_verified_at' => '2024-01-02'
        ]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El campo correo electrónico ya ha sido tomado');
        $request = Request::create(route('personal.editar'), 'POST', [
            'IdPersonal' => $personal->IdPersonal,
            'Nombre' => 'Ruby',
            'Apellidos' => 'Lopez',
            'Sexo' => 'Mujer',
            'Departamento' => ['IdDepartamento' => $departamentoNuevo->IdDepartamento],
            'Docente' => true,
            'GradoAcademico' => 'Licenciatura',
            'email' => 'jahir.lopez@itoaxaca.edu.mx',
            'email_confirmation' => 'jahir.lopez@itoaxaca.edu.mx',
        ]);

        $controller = new personalController();
        $controller->validarPersonal($request);
    }
    //////////////PRuebas de la funcion de borrar personal/////////
    public function test_eliminar_personal_administrador()
    {
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
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => '2024-01-02'
        ]);

        $request = Request::create(route('personal.borrar'), 'DELETE', [
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $controller = new personalController();
        $controller->borrarPersonal($request);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'email' => 'test@itoaxaca.edu.mx',
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $this->assertDatabaseMissing('administrador', [
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $this->assertDatabaseMissing('personal', [
            'IdPersonal' => $personal->IdPersonal,
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
    }
    public function test_eliminar_personal_docente()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);

        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);

        $docente = Docente::create([
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);
        $expediente = expediente::create([
            'IdDocente' => $docente->IdDocente,
        ]);
        $request = Request::create(route('personal.borrar'), 'DELETE', [
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $controller = new personalController();
        $controller->borrarPersonal($request);

        $this->assertDatabaseMissing('docente', [
            'IdDocente' => $docente->IdDocente,
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);

        $this->assertDatabaseMissing('expediente', [
            'IdExpediente' => $expediente->IdExpediente,
            'IdDocente' => $docente->IdDocente,
        ]);

        $this->assertDatabaseMissing('personal', [
            'IdPersonal' => $personal->IdPersonal,
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
    }
    public function test_eliminar_personal_secretaria()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);

        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);

        $secretaria = Secretaria::create([
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $request = Request::create(route('personal.borrar'), 'DELETE', [
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $controller = new personalController();
        $controller->borrarPersonal($request);

        $this->assertDatabaseMissing('secretaria', [
            'IdSecretaria' => $secretaria->IdDocente,
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $this->assertDatabaseMissing('personal', [
            'IdPersonal' => $personal->IdPersonal,
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
    }
}
