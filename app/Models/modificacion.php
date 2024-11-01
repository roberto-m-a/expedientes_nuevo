<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class modificacion extends Model
{
    protected $table = "modificacion";
    protected $primaryKey = 'IdModificacion';
    protected $fillable = [
        'Nombre',
        'Apellidos',
        'Titulo',
        'Modificaciones',
        'IdDocumento',
        'IdUsuario',

    ];
}
