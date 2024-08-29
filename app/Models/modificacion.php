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

    /**
     * Obten el documento asociado a la modificacion
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function documento(): HasOne
    {
        return $this->hasOne(document::class, 'IdDocumento', 'IdDocumento');
    }

    /**
     * Obten el usuario asociado a la modificacion
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'IdUsurio');
    }
}
