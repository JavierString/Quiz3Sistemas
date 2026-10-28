<?php

namespace App\Models;

use Database\Factories\DocenteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['cod_empleado', 'nombre_empleado', 'apellidos_empleado'])]
class Docente extends Model
{
    /** @use HasFactory<DocenteFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cod_empleado' => 'integer',
        ];
    }

    /**
     * Estudiantes de los que el docente es profesor consejero.
     *
     * @return HasMany<Estudiante, $this>
     */
    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'consejero_id');
    }

    /**
     * Grupos que imparte el docente.
     *
     * @return BelongsToMany<Grupo, $this>
     */
    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'docente_grupo')->withTimestamps();
    }
}