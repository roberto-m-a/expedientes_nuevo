<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class departamentoController extends Controller
{
    //
    public function index(){
        
        $user = User::find(Auth::user()->id);
        $personal = Personal::where('IdPersonal', Auth::user()->IdPersonal)->first();
        if(Docente::where('IdPersonal', $personal->IdPersonal)->first() != null)
            return Redirect::route('dashboard');
        $departamentos = Departamento::with('personal', 'documento')
                         ->withCount('personal as numPersonal')
                         ->withCount('documento as numDocumentos')
                         ->get();
        if(Secretaria::where('IdPersonal', Auth::user()->IdPersonal)->first()!==null){
            return Inertia::render('Dashboard_secre_departamento', ['user' => $user, 'personal' => $personal, 'departamentos' => $departamentos]);    
        }
        if (Administrador::where('IdPersonal', Auth::user()->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_admin_departamento',['user'=>$user,'personal'=>$personal, 'departamentos'=>$departamentos]);
        }
    }
    public function nuevoDepartamento(Request $request) {
        $request->validate([
            'nombreDepartamento' => 'required|string|max:150|unique:'.Departamento::class,
        ]);
            $tipoDoc = Departamento::create([
                "nombreDepartamento" => $request ->nombreDepartamento,
            ]);
            event(new Registered($tipoDoc));
            
            return Redirect::route('departamento');
    }

    public function editarDepartamento(Request $request){
        $request->validate([
            'nombreDepartamento' => 'required|string|max:150|unique:'.Departamento::class,
        ]);
        
        $departamento = Departamento::where('IdDepartamento', $request->idDepartamento)->first();
        $departamento->nombreDepartamento = $request->nombreDepartamento;
        $departamento->save();
    }

    public function validarDepartamento(Request $request){
        $request->validate([
            'nombreDepartamento' => 'required|string|max:150|unique:'.Departamento::class,
        ]);
    }

    public function borrarDepartamento(Request $request){
        $departamento = Departamento::where('IdDepartamento', $request->idDepartamento)->first();
        $departamento->delete();
    }
}