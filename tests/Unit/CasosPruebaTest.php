<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombre_curso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);
});

/**
 * Crea el grupo 01 del curso (ciclo 2, año 2026) con el estado de apertura indicado.
 */
function crearGrupoUno(Curso $curso, bool $abierto): Grupo
{
    return Grupo::factory()->for($curso)->create([
        'numero_ciclo' => 2,
        'ano' => 2026,
        'numero_grupo' => 1,
        'abierto' => $abierto,
    ]);
}

/**
 * Crea uno de los docentes fijos de los datos de prueba (1001, 1002 o 1003).
 */
function crearDocenteFijo(int $codEmpleado): Docente
{
    $datos = [
        1001 => ['Ana', 'Mora Vargas'],
        1002 => ['Luis', 'Rojas Solano'],
        1003 => ['Carla', 'Jiménez Araya'],
    ][$codEmpleado];

    return Docente::factory()->create([
        'cod_empleado' => $codEmpleado,
        'nombre_empleado' => $datos[0],
        'apellidos_empleado' => $datos[1],
    ]);
}

/**
 * Genera estudiantes con factory y los matricula en el grupo.
 */
function matricularGenerados(Grupo $grupo, int $cantidad): void
{
    Estudiante::factory()
        ->count($cantidad)
        ->create()
        ->each(fn (Estudiante $estudiante) => $grupo->matricular($estudiante));
}

test('CP-01 registra un grupo con toda su informacion y su curso', function () {
    $creado = crearGrupoUno($this->curso, abierto: true);

    $grupo = Grupo::find($creado->id);

    expect($grupo->numero_ciclo)->toBe(2)
        ->and($grupo->ano)->toBe(2026)
        ->and($grupo->numero_grupo)->toBe(1)
        ->and($grupo->abierto)->toBeTrue()
        ->and($grupo->curso->sigla)->toBe('IF6100')
        ->and($grupo->curso->creditos)->toBe(4)
        ->and(Grupo::count())->toBe(1);
});

test('CP-02 un curso tiene varios grupos y se distinguen los abiertos', function () {
    foreach ([1 => true, 2 => true, 3 => false] as $numeroGrupo => $abierto) {
        Grupo::factory()->for($this->curso)->create([
            'numero_ciclo' => 2,
            'ano' => 2026,
            'numero_grupo' => $numeroGrupo,
            'abierto' => $abierto,
        ]);
    }

    $abiertos = $this->curso->grupos->where('abierto', true);

    expect($this->curso->grupos)->toHaveCount(3)
        ->and($abiertos)->toHaveCount(2)
        ->and($abiertos->pluck('numero_grupo')->sort()->values()->all())->toBe([1, 2])
        ->and(Grupo::where('curso_id', $this->curso->id)->count())->toBe(3);
});

test('CP-03 un grupo acepta dos docentes', function () {
    $grupo = crearGrupoUno($this->curso, abierto: false);
    $docente = crearDocenteFijo(1001);
    crearDocenteFijo(1002);

    $grupo->asignarDocente(1001);
    $grupo->asignarDocente(1002);

    expect($grupo->docentes->pluck('cod_empleado')->sort()->values()->all())->toBe([1001, 1002])
        ->and($docente->grupos->contains($grupo))->toBeTrue()
        ->and(DB::table('docente_grupo')->count())->toBe(2);
});

test('CP-04 un grupo rechaza un tercer docente', function () {
    $grupo = crearGrupoUno($this->curso, abierto: false);
    crearDocenteFijo(1001);
    crearDocenteFijo(1002);
    $tercero = crearDocenteFijo(1003);
    $grupo->asignarDocente(1001);
    $grupo->asignarDocente(1002);

    expect(fn () => $grupo->asignarDocente(1003))->toThrow(DomainException::class);

    expect($grupo->docentes()->count())->toBe(2)
        ->and(DB::table('docente_grupo')->where('docente_id', $tercero->id)->exists())->toBeFalse();
});

test('CP-05 un grupo con 14 estudiantes no puede abrirse', function () {
    $grupo = crearGrupoUno($this->curso, abierto: false);
    crearDocenteFijo(1001);
    $grupo->asignarDocente(1001);
    matricularGenerados($grupo, 14);

    expect($grupo->puedeAbrirse())->toBeFalse();
    expect(fn () => $grupo->abrir())->toThrow(DomainException::class);
    expect($grupo->fresh()->abierto)->toBeFalse();
});

test('CP-06 un grupo con 15 estudiantes y un docente se abre', function () {
    $grupo = crearGrupoUno($this->curso, abierto: false);
    crearDocenteFijo(1001);
    $grupo->asignarDocente(1001);
    matricularGenerados($grupo, 15);

    expect($grupo->puedeAbrirse())->toBeTrue();

    $grupo->abrir();

    expect($grupo->fresh()->abierto)->toBeTrue();
});
