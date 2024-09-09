<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Secretaria extends Model
{
    use HasFactory;
    protected $table = 'secretaria';
    protected $primaryKey ='IdSecretaria';
    protected $fillable = [
        'IdPersonal',
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
     * Obten el personal asociado a la secretaria
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function personal(): HasOne{
        return $this->hasOne(Personal::class, 'IdPersonal', 'IdPersonal');
    }
}
