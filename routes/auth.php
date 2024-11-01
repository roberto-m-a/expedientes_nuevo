<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\ValidacionEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\departamentoController;
use App\Http\Controllers\documentController;
use App\Http\Controllers\expedienteController;
use App\Http\Controllers\periodoEscolarController;
use App\Http\Controllers\personalController;
use App\Http\Controllers\tipoDocumentoController;
use App\Http\Controllers\Auth\TwoFactorAutenticateController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    Route::get('/email/verify/{id}/{hash}', ValidacionEmailController::class)->name('verification.verify2');
    Route::get('/verificar-correo/{email}/{token}', [TwoFactorAutenticateController::class, 'verificarCorreo']);
    Route::get('/verificar-otp/{email}/{token}', [TwoFactorAutenticateController::class, 'verificarOTPassword']);
    Route::post('/login-otp', [TwoFactorAutenticateController::class, 'loginConOTPassword'])->name('login.otp');
    Route::post('/reenviar-verificacion-de-correo', [TwoFactorAutenticateController::class, 'regenerarTokenVerificarEmail'])->name('reenviar.verificacionemail');
    Route::post('/reenviar-OTP', [TwoFactorAutenticateController::class, 'regenerarOTP'])->name('reenviar.otp');
});


Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('completarInformacion', [PasswordController::class, 'completarInformacion'])->name('completar.informacion');
    //TIPO DOCUMENTO
    Route::post('tipoDoc', [tipoDocumentoController::class, 'nuevoTipoDoc'])->name('tipoDoc.nuevo');

    Route::post('tipoDoc/editar', [tipoDocumentoController::class, 'editarTipoDoc'])->name('tipoDoc.editar');

    Route::post('validacion/TipoDoc', [tipoDocumentoController::class, 'validacionTipoDoc'])->name('validar.tipoDoc');

    Route::post('borrar/tipoDoc', [tipoDocumentoController::class, 'borrarTipoDoc'])->name('tipoDoc.borrar');
    //DEPARTAMENTO
    Route::post('departamento', [departamentoController::class, 'nuevoDepartamento'])->name('departamento.nuevo');

    Route::post('departamento/editar', [departamentoController::class, 'editarDepartamento'])->name('departamento.editar');

    Route::post('validacion/departamento', [departamentoController::class, 'validarDepartamento'])->name('validar.departamento');

    Route::post('borrar/departamento', [departamentoController::class, 'borrarDepartamento'])->name('departamento.borrar');
    //PERIODO ESCOLAR
    Route::post('periodoEscolar', [periodoEscolarController::class, 'nuevoPeriodoEscolar'])->name('periodoEscolar.nuevo');

    Route::post('periodoEscolar/editar', [periodoEscolarController::class, 'editarPeriodoEscolar'])->name('periodoEscolar.editar');

    Route::post('validacion/periodoEscolar', [periodoEscolarController::class, 'validarPeriodoEscolar'])->name('validar.periodoEscolar');

    Route::post('borrar/periodoEscolar', [periodoEscolarController::class, 'borrarPeriodoEscolar'])->name('periodoEscolar.borrar');
    //PERSONAL
    Route::post('personal', [personalController::class, 'nuevoPersonal'])->name('personal.nuevo');

    Route::post('personal/editar', [personalController::class, 'editarPersonal'])->name('personal.editar');

    Route::post('personal/borrar', [personalController::class, 'borrarPersonal'])->name('personal.borrar');

    Route::post('validacion/personal', [personalController::class, 'validarPersonal'])->name('validar.personal');
    //EXPEDIENTE
    Route::get('expediente/{id}', [expedienteController::class, 'expedienteEspecifico'])->name('expediente.especifico');
    //DOCUMENTOS
    Route::post('registrarDoc', [documentController::class, 'nuevoDocumento'])->name('registrar.documento');

    Route::post('documento/editar', [documentController::class, 'editarDocumento'])->name('documento.editar');

    Route::post('validacion/documento', [documentController::class, 'validarDocumento'])->name('validar.documento');

    Route::post('documento/entregar', [documentController::class, 'entregarDocumento'])->name('entregar.documento');

    Route::post('validacion/entregaDocumento', [documentController::class, 'validarEntrega'])->name('validar.entrega');
    //USUARIO
    Route::post('validar/usuario', [RegisteredUserController::class, 'validarUsuario'])->name(('validar.usuario'));

    Route::post('aniadir/usuario', [RegisteredUserController::class, 'aniadirUsuario'])->name('aniadir.usuario');
    //DASHBOARD
    Route::post('dashboard', [DashboardController::class, 'filtrarConsulta'])->name('filtrar.consulta');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
