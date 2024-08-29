<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Docente;
use App\Models\PeriodoEscolar;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class periodoEscolarController extends Controller
{
    /**
     * 
     */
    public function index()
    {
        $user = User::find(Auth::user()->id);
        $personal = $user->personal;
        if (Docente::where('IdPersonal', $personal->IdPersonal)->first() != null)
            return Redirect::route('dashboard');

        $periodosEscolares = PeriodoEscolar::with('documento')
            ->withCount('documento as numDocumentos')
            ->get();
        if (Secretaria::where('IdPersonal', Auth::user()->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_secre_PeriodoEscolar', ['user' => $user, 'personal' => $personal, 'periodosEscolares' => $periodosEscolares]);
        }
        if (Administrador::where('IdPersonal', Auth::user()->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_admin_PeriodoEscolar', ['user' => $user, 'personal' => $personal, 'periodosEscolares' => $periodosEscolares]);
        }
    }
    /**
     * Crear un período escolar
     *
     * Valida los datos ingresados en la solicitud, si es exitosa, se creará en la base de datos
     * el nuevo registro en la tabla de período escolar. Despues redirecciona a la vista de período escolar
     * con un mensaje flash de la acción realizada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del nuevo período escolar.
     * 
     * @return Illuminate\Support\Facades\Redirect  Redirige a la vista de período escolars con un mensaje de éxito.
     */
    public function nuevoPeriodoEscolar(Request $request)
    {
        $this->validarPeriodoEscolar($request);
        PeriodoEscolar::create([
            'fechaInicio' => $request->fechaInicio,
            'fechaTermino' => $request->fechaTermino,
            'nombre_corto' => $request->nombre_corto,
        ]);
        return Redirect::route('periodoEscolar')->with('creacionCorrecta', 'Período escolar creado correctamente');
    }
    /**
     * Edita un período escolar
     *
     * Busca el período escolar con el id que se proporciona en la solicitud para despues actualizar
     * los campos del registro en la base de datos. Despues redirecciona a la vista de período escolar
     * con un mensaje flash de la acción realizada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del período escolar a editar.
     * 
     * @return Illuminate\Support\Facades\Redirect  Redirige a la vista de período escolars con un mensaje de éxito.
     */
    public function editarPeriodoEscolar(Request $request)
    {
        PeriodoEscolar::find($request->IdPeriodoEscolar)->update([
                'fechaInicio' => $request->fechaInicio,
                'fechaTermino' => $request->fechaTermino,
                'nombre_corto' => $request->nombre_corto,
        ]);
        return Redirect::route('periodoEscolar')->with('actualizacionCorrecta', 'Período escolar actualizado correctamente');
    }
    /**
     * Valida un período escolar
     *
     * Valida el período escolar a través del modelo y si la validación no es correcta se retornarán los errores
     * en la solicitud mandada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del período escolar a validar.
     */
    public function validarPeriodoEscolar(Request $request)
    {
        $request->validate(PeriodoEscolar::$validacionPeriodoEscolar);
    }
    /**
     * Borra un período escolar
     *
     * Busca el período escolar con el id que se proporciona en la solicitud para despues borrar
     * el registro en la base de datos. Despues redirecciona a la vista de período escolar con
     * un mensaje flash de la acción realizada.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP que contiene los datos del período escolar a borrar.
     * 
     * @return Illuminate\Support\Facades\Redirect  Redirige a la vista de período escolars con un mensaje de éxito.
     */
    public function borrarPeriodoEscolar(Request $request)
    {
        $periodoEscolar = PeriodoEscolar::where('IdPeriodoEscolar', $request->IdPeriodoEscolar);
        $periodoEscolar->delete();
        return Redirect::route('tipoDoc')->with('borradoCorrecto', 'Período escolar borrado correctamente');
    }
}
