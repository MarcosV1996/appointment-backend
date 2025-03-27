<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:daily,weekly,monthly,custom',
            'data' => 'required|array',
            'period' => 'nullable|string',
            'report_date' => 'nullable|date'
        ]);

        $report = Report::create([
            'type' => $validated['type'],
            'report_date' => $validated['report_date'] ?? now()->format('Y-m-d'),
            'period' => $validated['period'] ?? $validated['type'],
            'data' => json_encode($validated['data']),
            'summary' => $this->generateSummary($validated['data']),
            'user_id' => Auth::id(),
        ]);

        return response()->json($report, 201);
    }

    private function generateSummary(array $data): string
    {
        $total = array_sum(array_column($data['gender_counts'] ?? [], 'count'));
        
        return sprintf(
            "Relatório %s - Total: %d acolhidos. Quartos: A(%d), B(%d), C(%d). Gênero: M(%d), F(%d).",
            now()->format('d/m/Y'),
            $total,
            $data['bed_counts']['A'] ?? 0,
            $data['bed_counts']['B'] ?? 0,
            $data['bed_counts']['C'] ?? 0,
            collect($data['gender_counts'] ?? [])->where('gender', 'Masculino')->first()['count'] ?? 0,
            collect($data['gender_counts'] ?? [])->where('gender', 'Feminino')->first()['count'] ?? 0
        );
    }
}