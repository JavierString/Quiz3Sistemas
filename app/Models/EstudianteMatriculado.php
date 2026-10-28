<?php

namespace App\Models;

use Database\Factories\EstudianteMatriculadoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Clase de asociación entre Grupo y Estudiante: guarda la nota final del estudiante.
 */
#[Fillable(['grupo_id', 'estudiante_id', 'nota'])]
class EstudianteMatriculado extends Pivot
{
    /** @use HasFactory<EstudianteMatriculadoFactory> */
    use HasFactory;

    public $incrementing = true;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nota' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Grupo, $this>
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    /**
     * @return BelongsTo<Estudiante, $this>
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
}