<?php

namespace App\Services;

use App\Models\Patient;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class PatientExportService
{
    public function exportToXlsx(Patient $patient)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar propriedades do documento
        $spreadsheet->getProperties()
            ->setCreator("Sistema CEAL")
            ->setTitle("Dados do Paciente: {$patient->name}")
            ->setDescription("Relatório completo do paciente gerado pelo sistema CEAL");

        // Carregar relacionamentos
        $patient->load([
            'attendances.healer.user',
            'appointments.healer.user',
            'absences.appointment',
            'preferredHealer.user',
            'manager'
        ]);

        // Definir larguras das colunas
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(35);

        // ========== SEÇÃO 1: DADOS DO PACIENTE ==========
        $sheet->setCellValue('A1', 'DADOS DO PACIENTE');
        $sheet->mergeCells('A1:B1');
        $this->applyHeaderStyle($sheet, 'A1:B1');

        $patientData = [
            ['ID', $patient->id],
            ['Nome Completo', $patient->name],
            ['Email', $patient->email ?? ''],
            ['Telefone', $patient->phone ?? ''],
            ['Data de Nascimento', $patient->birth_date ? Carbon::parse($patient->birth_date)->format('d/m/Y') : ''],
            ['Contato de Emergência', $patient->emergency_contact ?? ''],
            ['Observações de Saúde', $patient->health_notes ?? ''],
            ['Código de Acesso', $patient->access_code ?? ''],
            ['Magnetizador Preferido', $patient->preferredHealer->user->name ?? 'N/A'],
            ['Gerente Responsável', $patient->manager->name ?? 'N/A'],
            ['Data de Cadastro', $patient->created_at->format('d/m/Y H:i')],
        ];

        $row = 2;
        foreach ($patientData as $data) {
            $sheet->setCellValue("A{$row}", $data[0]);
            $sheet->setCellValue("B{$row}", $data[1]);
            $this->applyDataRowStyle($sheet, "A{$row}:B{$row}");
            $row++;
        }

        // ========== SEÇÃO 2: ESTATÍSTICAS ==========
        $row += 2;
        $sheet->setCellValue("A{$row}", 'ESTATÍSTICAS');
        $sheet->mergeCells("A{$row}:B{$row}");
        $this->applyHeaderStyle($sheet, "A{$row}:B{$row}");

        $row++;
        $stats = [
            ['Total de Atendimentos', $patient->attendances->count()],
            ['Total de Consultas', $patient->appointments->count()],
            ['Total de Faltas', $patient->absences->count()],
            ['Atendimentos Hoje', $patient->attendances->where('checkin_time', '>=', today())->count()],
        ];

        foreach ($stats as $stat) {
            $sheet->setCellValue("A{$row}", $stat[0]);
            $sheet->setCellValue("B{$row}", $stat[1]);
            $this->applyStatRowStyle($sheet, "A{$row}:B{$row}");
            $row++;
        }

        // ========== SEÇÃO 3: ATENDIMENTOS ==========
        if ($patient->attendances->count() > 0) {
            $row += 2;
            $sheet->setCellValue("A{$row}", 'ATENDIMENTOS');
            $sheet->mergeCells("A{$row}:H{$row}");
            $this->applyHeaderStyle($sheet, "A{$row}:H{$row}");

            $row++;
            $headers = ['Data', 'Hora', 'Magnetizador', 'Status', 'Senha', 'Sintomas', 'Bem-estar', 'Observações'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $col++;
            }
            $this->applyTableHeaderStyle($sheet, "A{$row}:H{$row}");

            $row++;
            foreach ($patient->attendances as $attendance) {
                $sheet->setCellValue("A{$row}", $attendance->checkin_time ? Carbon::parse($attendance->checkin_time)->format('d/m/Y') : '');
                $sheet->setCellValue("B{$row}", $attendance->checkin_time ? Carbon::parse($attendance->checkin_time)->format('H:i') : '');
                $sheet->setCellValue("C{$row}", $attendance->healer->user->name ?? 'N/A');
                $sheet->setCellValue("D{$row}", $this->translateStatus($attendance->status));
                $sheet->setCellValue("E{$row}", $attendance->queue_number ?? '');
                $sheet->setCellValue("F{$row}", $this->translateSymptoms($attendance->symptoms_status));
                $sheet->setCellValue("G{$row}", $attendance->wellness_score ? "{$attendance->wellness_score}/10" : '');
                $sheet->setCellValue("H{$row}", $attendance->notes ?? '');

                $this->applyTableRowStyle($sheet, "A{$row}:H{$row}", $row % 2 == 0);
                $row++;
            }

            // Ajustar larguras das colunas da tabela
            foreach (range('A', 'H') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        // ========== SEÇÃO 4: CONSULTAS ==========
        if ($patient->appointments->count() > 0) {
            $row += 2;
            $sheet->setCellValue("A{$row}", 'CONSULTAS');
            $sheet->mergeCells("A{$row}:E{$row}");
            $this->applyHeaderStyle($sheet, "A{$row}:E{$row}");

            $row++;
            $headers = ['Data', 'Hora', 'Magnetizador', 'Status', 'Observações'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $col++;
            }
            $this->applyTableHeaderStyle($sheet, "A{$row}:E{$row}");

            $row++;
            foreach ($patient->appointments as $appointment) {
                $sheet->setCellValue("A{$row}", $appointment->scheduled_date ? Carbon::parse($appointment->scheduled_date)->format('d/m/Y') : '');
                $sheet->setCellValue("B{$row}", $appointment->scheduled_time ? Carbon::parse($appointment->scheduled_time)->format('H:i') : '');
                $sheet->setCellValue("C{$row}", $appointment->healer->user->name ?? 'N/A');
                $sheet->setCellValue("D{$row}", $this->translateAppointmentStatus($appointment->status));
                $sheet->setCellValue("E{$row}", $appointment->notes ?? '');

                $this->applyTableRowStyle($sheet, "A{$row}:E{$row}", $row % 2 == 0);
                $row++;
            }

            foreach (range('A', 'E') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        // ========== SEÇÃO 5: FALTAS ==========
        if ($patient->absences->count() > 0) {
            $row += 2;
            $sheet->setCellValue("A{$row}", 'FALTAS');
            $sheet->mergeCells("A{$row}:D{$row}");
            $this->applyHeaderStyle($sheet, "A{$row}:D{$row}");

            $row++;
            $headers = ['Data', 'Justificada', 'Justificativa', 'Data Consulta'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $col++;
            }
            $this->applyTableHeaderStyle($sheet, "A{$row}:D{$row}");

            $row++;
            foreach ($patient->absences as $absence) {
                $sheet->setCellValue("A{$row}", $absence->absence_date ? Carbon::parse($absence->absence_date)->format('d/m/Y') : '');
                $sheet->setCellValue("B{$row}", $absence->justified ? 'Sim' : 'Não');
                $sheet->setCellValue("C{$row}", $absence->justification ?? '');
                $sheet->setCellValue("D{$row}", $absence->appointment ? $absence->appointment->scheduled_date->format('d/m/Y') : '');

                $this->applyTableRowStyle($sheet, "A{$row}:D{$row}", $row % 2 == 0);
                $row++;
            }

            foreach (range('A', 'D') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        // ========== RODAPÉ ==========
        $row += 2;
        $sheet->setCellValue("A{$row}", 'Relatório gerado em: ' . now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setItalic(true);

        // Salvar arquivo temporário
        $filename = "paciente_{$patient->name}_" . now()->format('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'patient_export_') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return [
            'path' => $tempFile,
            'filename' => $filename
        ];
    }

    private function applyHeaderStyle($sheet, $range)
    {
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true)->setSize(14);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF4F81BD');
        $style->getFont()->getColor()->setARGB('FFFFFFFF');
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    private function applyDataRowStyle($sheet, $range)
    {
        $style = $sheet->getStyle($range);
        $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle(substr($range, 0, 2))->getFont()->setBold(true);
    }

    private function applyStatRowStyle($sheet, $range)
    {
        $style = $sheet->getStyle($range);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDEBF7');
        $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle(substr($range, 0, 2))->getFont()->setBold(true);
    }

    private function applyTableHeaderStyle($sheet, $range)
    {
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFB8CCE4');
        $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function applyTableRowStyle($sheet, $range, $alternate)
    {
        $style = $sheet->getStyle($range);
        if ($alternate) {
            $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEAF1DD');
        }
        $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $style->getAlignment()->setWrapText(true);
    }

    private function translateStatus($status)
    {
        return match ($status) {
            'waiting' => 'Aguardando',
            'in_progress' => 'Em Atendimento',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
            default => $status
        };
    }

    private function translateSymptoms($symptoms)
    {
        return match ($symptoms) {
            'worsened' => 'Pioraram',
            'same' => 'Iguais',
            'improved' => 'Melhoraram',
            default => $symptoms ?? 'N/A'
        };
    }

    private function translateAppointmentStatus($status)
    {
        return match ($status) {
            'scheduled' => 'Agendada',
            'confirmed' => 'Confirmada',
            'completed' => 'Concluída',
            'cancelled' => 'Cancelada',
            'no_show' => 'Não Compareceu',
            default => $status
        };
    }
}
