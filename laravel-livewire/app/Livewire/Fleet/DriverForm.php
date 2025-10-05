<?php

namespace App\Livewire\Fleet;

use App\Models\Driver;
use App\Models\DriverEvaluation;
use App\Models\DriverSchedule;
use App\Models\DriverTraining;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app.sidebar', ['title' => 'Chofer'])]
#[Title('Chofer')]
class DriverForm extends Component
{
    use AuthorizesRequests;

    public Driver $driver;
    public bool $isEdit = false;
    public array $form = [];
    public array $schedules = [];
    public array $evaluations = [];
    public array $trainings = [];

    protected function rules(): array
    {
        return [
            // Datos basicos del chofer.
            'form.name' => ['required', 'string', 'max:100'],
            'form.last_name' => ['required', 'string', 'max:100'],
            'form.document_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('drivers', 'document_number')->ignore($this->driver->id),
            ],
            'form.license_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('drivers', 'license_number')->ignore($this->driver->id),
            ],
            'form.license_expiration' => ['required', 'date'],
            'form.phone' => ['nullable', 'string', 'max:20'],
            'form.email' => ['nullable', 'email', 'max:100'],
            'form.address' => ['nullable', 'string', 'max:255'],
            'form.status' => ['required', 'string', 'in:active,assigned,inactive,on_leave'],
            'form.notes' => ['nullable', 'string'],
            'schedules' => ['array'],
            'schedules.*.day_of_week' => ['required', 'string', 'in:Lunes,Martes,Miercoles,Jueves,Viernes,Sabado,Domingo'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i'],
            'evaluations' => ['array'],
            'evaluations.*.score' => ['required', 'integer', 'min:1', 'max:5'],
            'evaluations.*.evaluated_at' => ['required', 'date'],
            'evaluations.*.evaluator' => ['nullable', 'string', 'max:100'],
            'evaluations.*.comments' => ['nullable', 'string'],
            'trainings' => ['array'],
            'trainings.*.name' => ['required', 'string', 'max:150'],
            'trainings.*.provider' => ['nullable', 'string', 'max:150'],
            'trainings.*.issued_at' => ['nullable', 'date'],
            'trainings.*.expires_at' => ['nullable', 'date', 'after_or_equal:trainings.*.issued_at'],
            'trainings.*.hours' => ['nullable', 'integer', 'min:0', 'max:200'],
            'trainings.*.status' => ['required', 'string', 'in:valid,expired,in_progress'],
            'trainings.*.certificate_url' => ['nullable', 'url'],
        ];
    }

    public function mount(?Driver $driver = null): void
    {
        if ($driver && $driver->exists) {
            $this->driver = $driver->load(['schedules', 'evaluations', 'trainings']);
            $this->authorize('update', $this->driver);
            $this->isEdit = true;
        } else {
            $this->authorize('create', Driver::class);
            $this->driver = new Driver([
                'status' => 'active',
            ]);
        }

        $this->form = [
            'name' => $this->driver->name ?? '',
            'last_name' => $this->driver->last_name ?? '',
            'document_number' => $this->driver->document_number ?? '',
            'license_number' => $this->driver->license_number ?? '',
            'license_expiration' => optional($this->driver->license_expiration)->format('Y-m-d'),
            'phone' => $this->driver->phone ?? '',
            'email' => $this->driver->email ?? '',
            'address' => $this->driver->address ?? '',
            'status' => $this->driver->status ?? 'active',
            'notes' => $this->driver->notes ?? '',
        ];

        $existingSchedules = $this->driver->exists ? $this->driver->schedules : collect();
        $existingEvaluations = $this->driver->exists ? $this->driver->evaluations : collect();
        $existingTrainings = $this->driver->exists ? $this->driver->trainings : collect();

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

        $this->trainings = $existingTrainings->map(fn (DriverTraining $training) => [
            'id' => $training->id,
            'name' => $training->name,
            'provider' => $training->provider,
            'issued_at' => optional($training->issued_at)->format('Y-m-d'),
            'expires_at' => optional($training->expires_at)->format('Y-m-d'),
            'hours' => $training->hours,
            'status' => $training->status,
            'certificate_url' => $training->certificate_url,
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

    public function addTraining(): void
    {
        $this->trainings[] = [
            'name' => 'Capacitación',
            'provider' => null,
            'issued_at' => now()->format('Y-m-d'),
            'expires_at' => now()->addYear()->format('Y-m-d'),
            'hours' => 8,
            'status' => 'valid',
            'certificate_url' => null,
        ];
    }

    public function removeTraining(int $index): void
    {
        unset($this->trainings[$index]);
        $this->trainings = array_values($this->trainings);
    }

    public function save()
    {
        $this->authorize($this->isEdit ? 'update' : 'create', $this->isEdit ? $this->driver : Driver::class);

        $validated = $this->validate();
        $data = $validated['form'];

        $data['phone'] = trim((string) $data['phone']) ?: null;
        $data['email'] = trim((string) $data['email']) ?: null;
        $data['address'] = trim((string) $data['address']) ?: null;
        $data['notes'] = trim((string) $data['notes']) ?: null;

        foreach ($this->schedules as $schedule) {
            if (isset($schedule['start_time'], $schedule['end_time']) && $schedule['start_time'] >= $schedule['end_time']) {
                $this->addError('schedules', 'La hora de inicio debe ser menor a la hora de fin en cada horario.');
                return;
            }
        }

        DB::transaction(function () use ($data) {
            // Persistimos los datos principales y reseteamos colecciones dependientes.
            $this->driver->fill($data);
            $this->driver->work_schedule = $this->schedules;
            $this->driver->save();

            $this->driver->schedules()->delete();
            $this->driver->evaluations()->delete();
            $this->driver->trainings()->delete();

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

            if (! empty($this->trainings)) {
                $this->driver->trainings()->createMany(array_map(fn ($training) => [
                    'name' => $training['name'],
                    'provider' => $training['provider'],
                    'issued_at' => $training['issued_at'] ?: null,
                    'expires_at' => $training['expires_at'] ?: null,
                    'hours' => $training['hours'] ?? null,
                    'status' => $training['status'],
                    'certificate_url' => $training['certificate_url'] ?? null,
                ], $this->trainings));
            }
        });

        session()->flash('message', $this->isEdit ? 'Chofer actualizado correctamente.' : 'Chofer registrado correctamente.');

        return redirect()->route('fleet.drivers.index');
    }

    public function render()
    {
        $this->authorize('viewAny', Driver::class);

        return view('livewire.fleet.driver-form');
    }
}
