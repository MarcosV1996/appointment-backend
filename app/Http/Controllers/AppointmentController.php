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
use App\Rules\CpfRule;
use App\Rules\ValidCpf;

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
                'phone' => ['nullable', 'regex:/^(\(\d{2}\)\s?)?\d{4,5}-\d{4}$/'],
                'observation' => 'nullable',
                'gender' => 'required',
                'foreign_country' => 'boolean',
                'noPhone' => 'boolean',
                'isHidden' => 'boolean',
                'replace' => 'boolean',
                'showMore' => 'boolean',
                'photo' => 'nullable|file|image|max:2048',
                'accommodation_mode' => 'required|string|in:24_horas,pernoite',
                'exit_date' => 'nullable|date|after_or_equal:entry_date',
            ]);
    
            $validatedData['date'] = now()->format('Y-m-d');
    
            Log::info('Dados validados:', $validatedData);
    
            // Verifica se já existe um agendamento com o mesmo CPF
            $appointment = Appointment::where('cpf', $validatedData['cpf'])->first();
    
            if ($appointment) {
                if ($validatedData['replace']) {
                    Log::info('Substituindo agendamento existente para CPF:', ['cpf' => $validatedData['cpf']]);
    
                    // Se uma nova foto foi enviada, atualiza; senão, mantém a antiga
                    if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                        // Apaga a foto antiga (opcional)
                        if ($appointment->photo) {
                            Storage::disk('public')->delete($appointment->photo);
                        }
    
                        // Salva a nova foto
                        $validatedData['photo'] = $request->file('photo')->store('photos', 'public');
                        Log::info('Nova foto salva com sucesso:', ['path' => $validatedData['photo']]);
                    } else {
                        // Mantém a foto antiga se nenhuma nova for enviada
                        $validatedData['photo'] = $appointment->photo;
                        Log::info('Nenhuma nova foto enviada. Mantendo a foto antiga:', ['photo' => $validatedData['photo']]);
                    }
    
                    // Atualiza os dados do agendamento existente
                    $appointment->update($validatedData);
    
                    return response()->json([
                        'message' => 'Agendamento substituído com sucesso!',
                        'appointment' => $appointment,
                    ], 200);
                } else {
                    Log::warning('CPF já existe no banco e "replace" não foi solicitado:', ['cpf' => $validatedData['cpf']]);
                    return response()->json([
                        'message' => 'Já existe um agendamento com este CPF!',
                    ], 409);
                }
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
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Agendamento não encontrado.'], 404);
        }

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
            'phone', 'state', 'city', 'observation','accommodation_mode', 'isHidden'
        ]));
    
        // Verifica se additionalInfo foi enviado e atualiza
        if ($request->has('additionalInfo')) {
            $additionalInfoData = $request->input('additionalInfo');
    
            // Adiciona validação para exit_date (opcional)
            if (isset($additionalInfoData['exit_date'])) {
                $request->validate([
                    'additionalInfo.exit_date' => 'nullable|date|after_or_equal:arrival_date',
                ]);
            }
    
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
            'accommodation_mode',
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


    public function getAvailableBeds()
    {
        // Total de camas disponíveis nos quartos (supondo que cada quarto tem 4 camas)
        $totalBeds = \App\Models\Bed::count();
    
        // Contar quantas camas estão ocupadas na tabela `additional_infos`
        $occupiedBeds = \App\Models\AdditionalInfo::whereNotNull('bed_id')->count();
    
        // Calcular vagas disponíveis
        $availableBeds = $totalBeds - $occupiedBeds;
    
        return response()->json(['availableBeds' => $availableBeds]);
    }
}    