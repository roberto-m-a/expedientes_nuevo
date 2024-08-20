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
     * Get the documento associated with the modificacion
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function documento(): HasOne
    {
        return $this->hasOne(document::class, 'IdDocumento', 'IdDocumento');
    }

    /**
     * Get the user associated with the modificacion
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'IdUsurio');
    }
}
