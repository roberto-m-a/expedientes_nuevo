<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradoAcademico extends Model
{
    use HasFactory;
    protected $table = 'gradoacademico';
    protected $primaryKey = 'IdGradoAcademico';
    protected $fillable = [
        'nombreGradoAcademico',
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
}
