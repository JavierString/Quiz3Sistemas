<?php

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Database\QueryException;

test('un estudiante guarda carne, nombre y apellidos', function () {
    $estudiante = Estudiante::factory()->create([
        'carne' => 'B12345',
        'nombre' => 'Javier',
        'apellidos' => 'Mora Solís',
    ]);

    $estudiante = Estudiante::find($estudiante->id);

    expect($estudiante->carne)->toBe('B12345')
        ->and($estudiante->nombre)->toBe('Javier')
        ->and($estudiante->apellidos)->toBe('Mora Solís');
});

test('un estudiante tiene un unico profesor consejero', function () {
    $consejero = Docente::factory()->create();
    $estudiante = Estudiante::factory()->for($consejero, 'consejero')->create();

    expect($estudiante->consejero)->toBeInstanceOf(Docente::class)
        ->and($estudiante->consejero->is($consejero))->toBeTrue();
});

test('un estudiante no puede existir sin profesor consejero', function () {
    Estudiante::create([
        'carne' => 'B99999',
        'nombre' => 'Sin',
        'apellidos' => 'Consejero',
    ]);
})->throws(QueryException::class);

test('un estudiante puede matricularse en varios grupos con su nota', function () {
    $estudiante = Estudiante::factory()->create();
    $grupos = Grupo::factory()->count(2)->create();

    $estudiante->grupos()->attach($grupos->first()->id, ['nota' => 85.5]);
    $estudiante->grupos()->attach($grupos->last()->id, ['nota' => 70]);

    expect($estudiante->grupos)->toHaveCount(2)
        ->and($estudiante->grupos->firstWhere('id', $grupos->first()->id)->matricula->nota)->toBe(85.5);
});