<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoDocumento extends Model
{
    protected $table = 'tipo_documento';
    protected $primaryKey = 'IdTipoDocumento';
    use HasFactory;
    protected $fillable = [
        'nombreTipoDoc',
    ];
    /**
     * Los atributos que se deben ocultar para la serialización.
     *
     * @var array< date, date>
     */
    protected $hidden = [
        'updated_at',
        'created_at',
    ];
    /**
     * Variable que valida la agregación o edición de un tipo de documento
     */
    public static $validacionTipoDocumento = [
        'nombreTipoDoc' => 'required|string|regex:/^[\pL\s]+$/u|max:100|unique:'.TipoDocumento::class,
    ];
    /**
     * Obten todos los documentos asociados a un tipo de documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documento(): HasMany
    {
        return $this->hasMany(document::class, 'IdTipoDocumento', 'IdTipoDocumento');
    }
}
