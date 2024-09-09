<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Departamento extends Model
{
    use HasFactory;
    
    protected $table = 'departamento';
    protected $primaryKey = 'IdDepartamento';
    protected $fillable = [
        'nombreDepartamento'
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
     * Variable que valida la agregación o edición de un nuevo departamento
     */
    public static $departamentoValidacion = [
        'nombreDepartamento' => 'required|string|regex:/^[\pL\s]+$/u|max:150|unique:'.Departamento::class,
    ];
    /**
     * Obten todos los documentos de un departamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documento(): HasMany
    {
        return $this->hasMany(document::class, 'IdDepartamento', 'IdDepartamento');
    }

    /**
     * Obten todo el personal de un departamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function personal(): HasMany
    {
        return $this->hasMany(Personal::class, 'IdDepartamento', 'IdDepartamento');
    }
}