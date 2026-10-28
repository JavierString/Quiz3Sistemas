<?php

namespace App\Models;

use Database\Factories\EstudianteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['carne', 'nombre', 'apellidos', 'consejero_id'])]
class Estudiante extends Model
{
    /** @use HasFactory<EstudianteFactory> */
    use HasFactory;

    /**
     * Cada estudiante tiene un único profesor consejero.
     *
     * @return BelongsTo<Docente, $this>
     */
    public function consejero(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'consejero_id');
    }

    /**
     * Grupos en los que el estudiante está matriculado; la nota viaja en la matrícula.
     *
     * @return BelongsToMany<Grupo, $this>
     */
    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, EstudianteMatriculado::class)
            ->using(EstudianteMatriculado::class)
            ->as('matricula')
            ->withPivot('nota')
            ->withTimestamps();
    }

    /**
     * @return HasMany<EstudianteMatriculado, $this>
     */
    public function matriculas(): HasMany
    {
        return $this->hasMany(EstudianteMatriculado::class);
    }
}