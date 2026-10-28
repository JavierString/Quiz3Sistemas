<?php

use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;
use App\Models\Grupo;
use Illuminate\Database\QueryException;

test('una matricula une a un estudiante con un grupo y guarda la nota', function () {
    $estudiante = Estudiante::factory()->create();
    $grupo = Grupo::factory()->create();

    $matricula = EstudianteMatriculado::create([
        'estudiante_id' => $estudiante->id,
        'grupo_id' => $grupo->id,
        'nota' => 92.5,
    ]);

    $matricula = EstudianteMatriculado::find($matricula->id);

    expect($matricula->estudiante->is($estudiante))->toBeTrue()
        ->and($matricula->grupo->is($grupo))->toBeTrue()
        ->and($matricula->nota)->toBe(92.5);
});

test('la nota de una matricula queda vacia hasta que finaliza el curso', function () {
    $matricula = EstudianteMatriculado::factory()->create(['nota' => null]);

    expect(EstudianteMatriculado::find($matricula->id)->nota)->toBeNull();
});

test('un estudiante no puede matricularse dos veces en el mismo grupo', function () {
    $estudiante = Estudiante::factory()->create();
    $grupo = Grupo::factory()->create();

    EstudianteMatriculado::create(['estudiante_id' => $estudiante->id, 'grupo_id' => $grupo->id]);
    EstudianteMatriculado::create(['estudiante_id' => $estudiante->id, 'grupo_id' => $grupo->id]);
})->throws(QueryException::class);