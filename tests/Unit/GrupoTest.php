<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;
use App\Models\Grupo;
use Illuminate\Database\Eloquent\Collection;

beforeEach(function () {
    $this->curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombre_curso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $this->grupo = Grupo::factory()->for($this->curso)->create([
        'numero_ciclo' => 2,
        'ano' => 2026,
        'numero_grupo' => 1,
        'abierto' => true,
    ]);
});

/**
 * Crea estudiantes con un mismo consejero y los matricula en el grupo, cada uno con su nota.
 *
 * @return Collection<int, Estudiante>
 */
function matricularEstudiantes(Grupo $grupo, Docente $consejero, int $cantidad)
{
    $estudiantes = Estudiante::factory()->count($cantidad)->for($consejero, 'consejero')->create();

    foreach ($estudiantes as $indice => $estudiante) {
        $grupo->estudiantes()->attach($estudiante->id, ['nota' => 60 + $indice]);
    }

    return $estudiantes;
}

test('un grupo guarda sus atributos propios', function () {
    $grupo = Grupo::find($this->grupo->id);

    expect($grupo->numero_ciclo)->toBe(2)
        ->and($grupo->ano)->toBe(2026)
        ->and($grupo->numero_grupo)->toBe(1)
        ->and($grupo->abierto)->toBeTrue();
});

test('un grupo pertenece a un curso', function () {
    $grupo = Grupo::find($this->grupo->id);

    expect($grupo->curso)->toBeInstanceOf(Curso::class)
        ->and($grupo->curso->sigla)->toBe('IF6100')
        ->and($grupo->curso->nombre_curso)->toBe('Análisis y Diseño de Sistemas')
        ->and($grupo->curso->creditos)->toBe(4);
});

test('un grupo puede ser impartido por un docente', function () {
    $docente = Docente::factory()->create();

    $this->grupo->docentes()->attach($docente);

    expect($this->grupo->docentes)->toHaveCount(1)
        ->and($this->grupo->docentes->first()->is($docente))->toBeTrue();
});

test('un grupo puede ser impartido por dos docentes', function () {
    $docentes = Docente::factory()->count(2)->create();

    $this->grupo->docentes()->attach($docentes->pluck('id'));

    expect($this->grupo->docentes)->toHaveCount(2)
        ->and($this->grupo->docentes->pluck('id')->all())->toEqualCanonicalizing($docentes->pluck('id')->all());
});

test('un grupo esta matriculado por al menos 15 estudiantes', function () {
    $consejero = Docente::factory()->create();
    matricularEstudiantes($this->grupo, $consejero, 15);

    expect($this->grupo->estudiantes)->toHaveCount(15)
        ->each->toBeInstanceOf(Estudiante::class);
});

test('cada estudiante matriculado en el grupo tiene registrada su nota', function () {
    $consejero = Docente::factory()->create();
    $estudiantes = matricularEstudiantes($this->grupo, $consejero, 15);

    $primero = $this->grupo->estudiantes->firstWhere('id', $estudiantes->first()->id);

    expect($primero->matricula)->toBeInstanceOf(EstudianteMatriculado::class)
        ->and($primero->matricula->nota)->toBe(60.0);

    expect($this->grupo->estudiantes->map(fn ($estudiante) => $estudiante->matricula->nota))
        ->each->toBeFloat();
});

test('cada estudiante del grupo tiene un unico profesor consejero', function () {
    $consejero = Docente::factory()->create();
    matricularEstudiantes($this->grupo, $consejero, 15);

    $this->grupo->estudiantes->each(function (Estudiante $estudiante) use ($consejero) {
        expect($estudiante->consejero)->toBeInstanceOf(Docente::class)
            ->and($estudiante->consejero->is($consejero))->toBeTrue();
    });
});

test('la instancia de un grupo contiene toda la informacion real asociada', function () {
    $docentes = Docente::factory()->count(2)->create();
    $this->grupo->docentes()->attach($docentes->pluck('id'));

    $consejero = Docente::factory()->create();
    matricularEstudiantes($this->grupo, $consejero, 15);

    $grupo = Grupo::with(['curso', 'docentes', 'estudiantes.consejero'])->find($this->grupo->id);

    expect($grupo->numero_ciclo)->toBe(2)
        ->and($grupo->ano)->toBe(2026)
        ->and($grupo->numero_grupo)->toBe(1)
        ->and($grupo->abierto)->toBeTrue()
        ->and($grupo->curso->sigla)->toBe('IF6100')
        ->and($grupo->docentes)->toHaveCount(2)
        ->and($grupo->estudiantes)->toHaveCount(15)
        ->and($grupo->estudiantes->every(fn ($estudiante) => $estudiante->consejero->is($consejero)))->toBeTrue()
        ->and($grupo->estudiantes->every(fn ($estudiante) => is_float($estudiante->matricula->nota)))->toBeTrue();
});