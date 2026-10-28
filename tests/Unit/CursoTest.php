<?php

use App\Models\Curso;
use App\Models\Grupo;

test('un curso guarda sigla, nombre y creditos', function () {
    $curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombre_curso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $curso = Curso::find($curso->id);

    expect($curso->sigla)->toBe('IF6100')
        ->and($curso->nombre_curso)->toBe('Análisis y Diseño de Sistemas')
        ->and($curso->creditos)->toBe(4);
});

test('un curso puede tener varios grupos abiertos', function () {
    $curso = Curso::factory()->create();

    Grupo::factory()->count(3)->for($curso)->create(['abierto' => true]);

    expect($curso->grupos)->toHaveCount(3)
        ->each->toBeInstanceOf(Grupo::class)
        ->and($curso->grupos->every(fn (Grupo $grupo) => $grupo->abierto))->toBeTrue();
});