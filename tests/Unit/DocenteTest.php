<?php

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;

test('un docente guarda codigo, nombre y apellidos', function () {
    $docente = Docente::factory()->create([
        'cod_empleado' => 12345,
        'nombre_empleado' => 'María',
        'apellidos_empleado' => 'Rojas Vargas',
    ]);

    $docente = Docente::find($docente->id);

    expect($docente->cod_empleado)->toBe(12345)
        ->and($docente->nombre_empleado)->toBe('María')
        ->and($docente->apellidos_empleado)->toBe('Rojas Vargas');
});

test('un docente puede ser consejero de varios estudiantes', function () {
    $docente = Docente::factory()->create();

    Estudiante::factory()->count(3)->for($docente, 'consejero')->create();

    expect($docente->estudiantes)->toHaveCount(3)
        ->each->toBeInstanceOf(Estudiante::class);
});

test('un docente puede impartir varios grupos', function () {
    $docente = Docente::factory()->create();
    $grupos = Grupo::factory()->count(2)->create();

    $docente->grupos()->attach($grupos->pluck('id'));

    expect($docente->grupos)->toHaveCount(2)
        ->each->toBeInstanceOf(Grupo::class);
});