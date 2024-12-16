<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AdditionalInfo;
use App\Models\Bed;
use App\Http\Requests\AppointmentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
        foreach ($appointments as $appointment) {
            $appointment->photo = $appointment->photo ? url(Storage::url($appointment->photo)) : null;
            if ($appointment->additionalInfo) {
                $appointment->additionalInfo = $appointment->additionalInfo;
            }
        }
        return response()->json($appointments);
    }

    public function store(AppointmentRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            \Log::info('Iniciando criação de agendamento.', ['request' => $request->all()]);
            
            $validatedData = $request->validated();
            $validatedData['gender'] = $this->genderMap[$request->gender] ?? null;
    
            // Adicione o valor para o campo `date` se necessário
            $validatedData['date'] = $request->input('arrival_date'); // ou outro campo apropriado
    
            \Log::info('Dados validados com sucesso.', ['validatedData' => $validatedData]);
    
            // Verifique se o CPF já existe
            if (Appointment::where('cpf', $validatedData['cpf'])->exists()) {
                return response()->json(['message' => 'CPF já utilizado para uma reserva. Por favor, utilize outro CPF.'], 409);
            }
    
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                try {
                    $photoPath = $request->file('photo')->store('photos', 'public');
                    $validatedData['photo'] = $photoPath;
                } catch (\Exception $e) {
                    return response()->json(['message' => 'Erro ao fazer upload da foto.'], 500);
                }
            }
    
            // Crie o agendamento
            $appointment = Appointment::create($validatedData);
    
            if ($request->has('additionalInfo')) {
                $additionalInfoData = $request->input('additionalInfo');
                $additionalInfoData['appointment_id'] = $appointment->id;
    
                // Inclua o campo de duração da estadia
                $additionalInfoData['stay_duration'] = $additionalInfoData['stay_duration'] ?? null;
    
                // Verifique se a cama está disponível
                $bed = Bed::find($additionalInfoData['bed_id']);
                if (!$bed || !$bed->is_available) {
                    return response()->json(['message' => 'A cama selecionada não está disponível.'], 422);
                }
    
                // Marque a cama como ocupada
                $bed->is_available = false;
                $bed->save();
    
                AdditionalInfo::create($additionalInfoData);
            }
    
            $appointment->photo = $appointment->photo ? url(Storage::url($appointment->photo)) : null;
            $appointment->gender = $this->genderMap[$appointment->gender] ?? $appointment->gender;
    
            DB::commit();
            return response()->json($appointment->load('additionalInfo'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao criar o agendamento.', ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao criar o agendamento.'], 500);
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

    public function update(AppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        DB::beginTransaction();
        try {
            Log::info('Iniciando atualização de agendamento.', ['appointment_id' => $appointment->id]);

            $validatedData = $request->validated();
            $validatedData['gender'] = $this->genderMap[$request->gender] ?? null;

            // Verifica se a foto foi atualizada
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                $path = $request->file('photo')->store('photos', 'public');
                $validatedData['photo'] = $path;
                Log::info('Foto atualizada.', ['path' => $path]);
            }

            // Defina isHidden como false ao adicionar novamente à lista
            $validatedData['isHidden'] = false;

            // Libera a cama anterior, se houver
            if ($appointment->additionalInfo) {
                $oldBed = Bed::find($appointment->additionalInfo->bed_id);
                if ($oldBed) {
                    $oldBed->is_available = true;
                    $oldBed->save();
                    Log::info('Cama anterior liberada.', ['bed_id' => $oldBed->id]);
                }
            }

            $appointment->update($validatedData);

            if ($request->has('additionalInfo')) {
                $additionalInfoData = $request->input('additionalInfo');
                $additionalInfoData['stay_duration'] = $additionalInfoData['stay_duration'] ?? null;

                $bed = Bed::find($additionalInfoData['bed_id']);
                if (!$bed || !$bed->is_available) {
                    Log::warning('Nova cama indisponível.', ['bed_id' => $additionalInfoData['bed_id']]);
                    return response()->json(['message' => 'A cama selecionada não está disponível.'], 422);
                }

                // Marque a nova cama como ocupada
                $bed->is_available = false;
                $bed->save();
                Log::info('Nova cama marcada como ocupada.', ['bed_id' => $bed->id]);

                $appointment->additionalInfo()->updateOrCreate(
                    ['appointment_id' => $appointment->id],
                    $additionalInfoData
                );
                Log::info('Informações adicionais atualizadas.', ['additionalInfo' => $additionalInfoData]);
            }

            $appointment->gender = $this->genderMap[$appointment->gender] ?? $appointment->gender;

            DB::commit();
            Log::info('Atualização concluída com sucesso.');
            return response()->json($appointment->load('additionalInfo'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar o agendamento.', ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao atualizar o agendamento.'], 500);
        }
    }

    public function hide($id): JsonResponse
    {
        $appointment = Appointment::with('additionalInfo')->findOrFail($id);

        DB::beginTransaction();
        try {
            $appointment->isHidden = true;
            $appointment->save();

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

            DB::commit();
            Log::info('Agendamento ocultado com sucesso.', ['appointment_id' => $id]);
            return response()->json(['message' => 'Agendamento ocultado com sucesso.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao ocultar o agendamento.', ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao ocultar o agendamento.'], 500);
        }
    }

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

}