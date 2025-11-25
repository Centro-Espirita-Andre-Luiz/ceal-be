<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckinController extends Controller
{
    public function index()
    {
        return view('checkin.index');
    }

    public function checkAccessCode(Request $request)
    {
        $request->validate([
            'access_code' => 'required|string'
        ]);

        $patient = Patient::where('access_code', $request->access_code)->first();

        if (!$patient) {
            return redirect()->back()->with('error', 'Código de acesso inválido.');
        }

        return view('checkin.welcome', compact('patient'));
    }

    public function store(Request $request)
    {
        $patient = Patient::findOrFail($request->patient_id);

        // Verificar se já fez check-in hoje
        $todayCheckin = Attendance::where('patient_id', $patient->id)
            ->whereDate('checkin_time', today())
            ->first();

        if ($todayCheckin) {
            return redirect()->route('checkin.success', ['queue_number' => $todayCheckin->queue_number]);
        }

        // Obter o último número de senha do dia
        $lastQueueNumber = Attendance::whereDate('checkin_time', today())->max('queue_number') ?? 0;

        // Criar registro de atendimento
        $attendance = Attendance::create([
            'patient_id' => $patient->id,
            'healer_id' => $patient->preferred_healer_id,
            'checkin_time' => now(),
            'status' => 'waiting',
            'queue_number' => $lastQueueNumber + 1
        ]);

        return redirect()->route('checkin.success', ['queue_number' => $attendance->queue_number]);
    }

    public function success($queue_number)
    {
        return view('checkin.success', compact('queue_number'));
    }
}
