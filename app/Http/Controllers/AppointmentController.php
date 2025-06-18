<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use App\Models\AdditionalInfo;
use App\Models\Bed;
use App\Http\Requests\AppointmentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Rules\CpfRule;
use App\Rules\ValidCpf;
use App\Models\Report;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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
        // Log simplificado dos dados recebidos (sem dados sensíveis)
        Log::info('Novo agendamento recebido', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'cpf_hash' => hash('sha256', $request->input('cpf', '')), 
            'arrival_date' => $request->input('arrival_date')
        ]);
    
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts(
            'appointment-submission:'.$request->ip(), 
            5 // 5 tentativas por minuto
        )) {
            return response()->json([
                'message' => 'Muitas tentativas de agendamento. Por favor, tente novamente mais tarde.'
            ], 429);
        }
        \Illuminate\Support\Facades\RateLimiter::hit('appointment-submission:'.$request->ip());
    
        try {
            $request->validate([
                'cpf' => ['required', 'string'],
                'arrival_date' => ['required', 'date']
            ]);
    
            $request->merge([
                'date' => $request->input('arrival_date'),
                'replace' => filter_var($request->input('replace', false), FILTER_VALIDATE_BOOLEAN)
            ]);
    
            $validatedData = $request->validate([
                'cpf' => ['required', 'string', 'size:11', new ValidCpf()],
                'name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'date' => 'required|date',
                'arrival_date' => 'required|date|after_or_equal:today',
                'time' => 'required|date_format:H:i',
                'birth_date' => 'required|date|before_or_equal:-18 years',
                'state' => 'required|string|max:50',
                'city' => 'required|string|max:50',
                'mother_name' => 'required|string|max:100',
                'phone' => ['nullable', 'string', 'min:10', 'max:11'],
                'observation' => 'nullable|string|max:500',
                'gender' => 'required|in:male,female,other',
                'foreign_country' => ['required', Rule::in([true, false, 'true', 'false', 1, 0, '1', '0'])],
                'noPhone' => ['sometimes', Rule::in([true, false, 'true', 'false', 1, 0, '1', '0'])],
                'replace' => 'sometimes|boolean',
                'photo' => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
                'accommodation_mode' => 'required|in:24_horas,pernoite'
            ]);
    
            $validatedData['date'] = now()->format('Y-m-d');
            $validatedData['isHidden'] = false;
    
            $existingAppointment = Appointment::where('cpf', $validatedData['cpf'])->first();
    
            if ($existingAppointment) {
                if (!$validatedData['replace']) {
                    Log::warning('Tentativa de agendamento com CPF existente', [
                        'cpf_hash' => hash('sha256', $validatedData['cpf'])
                    ]);
                    return response()->json([
                        'message' => 'Já existe um agendamento com este CPF!',
                        'existing_appointment_id' => $existingAppointment->id
                    ], 409);
                }
    
                return $this->replaceExistingAppointment($existingAppointment, $validatedData, $request);
            }
    
            return $this->createNewAppointment($validatedData, $request);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Erro de validação no agendamento', [
                'errors' => $e->errors(),
                'input' => $request->except(['photo'])
            ]);
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);
    
        } catch (\Exception $e) {
            Log::error('Erro ao processar agendamento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Erro ao processar o agendamento. Por favor, tente novamente.'
            ], 500);
        }

if ($request->hasFile('photo')) {
    $filename = time().'_'.Str::random(20).'.'.$request->file('photo')->extension();
    $path = $request->file('photo')->storeAs('public/photos', $filename);
    $validatedData['photo'] = 'photos/'.$filename; // Armazena apenas o caminho relativo
    
    \Log::info('Foto armazenada:', [
        'filename' => $filename,
        'full_path' => storage_path('app/'.$path),
        'public_url' => asset('storage/photos/'.$filename)
    ]);
}
    }
    
    /**
     * Substitui um agendamento existente
     */
    protected function replaceExistingAppointment(Appointment $appointment, array $validatedData, Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                if ($appointment->photo) {
                    Storage::disk('public')->delete($appointment->photo);
                }
                $validatedData['photo'] = $request->file('photo')->store('photos', 'public');
            } else {
                $validatedData['photo'] = $appointment->photo;
            }
    
            $appointment->update($validatedData);
    
            DB::commit();
    
            Log::info('Agendamento substituído com sucesso', ['appointment_id' => $appointment->id]);
    
            return response()->json([
                'message' => 'Agendamento atualizado com sucesso!',
                'appointment' => $appointment
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Falha ao substituir agendamento', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id
            ]);
            throw $e;
        }
    }

   public function getPhotoUrlAttribute()
{
    if (!$this->photo) {
        return null;
    }
    return asset('storage/'.$this->photo);
}
    
   
    protected function createNewAppointment(array $validatedData, Request $request)
    {
      DB::beginTransaction();
    try {
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $path = $request->file('photo')->store('public/photos');
            $validatedData['photo'] = str_replace('public/', '', $path); // Remove 'public/' do caminho
            
            Log::info('Foto armazenada:', [
                'caminho_completo' => storage_path('app/'.$path),
                'caminho_banco' => $validatedData['photo']
            ]);
        }
    
            $sanitizedData = array_map(function ($value) {
                return is_string($value) ? htmlspecialchars(strip_tags($value)) : $value;
            }, $validatedData);
    
            $newAppointment = Appointment::create($sanitizedData);
    
            DB::commit();
    
            Log::info('Novo agendamento criado', ['appointment_id' => $newAppointment->id]);
    
            return response()->json([
                'message' => 'Agendamento realizado com sucesso!',
                'appointment' => $newAppointment
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($validatedData['photo'])) {
                Storage::disk('public')->delete($validatedData['photo']);
            }
            
            Log::error('Falha ao criar novo agendamento', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
   
    
    public function uploadPhoto(Request $request, $id)
{
    $request->validate(['photo' => 'required|image|max:2048']);
    
    if (!auth()->check()) {
        abort(401, 'Unauthenticated');
    }

    $user = User::findOrFail($id);
    
    if ($user->photo) {
        Storage::disk('public')->delete($user->photo);
    }
    
    $path = $request->file('photo')->store('photos', 'public');
    $user->photo = $path;
    $user->save();
    
    return response()->json([
        'photo' => $path,
        'photo_url' => Storage::url($path),
        'user' => $user->fresh()
    ]);
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
    
        if ($request->has('additionalInfo')) {
            $additionalInfoData = $request->input('additionalInfo');
    
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

        $roomMapping = [
            'A' => 1,
            'B' => 2,
            'C' => 3,
        ];

        $query = Appointment::query();

        if ($room && isset($roomMapping[$room])) {
            $roomId = $roomMapping[$room];
            $query->whereHas('additionalInfo', function ($q) use ($roomId) {
                $q->where('room_id', $roomId);
            });
        }

        if ($gender) {
            $query->where('gender', $gender);
        }

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
            'bed_counts' => $bedCounts, 
        ]);
    } catch (\Exception $e) {
        \Log::error('Erro ao gerar relatórios: ' . $e->getMessage());
        return response()->json([
            'error' => 'Erro ao processar os relatórios',
            'details' => $e->getMessage(),
        ], 500);
    }
  }

  public function register(Request $request)
  {
      $validator = Validator::make($request->all(), [
          'name' => 'required|string|max:255',
          'username' => 'required|string|max:255|unique:users',
          'email' => 'nullable|email|max:255|unique:users',
          'password' => 'required|string|min:6|confirmed', 
          'role' => 'required|in:admin,employee'
      ]);
  
      if ($validator->fails()) {
          return response()->json([
              'message' => 'Validation failed',
              'errors' => $validator->errors()
          ], 422);
      }
  
      $user = User::create([
          'name' => $request->name,
          'username' => $request->username,
          'email' => $request->email,
          'password' => Hash::make($request->password),
          'role' => $request->role
      ]);
  
      return response()->json([
          'message' => 'User registered successfully',
          'user' => $user
      ], 201);
  }

  public function updateAppointment(Request $request, $id)
  {
      $appointment = Appointment::find($id);
  
      if ($appointment) {
          $appointment->fill($request->all());
          $appointment->save();
  
          return response()->json([
           'appointment' => $appointment
          ], 200);
      }
  
      return response()->json(['message' => 'Agendamento não encontrado'], 404);
  }
  

  public function saveOrUpdateAppointment(Request $request) {
    $cpf = $request->input('cpf');
    $appointment = Appointment::where('cpf', $cpf)->first();

    if ($appointment) {
        $appointment->update([
            'name' => $request->input('name'),
            'last_name' => $request->input('last_name'),
            'arrival_date' => $request->input('arrival_date'),
            'isHidden' => false, 
        ]);
    } else {
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
    $totalBeds = 12;
    
    // Conta camas ocupadas em agendamentos não ocultos
    $occupiedBeds = DB::table('additional_infos')
        ->join('appointments', 'additional_infos.appointment_id', '=', 'appointments.id')
        ->where('appointments.isHidden', false)
        ->whereNotNull('additional_infos.bed_id')
        ->count();

    // Calcula por quarto (supondo 4 camas por quarto)
    $rooms = ['A' => 1, 'B' => 2, 'C' => 3];
    $roomData = [];
    
    foreach ($rooms as $roomName => $roomId) {
        $occupied = DB::table('additional_infos')
            ->join('appointments', 'additional_infos.appointment_id', '=', 'appointments.id')
            ->where('additional_infos.room_id', $roomId)
            ->where('appointments.isHidden', false)
            ->whereNotNull('additional_infos.bed_id')
            ->count();
            
        $roomData[$roomName] = [
            'available' => max(0, 4 - $occupied),
            'occupied' => $occupied
        ];
    }

    return response()->json([
        'availableBeds' => max(0, $totalBeds - $occupiedBeds),
        'totalBeds' => $totalBeds,
        'occupiedBeds' => $occupiedBeds,
        'rooms' => $roomData
    ]);
}

private function getBedsByRoom($roomId)
{
    $totalInRoom = 4;
    $occupied = DB::table('additional_infos')
        ->join('appointments', 'additional_infos.appointment_id', '=', 'appointments.id')
        ->where('additional_infos.room_id', $roomId)
        ->where('appointments.isHidden', false)
        ->whereNotNull('additional_infos.bed_id')
        ->count();
    
    return [
        'available' => max(0, $totalInRoom - $occupied),
        'occupied' => $occupied
    ];
}

    public function saveReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:daily,weekly,monthly,custom',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);
    
            // Gera os dados do relatório
            $reportResponse = $this->getReports($request);
            
            if ($reportResponse->getStatusCode() !== 200) {
                throw new \Exception("Erro ao gerar relatório: " . $reportResponse->getContent());
            }
    
            $reportData = $reportResponse->getData(true);
    
            $summary = $this->generateReportSummary($reportData);
    
            // Salva no banco de dados
            $report = new Report();
            $report->type = $validated['type'];
            $report->report_date = now()->format('Y-m-d');
            $report->data = json_encode($reportData);
            $report->summary = $summary;
            $report->user_id = Auth::id(); // Ou Auth::user()->id
            $report->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Relatório salvo com sucesso!',
                'report' => $report
            ]);
    
        } catch (\Exception $e) {
            \Log::error("Erro ao salvar relatório: " . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar relatório: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function generateReportSummary(array $reportData): string
    {
        $total = count($reportData['appointments'] ?? []);
        
        $summary = "Relatório gerado em " . now()->format('d/m/Y H:i') . "\n";
        $summary .= "Total de acolhidos: " . $total . "\n";
        
        if (isset($reportData['bed_counts'])) {
            $summary .= "Quartos - A: {$reportData['bed_counts']['A']}, B: {$reportData['bed_counts']['B']}, C: {$reportData['bed_counts']['C']}\n";
        }
        
        if (isset($reportData['gender_counts'])) {
            $genders = array_map(function($item) {
                return "{$item['gender']}: {$item['count']}";
            }, $reportData['gender_counts']);
            $summary .= "Gêneros: " . implode(', ', $genders) . "\n";
        }
        
        if (isset($reportData['age_counts'])) {
            $ages = array_map(function($item) {
                return "{$item['group']}: {$item['count']}";
            }, $reportData['age_counts']);
            $summary .= "Faixa etária: " . implode(', ', $ages);
        }
        
        return $summary;
    }
}