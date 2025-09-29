<?php

namespace App\Livewire\Fleet;

use App\Models\Driver;
use App\Models\DriverEvaluation;
use App\Models\DriverSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app.sidebar', ['title' => 'Chofer'])]
#[Title('Chofer')]
class DriverForm extends Component
{
    public Driver $driver;
    public bool $isEdit = false;
    public array $schedules = [];
    public array $evaluations = [];

    protected function rules(): array
    {
        return [
            'driver.name' => ['required', 'string', 'max:100'],
            'driver.last_name' => ['required', 'string', 'max:100'],
            'driver.document_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('drivers', 'document_number')->ignore($this->driver->id),
            ],
            'driver.license_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('drivers', 'license_number')->ignore($this->driver->id),
            ],
            'driver.license_expiration' => ['required', 'date'],
            'driver.phone' => ['nullable', 'string', 'max:20'],
            'driver.email' => ['nullable', 'email', 'max:100'],
            'driver.address' => ['nullable', 'string', 'max:255'],
            'driver.status' => ['required', 'string', 'in:active,assigned,inactive,on_leave'],
            'driver.notes' => ['nullable', 'string'],
            'schedules' => ['array'],
            'schedules.*.day_of_week' => ['required', 'string', 'in:Lunes,Martes,Miercoles,Jueves,Viernes,Sabado,Domingo'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i'],
            'evaluations' => ['array'],
            'evaluations.*.score' => ['required', 'integer', 'min:1', 'max:5'],
            'evaluations.*.evaluated_at' => ['required', 'date'],
            'evaluations.*.evaluator' => ['nullable', 'string', 'max:100'],
            'evaluations.*.comments' => ['nullable', 'string'],
        ];
    }

    public function mount($id = null): void
    {
        if ($id) {
            $this->driver = Driver::with(['schedules', 'evaluations'])->findOrFail($id);
            $this->isEdit = true;
        } else {
            $this->driver = new Driver([
                'status' => 'active',
            ]);
        }

        $existingSchedules = $this->driver->exists ? $this->driver->schedules : collect();
        $existingEvaluations = $this->driver->exists ? $this->driver->evaluations : collect();

        $this->schedules = $existingSchedules->map(fn (DriverSchedule $schedule) => [
            'id' => $schedule->id,
            'day_of_week' => $schedule->day_of_week,
            'start_time' => optional($schedule->start_time)->format('H:i'),
            'end_time' => optional($schedule->end_time)->format('H:i'),
        ])->values()->toArray();

        $this->evaluations = $existingEvaluations->map(fn (DriverEvaluation $evaluation) => [
            'id' => $evaluation->id,
            'score' => $evaluation->score,
            'evaluated_at' => optional($evaluation->evaluated_at)->format('Y-m-d'),
            'evaluator' => $evaluation->evaluator,
            'comments' => $evaluation->comments,
        ])->values()->toArray();
    }

    public function addSchedule(): void
    {
        $this->schedules[] = [
            'day_of_week' => 'Lunes',
            'start_time' => '08:00',
            'end_time' => '18:00',
        ];
    }

    public function removeSchedule(int $index): void
    {
        unset($this->schedules[$index]);
        $this->schedules = array_values($this->schedules);
    }

    public function addEvaluation(): void
    {
        $this->evaluations[] = [
            'score' => 5,
            'evaluated_at' => now()->format('Y-m-d'),
            'evaluator' => null,
            'comments' => null,
        ];
    }

    public function removeEvaluation(int $index): void
    {
        unset($this->evaluations[$index]);
        $this->evaluations = array_values($this->evaluations);
    }

    public function save()
    {
        $validated = $this->validate();

        foreach ($this->schedules as $schedule) {
            if (isset($schedule['start_time'], $schedule['end_time']) && $schedule['start_time'] >= $schedule['end_time']) {
                $this->addError('schedules', 'La hora de inicio debe ser menor a la hora de fin en cada horario.');
                return;
            }
        }

        DB::transaction(function () use ($validated) {
            $this->driver->fill($validated['driver']);
            $this->driver->work_schedule = $this->schedules;
            $this->driver->save();

            $this->driver->schedules()->delete();
            $this->driver->evaluations()->delete();

            if (! empty($this->schedules)) {
                $this->driver->schedules()->createMany(array_map(fn ($schedule) => [
                    'day_of_week' => $schedule['day_of_week'],
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                ], $this->schedules));
            }

            if (! empty($this->evaluations)) {
                $this->driver->evaluations()->createMany(array_map(fn ($evaluation) => [
                    'score' => $evaluation['score'],
                    'evaluated_at' => $evaluation['evaluated_at'],
                    'evaluator' => $evaluation['evaluator'],
                    'comments' => $evaluation['comments'],
                ], $this->evaluations));
            }
        });

        session()->flash('message', $this->isEdit ? 'Chofer actualizado correctamente.' : 'Chofer registrado correctamente.');

        return redirect()->route('fleet.drivers.index');
    }

    public function render()
    {
        return view('livewire.fleet.driver-form');
    }
}
