<?php

namespace App\Models;

use Database\Factories\GrupoFactory;
use DomainException;
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

    public const MINIMO_DOCENTES = 1;

    public const MAXIMO_DOCENTES = 2;

    public const MINIMO_ESTUDIANTES = 15;

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

    /**
     * Asigna un docente al grupo; un grupo admite como máximo dos.
     *
     * @param  Docente|int  $docente  Modelo del docente o su cod_empleado.
     *
     * @throws DomainException Si el docente ya está asignado o el grupo ya tiene dos docentes.
     */
    public function asignarDocente(Docente|int $docente): static
    {
        $docente = $docente instanceof Docente
            ? $docente
            : Docente::where('cod_empleado', $docente)->firstOrFail();

        if ($this->docentes()->where('docentes.id', $docente->id)->exists()) {
            throw new DomainException("El docente {$docente->cod_empleado} ya está asignado al grupo.");
        }

        if ($this->docentes()->count() >= self::MAXIMO_DOCENTES) {
            throw new DomainException('Un grupo admite como máximo '.self::MAXIMO_DOCENTES.' docentes.');
        }

        $this->docentes()->attach($docente->id);
        $this->unsetRelation('docentes');

        return $this;
    }

    /**
     * Matricula a un estudiante en el grupo con la nota vacía.
     *
     * @param  Estudiante|string  $estudiante  Modelo del estudiante o su carné.
     */
    public function matricular(Estudiante|string $estudiante): static
    {
        $estudiante = $estudiante instanceof Estudiante
            ? $estudiante
            : Estudiante::where('carne', $estudiante)->firstOrFail();

        $this->estudiantes()->attach($estudiante->id, ['nota' => null]);
        $this->unsetRelation('estudiantes');

        return $this;
    }

    /**
     * Un grupo puede abrirse con al menos un docente y quince estudiantes matriculados.
     */
    public function puedeAbrirse(): bool
    {
        return $this->docentes()->count() >= self::MINIMO_DOCENTES
            && $this->estudiantes()->count() >= self::MINIMO_ESTUDIANTES;
    }

    /**
     * Abre el grupo si cumple las condiciones de apertura.
     *
     * @throws DomainException Si el grupo no cumple las condiciones de apertura.
     */
    public function abrir(): static
    {
        if (! $this->puedeAbrirse()) {
            throw new DomainException(
                'El grupo necesita al menos '.self::MINIMO_DOCENTES.' docente y '
                .self::MINIMO_ESTUDIANTES.' estudiantes para abrirse.'
            );
        }

        $this->abierto = true;
        $this->save();

        return $this;
    }
}
