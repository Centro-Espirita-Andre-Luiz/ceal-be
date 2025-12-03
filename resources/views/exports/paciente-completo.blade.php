@php
use App\Helpers\ExportHelper;
@endphp

DADOS COMPLETOS DO PACIENTE
============================

INFORMAÇÕES PESSOAIS:
---------------------
ID: {{ $patient->id }}
Nome: {{ $patient->name }}
Email: {{ $patient->email ?? 'N/A' }}
Telefone: {{ $patient->phone ?? 'N/A' }}
Data Nascimento: {{ ExportHelper::formatDate($patient->birth_date) }}
Contato Emergência: {{ $patient->emergency_contact ?? 'N/A' }}
Observações Saúde: {{ $patient->health_notes ?? 'Nenhuma' }}
Código Acesso: {{ $patient->access_code ?? 'N/A' }}
Magnetizador Preferido: {{ optional($patient->preferredHealer)->user->name ?? 'N/A' }}
Data Cadastro: {{ $patient->created_at->format('d/m/Y H:i') }}

ATENDIMENTOS ({{ $patient->attendances->count() }}):
----------------------------------------------------
@forelse($patient->attendances as $attendance)
- Data: {{ ExportHelper::formatDateTime($attendance->checkin_time, 'd/m/Y H:i') }}
Magnetizador: {{ optional($attendance->healer)->user->name ?? 'N/A' }}
Status: {{ $attendance->status ?? 'N/A' }}
Senha: {{ $attendance->queue_number ?? 'N/A' }}
Sintomas: {{ $attendance->symptoms_status ?? 'N/A' }}
Bem-estar: {{ $attendance->wellness_score ?? 'N/A' }}/10
Observações: {{ $attendance->notes ?? 'Nenhuma' }}
---
@empty
Nenhum atendimento registrado.
@endforelse

CONSULTAS ({{ $patient->appointments->count() }}):
---------------------------------------------------
@forelse($patient->appointments as $appointment)
- Data: {{ ExportHelper::formatDate($appointment->scheduled_date) }}
Horário: {{ ExportHelper::formatTime($appointment->scheduled_time) }}
Magnetizador: {{ optional($appointment->healer)->user->name ?? 'N/A' }}
Status: {{ $appointment->status ?? 'N/A' }}
Observações: {{ $appointment->notes ?? 'Nenhuma' }}
---
@empty
Nenhuma consulta agendada.
@endforelse

FALTAS ({{ $patient->absences->count() }}):
-------------------------------------------
@forelse($patient->absences as $absence)
- Data: {{ ExportHelper::formatDate($absence->absence_date) }}
Justificada: {{ $absence->justified ? 'Sim' : 'Não' }}
Justificativa: {{ $absence->justification ?? 'Nenhuma' }}
---
@empty
Nenhuma falta registrada.
@endforelse

===========================================
Relatório gerado em: {{ now()->format('d/m/Y H:i:s') }}