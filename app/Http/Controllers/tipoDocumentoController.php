<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Docente;
use App\Models\Secretaria;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class tipoDocumentoController extends Controller
{
    /**
     * 
     */
    public function index(){
        $user = User::find(Auth::user()->id);
        $personal = $user->personal;
        if(Docente::where('IdPersonal', $personal->IdPersonal)->first() != null)
            return Redirect::route('dashboard');
        $tipoDocs = TipoDocumento::with('documento')
                    ->withCount('documento as numDocumentos')
                    ->get();
        if(Secretaria::where('IdPersonal', Auth::user()->IdPersonal)->first()!==null){
            return Inertia::render('Dashboard_secre_tipoDoc',['user'=>$user,'personal'=>$personal, 'tipoDocs'=>$tipoDocs]);
        }
        if (Administrador::where('IdPersonal', Auth::user()->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_admin_tipoDoc',['user'=>$user,'personal'=>$personal, 'tipoDocs'=>$tipoDocs]);
        }
    }
     /**
     * Crear un tipo de documento
     *
     * Valida los datos ingresados en la solicitud, si es exitosa, se creará en la base de datos
     * el nuevo registro en la tabla de tipo de documento. Despues redirecciona a la vista de tipo de documento
     * con un mensaje flash de la acción realizada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del nuevo tipo de documento.
     * 
     * @return Illuminate\Support\Facades\Redirect  Redirige a la vista de tipo de documentos con un mensaje de éxito.
     */
    public function nuevoTipoDoc(Request $request){
        $this->validacionTipoDoc($request);
        TipoDocumento::create([
            "nombreTipoDoc" => $request ->nombreTipoDoc,
        ]);
        return Redirect::route('tipoDoc')->with('creacionCorrecta', 'Tipo de documento creado correctamente');
    }
    /**
     * Edita un tipo de documento
     *
     * Busca el tipo de documento con el id que se proporciona en la solicitud para despues actualizar
     * los campos del registro en la base de datos. Despues redirecciona a la vista de tipo de documento
     * con un mensaje flash de la acción realizada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del tipo de documento a editar.
     * 
     * @return Illuminate\Support\Facades\Redirect  Redirige a la vista de tipo de documentos con un mensaje de éxito.
     */
    public function editarTipoDoc(Request $request){
        TipoDocumento::find($request->idtipoDoc)->update(
            ["nombreTipoDoc" => $request ->nombreTipoDoc,]
        );
        return Redirect::route('tipoDoc')->with('actualizacionCorrecta', 'Tipo de documento actualizado correctamente');
    }
    /**
     * Valida un tipo de documento
     *
     * Valida el tipo de documento a través del modelo y si la validación no es correcta se retornarán los errores
     * en la solicitud mandada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del tipo de documento a validar.
     */
    public function validacionTipoDoc(Request $request){
        $request->validate(TipoDocumento::$validacionTipoDocumento);
    }
    /**
     * Borra un tipo de documento
     *
     * Busca el tipo de documento con el id que se proporciona en la solicitud para despues borrar
     * el registro en la base de datos. Despues redirecciona a la vista de tipo de documento con
     * un mensaje flash de la acción realizada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del tipo de documento a borrar.
     * 
     * @return Illuminate\Support\Facades\Redirect  Redirige a la vista de tipo de documentos con un mensaje de éxito.
     */
    public function borrarTipoDoc(Request $request){
        TipoDocumento::find($request->idtipoDoc)->delete();
        return Redirect::route('tipoDoc')->with('borradoCorrecto', 'Tipo de documento borrado correctamente');
    }
}
