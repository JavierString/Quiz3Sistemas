<?php

namespace App\Models;

use Database\Factories\GrupoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['curso_id', 'numero_ciclo', 'ano', 'numero_grupo', 'abierto'])]
class Grupo extends Model
{
    /** @use HasFactory<GrupoFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'numero_ciclo' => 'integer',
            'ano' => 'integer',
            'numero_grupo' => 'integer',
            'abierto' => 'boolean',
        ];
    }

    /**
     * Curso al que pertenece el grupo.
     *
     * @return BelongsTo<Curso, $this>
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /**
     * Docentes que imparten el grupo (uno o dos).
     *
     * @return BelongsToMany<Docente, $this>
     */
    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'docente_grupo')->withTimestamps();
    }

    /**
     * Estudiantes matriculados en el grupo; la nota viaja en la matrícula.
     *
     * @return BelongsToMany<Estudiante, $this>
     */
    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, EstudianteMatriculado::class)
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