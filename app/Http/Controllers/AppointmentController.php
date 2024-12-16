<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AdditionalInfo;
use App\Models\Bed;
use App\Http\Requests\AppointmentRequest;
<<<<<<< HEAD
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Rules\CpfRule;
use App\Rules\ValidCpf;
=======
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Carbon\Carbon;
>>>>>>> Initial commit - Laravel backend

class AppointmentController extends Controller
{
    protected $genderMap = [
        'male' => 'Masculino',
        'female' => 'Feminino',
        'other' => 'Outro'
    ];

    public function index(): JsonResponse
    {
        $appointments = Appointment::with('additionalInfo')->get();
<<<<<<< HEAD
        foreach ($appointments as $appointment) {
            $appointment->photo = $appointment->photo ? url(Storage::url($appointment->photo)) : null;
            if ($appointment->additionalInfo) {
                $appointment->additionalInfo = $appointment->additionalInfo;
            }
        }
        return response()->json($appointments);
    }

    public function store(Request $request)
   {
    Log::info('Dados recebidos:', $request->all());
    
    $request->merge(['date' => $request->input('arrival_date')]);

    try {
        // Converte valores booleanos antes da validação
        $request->merge([
            'replace' => filter_var($request->input('replace'), FILTER_VALIDATE_BOOLEAN)
        ]);

        // Validação dos dados recebidos
        $validatedData = $request->validate([
            'cpf' => ['required', 'string', 'size:11', new ValidCpf()],
            'name' => 'required',
            'last_name' => 'required',
            'date' => 'required|date',
            'arrival_date' => 'required|date',
            'time' => 'required',
            'birth_date' => 'required|date',
            'state' => 'required',
            'city' => 'required',
            'mother_name' => 'required',
            'phone' => 'nullable',
            'observation' => 'nullable',
            'gender' => 'required',
            'foreign_country' => 'boolean',
            'noPhone' => 'boolean',
            'isHidden' => 'boolean',
            'replace' => 'boolean',
            'showMore' => 'boolean',
            'photo' => 'nullable|file|image|max:2048',
        ]);

        // Define a data atual para 'date'
        $validatedData['date'] = now()->format('Y-m-d');

        Log::info('Dados validados:', $validatedData);

        // Verifica se já existe um agendamento com o mesmo CPF
        $appointment = Appointment::where('cpf', $validatedData['cpf'])->first();

        if ($appointment) {
            Log::warning('CPF já existe no banco:', ['cpf' => $validatedData['cpf']]);

            return response()->json([
                'message' => 'Já existe um agendamento com este CPF!',
            ], 409); // Retorna erro 409 Conflict
        }

        // Lida com upload de foto se houver
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $validatedData['photo'] = $request->file('photo')->store('photos', 'public');
            Log::info('Foto salva com sucesso:', ['path' => $validatedData['photo']]);
        }

        // Cria um novo agendamento
        $validatedData['isHidden'] = false;
        $newAppointment = Appointment::create($validatedData);

        Log::info('Novo agendamento criado com sucesso:', $newAppointment->toArray());

        return response()->json([
            'message' => 'Agendamento realizado com sucesso!',
            'appointment' => $newAppointment,
        ], 201);

    } catch (\Exception $e) {
        Log::error('Erro ao processar o agendamento:', ['exception' => $e->getMessage()]);

        return response()->json([
            'message' => 'Erro ao processar o agendamento.',
            'error' => $e->getMessage(),
        ], 500);
    }
   }

   public function unhide($id): JsonResponse
   {
    try {
        $appointment = Appointment::findOrFail($id);
        $appointment->is_hidden = false;
        $appointment->save();

        return response()->json([
            'message' => 'Agendamento reativado com sucesso!',
            'appointment' => $appointment->load('additionalInfo')
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erro ao reativar o agendamento.',
            'error' => $e->getMessage()
        ], 500);
    }
  }

    public function show($id): JsonResponse
    {
        $appointment = Appointment::with('additionalInfo')->findOrFail($id);
        $appointment->photo = $appointment->photo ? url(Storage::url($appointment->photo)) : null;
        if ($appointment->additionalInfo) {
            $appointment->additionalInfo = $appointment->additionalInfo;
        }
        return response()->json($appointment);
    }

     public function destroy($id)
=======
        $appointments->transform(fn($appointment) => $this->transformAppointment($appointment));

        return response()->json($appointments);
    }

    public function store(AppointmentRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $validated['gender'] = $this->genderMap[$validated['gender']] ?? null;
            $validated['date'] = $validated['arrival_date'];

            if ($request->hasFile('photo')) {
                $validated['photo'] = $this->uploadPhoto($request);
            }

            $appointment = Appointment::create($validated);
            $this->handleAdditionalInfo($appointment, $validated['additionalInfo'] ?? null);

            DB::commit();
            return response()->json($appointment->load('additionalInfo'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar o agendamento.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao criar o agendamento.'], 500);
        }
    }

    public function update(AppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $validated['gender'] = $this->genderMap[$validated['gender']] ?? null;

            if ($request->hasFile('photo')) {
                $validated['photo'] = $this->uploadPhoto($request);
            }

            $this->releaseBed($appointment);
            $appointment->update($validated);
            $this->handleAdditionalInfo($appointment, $validated['additionalInfo'] ?? null);

            DB::commit();
            return response()->json($appointment->load('additionalInfo'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar o agendamento.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao atualizar o agendamento.'], 500);
        }
    }

    public function destroy($id): JsonResponse
>>>>>>> Initial commit - Laravel backend
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Agendamento não encontrado.'], 404);
        }

<<<<<<< HEAD
        $appointment->delete();

        return response()->json(['message' => 'Agendamento deletado com sucesso.'], 204);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        Log::info('Dados recebidos no update:', $request->all());

        // Atualiza os campos do agendamento
        $appointment->update($request->only([
            'name', 'last_name', 'cpf', 'gender', 'date', 'arrival_date',
            'phone', 'state', 'city', 'observation', 'isHidden'
        ]));
    
        // Verifica se additionalInfo foi enviado e atualiza
        if ($request->has('additionalInfo')) {
            $additionalInfoData = $request->input('additionalInfo');
            $additionalInfo = $appointment->additionalInfo;
    
            if ($additionalInfo) {
                $additionalInfo->update($additionalInfoData);
            } else {
                $appointment->additionalInfo()->create($additionalInfoData);
            }
        }
    
        return response()->json(['message' => 'Agendamento atualizado com sucesso']);
    }
    
    public function hide($id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $appointment = Appointment::with('additionalInfo')->findOrFail($id);
    
            $appointment->isHidden = true;
            $appointment->save();
    
=======
        DB::beginTransaction();
        try {
            $this->releaseBed($appointment);
            $appointment->delete();
            DB::commit();
            return response()->json(['message' => 'Agendamento excluído com sucesso.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir o agendamento.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao excluir o agendamento.'], 500);
        }
    }

    public function hide($id): JsonResponse
    {
        $appointment = Appointment::with('additionalInfo')->findOrFail($id);

        DB::beginTransaction();
        try {
            $appointment->isHidden = true;
            $appointment->save();

>>>>>>> Initial commit - Laravel backend
            if ($appointment->additionalInfo) {
                $bed = Bed::find($appointment->additionalInfo->bed_id);
                if ($bed) {
                    $bed->is_available = true;
                    $bed->save();
                }
                $appointment->additionalInfo->update([
                    'room_id' => null,
                    'bed_id' => null
                ]);
            }
<<<<<<< HEAD
    
=======

>>>>>>> Initial commit - Laravel backend
            DB::commit();
            Log::info('Agendamento ocultado com sucesso.', ['appointment_id' => $id]);
            return response()->json(['message' => 'Agendamento ocultado com sucesso.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
<<<<<<< HEAD
            Log::error('Erro ao ocultar o agendamento.', ['exception' => $e->getMessage()]);
=======
            Log::error('Erro ao ocultar o agendamento.', ['error' => $e->getMessage()]);
>>>>>>> Initial commit - Laravel backend
            return response()->json(['message' => 'Erro ao ocultar o agendamento.'], 500);
        }
    }

<<<<<<< HEAD
    public function getReports(Request $request)
  {
    try {
        $room = $request->input('room');
        $gender = $request->input('gender');
        $ageGroup = $request->input('ageGroup');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $turn = $request->input('turn');

        // Mapeamento de "A", "B", "C" para IDs numéricos
        $roomMapping = [
            'A' => 1,
            'B' => 2,
            'C' => 3,
        ];

        $query = Appointment::query();

        // Aplicar filtro por quarto com mapeamento
        if ($room && isset($roomMapping[$room])) {
            $roomId = $roomMapping[$room];
            $query->whereHas('additionalInfo', function ($q) use ($roomId) {
                $q->where('room_id', $roomId);
            });
        }

        // Filtro por gênero
        if ($gender) {
            $query->where('gender', $gender);
        }

       // Filtro por faixa etária
        if ($ageGroup) {
            $query->where(function ($q) use ($ageGroup) {
                switch ($ageGroup) {
                    case 'idosos':
                        $q->whereRaw("(strftime('%Y', 'now') - strftime('%Y', birth_date)) >= 60");
                        break;
                    case 'adultos':
                        $q->whereRaw("(strftime('%Y', 'now') - strftime('%Y', birth_date)) BETWEEN 18 AND 59");
                        break;
                }
            });
        }

        // Filtro por intervalo de datas
        if ($startDate && $endDate) {
            $query->whereBetween('arrival_date', [$startDate, $endDate]);
        }

        // Filtro por turno
        if ($turn) {
            $query->where(function ($q) use ($turn) {
                switch ($turn) {
                    case 'manha':
                        $q->whereBetween('time', ['06:00', '11:59']);
                        break;
                    case 'tarde':
                        $q->whereBetween('time', ['12:00', '17:59']);
                        break;
                    case 'noite':
                        $q->whereBetween('time', ['18:00', '23:59']);
                        break;
                    case 'madrugada':
                        $q->whereBetween('time', ['00:00', '05:59']);
                        break;
                }
            });
        }

        // Executa a consulta com os filtros aplicados
        $appointments = $query->select(
            'id',
            'name',
            'last_name',
            'cpf',
            'mother_name',
            'birth_date',
            'date',
            'time',
            'state',
            'city',
            'phone',
            'foreign_country',
            'no_phone',
            'gender',
            'arrival_date',
            'observation',
            'photo',
            'created_at',
            'updated_at',
            'isHidden'
        )->get();

        // Calcular bed_counts
        $bedCounts = [
            'A' => 0,
            'B' => 0,
            'C' => 0,
        ];

        foreach ($appointments as $appointment) {
            if ($appointment->additionalInfo) {
                $roomId = $appointment->additionalInfo->room_id;
                $roomMapping = [
                    1 => 'A',
                    2 => 'B',
                    3 => 'C',
                ];

                if (isset($roomMapping[$roomId])) {
                    $mappedRoom = $roomMapping[$roomId];
                    $bedCounts[$mappedRoom]++;
                }
            }
        }

        // Calcular faixas etárias
        $ageCounts = [
            'Idosos (60+)' => 0,
            'Adultos (18-59)' => 0,
        ];

        foreach ($appointments as $appointment) {
            if ($appointment->birth_date) {
                $age = \Carbon\Carbon::parse($appointment->birth_date)->age; // Calcula a idade
                if ($age >= 60) {
                    $ageCounts['Idosos (60+)']++;
                } elseif ($age >= 18) {
                    $ageCounts['Adultos (18-59)']++;
                }
            }
        }

        // Retorno ajustado
        return response()->json([
            'appointments' => $appointments->values(),
            'gender_counts' => $appointments->groupBy('gender')->map(function ($group, $gender) {
                return [
                    'gender' => $gender,
                    'count' => $group->count(),
                ];
            })->values(),
            'age_counts' => collect($ageCounts)->map(function ($count, $group) {
                return [
                    'group' => $group,
                    'count' => $count,
                ];
            })->values(),
            'time_data' => $appointments->pluck('time')->filter()->toArray(),
            'bed_counts' => $bedCounts, // Inclui bed_counts na resposta
        ]);
    } catch (\Exception $e) {
        \Log::error('Erro ao gerar relatórios: ' . $e->getMessage());
        return response()->json([
            'error' => 'Erro ao processar os relatórios',
            'details' => $e->getMessage(),
        ], 500);
    }
  }

  public function updateAppointment(Request $request, $id)
  {
      $appointment = Appointment::find($id);
  
      if ($appointment) {
          $appointment->fill($request->all());
          $appointment->save();
  
          return response()->json([
              'message' => 'Agendamento atualizado com sucesso',
              'appointment' => $appointment
          ], 200);
      }
  
      return response()->json(['message' => 'Agendamento não encontrado'], 404);
  }
  

  public function saveOrUpdateAppointment(Request $request) {
    $cpf = $request->input('cpf');
    $appointment = Appointment::where('cpf', $cpf)->first();

    if ($appointment) {
        // Atualiza o registro existente
        $appointment->update([
            'name' => $request->input('name'),
            'last_name' => $request->input('last_name'),
            'arrival_date' => $request->input('arrival_date'),
            'isHidden' => false, // Marca como visível
        ]);
    } else {
        // Cria um novo registro
        Appointment::create($request->all());
    } 

    return response()->json(['success' => true]);
   }

  public function hideAppointment(Request $request, $id)
   {
        $appointment = Appointment::findOrFail($id);

        $appointment->isHidden = $request->input('isHidden', true);
        $appointment->additionalInfo = array_merge($appointment->additionalInfo, [
            'room_id' => null,
            'bed_id' => null,
        ]);

        $appointment->save();

        return response()->json(['message' => 'Acolhimento atualizado e vaga liberada com sucesso.']);
    }

}
=======
    public function getReports(Request $request): JsonResponse
    {
        try {
            $room = $request->input('room');
            $gender = $request->input('gender');
            $ageGroup = $request->input('ageGroup');
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');
            $turn = $request->input('turn');

            $roomMapping = ['A' => 1, 'B' => 2, 'C' => 3];

            $query = Appointment::query();

            if ($room && isset($roomMapping[$room])) {
                $query->whereHas('additionalInfo', fn($q) => $q->where('room_id', $roomMapping[$room]));
            }

            if ($gender) {
                $query->where('gender', $gender);
            }

            if ($ageGroup) {
                $query->where(function ($q) use ($ageGroup) {
                    if ($ageGroup === 'idosos') {
                        $q->whereRaw("(strftime('%Y', 'now') - strftime('%Y', birth_date)) >= 60");
                    } elseif ($ageGroup === 'adultos') {
                        $q->whereRaw("(strftime('%Y', 'now') - strftime('%Y', birth_date)) BETWEEN 18 AND 59");
                    }
                });
            }

            if ($startDate && $endDate) {
                $query->whereBetween('arrival_date', [$startDate, $endDate]);
            }

            if ($turn) {
                $query->where(function ($q) use ($turn) {
                    switch ($turn) {
                        case 'manha':
                            $q->whereBetween('time', ['06:00', '11:59']);
                            break;
                        case 'tarde':
                            $q->whereBetween('time', ['12:00', '17:59']);
                            break;
                        case 'noite':
                            $q->whereBetween('time', ['18:00', '23:59']);
                            break;
                        case 'madrugada':
                            $q->whereBetween('time', ['00:00', '05:59']);
                            break;
                    }
                });
            }

            $appointments = $query->with('additionalInfo')->get();

            return response()->json([
                'appointments' => $appointments,
                'gender_counts' => $appointments->groupBy('gender')->map->count(),
                'bed_counts' => $this->calculateBedCounts($appointments),
                'age_counts' => $this->calculateAgeCounts($appointments),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatórios.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao processar os relatórios.'], 500);
        }
    }

    private function calculateBedCounts($appointments): array
    {
        $bedCounts = ['A' => 0, 'B' => 0, 'C' => 0];
        foreach ($appointments as $appointment) {
            if ($appointment->additionalInfo) {
                $room = ['1' => 'A', '2' => 'B', '3' => 'C'][$appointment->additionalInfo->room_id] ?? null;
                if ($room) $bedCounts[$room]++;
            }
        }
        return $bedCounts;
    }

    private function calculateAgeCounts($appointments): array
    {
        $ageCounts = ['Idosos (60+)' => 0, 'Adultos (18-59)' => 0];
        foreach ($appointments as $appointment) {
            $age = $appointment->birth_date ? Carbon::parse($appointment->birth_date)->age : null;
            if ($age >= 60) {
                $ageCounts['Idosos (60+)']++;
            } elseif ($age >= 18) {
                $ageCounts['Adultos (18-59)']++;
            }
        }
        return $ageCounts;
    }

    private function uploadPhoto($request): string
    {
        return $request->file('photo')->store('photos', 'public');
    }

    private function releaseBed(Appointment $appointment): void
    {
        if ($appointment->additionalInfo) {
            $bed = Bed::find($appointment->additionalInfo->bed_id);
            if ($bed) {
                $bed->is_available = true;
                $bed->save();
            }
        }
    }

    private function handleAdditionalInfo(Appointment $appointment, ?array $data): void
    {
        if ($data) {
            $bed = Bed::find($data['bed_id']);
            if (!$bed || !$bed->is_available) {
                throw new \Exception('A cama selecionada não está disponível.');
            }

            $bed->is_available = false;
            $bed->save();

            AdditionalInfo::updateOrCreate(
                ['appointment_id' => $appointment->id],
                $data
            );
        }
    }

    private function transformAppointment($appointment)
    {
        $appointment->photo = $appointment->photo ? url(Storage::url($appointment->photo)) : null;
        return $appointment;
    }
}
>>>>>>> Initial commit - Laravel backend
