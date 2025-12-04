<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckinControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_responde_alguma_coisa()
    {
        $response = $this->get(route('checkin.index'));
        $this->assertNotNull($response);
    }

    /** @test */
    public function store_redireciona_para_sucesso_quando_paciente_ja_tem_checkin_hoje()
    {
        $patient = Patient::factory()->create();

        $attendance = Attendance::factory()->create([
            'patient_id'   => $patient->id,
            'checkin_time' => now(),
            'queue_number' => 5,
        ]);

        $response = $this->post(route('checkin.store'), [
            'patient_id' => $patient->id,
        ]);

        $response->assertRedirect(
            route('checkin.success', ['queue_number' => $attendance->queue_number])
        );

        $this->assertEquals(1, Attendance::count());
    }

    /** @test */
    public function store_cria_novo_atendimento_quando_paciente_nao_tem_checkin_hoje()
    {
        $patient = Patient::factory()->create();

        Attendance::factory()->create([
            'patient_id'   => $patient->id,
            'checkin_time' => now()->subDay(),
            'queue_number' => 3,
        ]);

        $response = $this->post(route('checkin.store'), [
            'patient_id' => $patient->id,
        ]);

        $this->assertEquals(2, Attendance::count());

        $todayAttendance = Attendance::whereDate('checkin_time', today())->first();
        $this->assertNotNull($todayAttendance);

        $this->assertEquals($patient->id, $todayAttendance->patient_id);
        $this->assertEquals('waiting', $todayAttendance->status);

        $response->assertRedirect(
            route('checkin.success', ['queue_number' => $todayAttendance->queue_number])
        );
    }

    /** @test */
    public function rota_de_sucesso_responde_alguma_coisa()
    {
        $response = $this->get(route('checkin.success'));
        $this->assertNotNull($response);
    }

     public function store_falha_quando_patient_id_nao_e_fornecido()
    {
        $response = $this->from(route('checkin.index'))
            ->post(route('checkin.store'), [
            ]);

        $response->assertRedirect(route('checkin.index'));
        $response->assertSessionHasErrors('patient_id');
    }

    /** @test */
    public function store_falha_quando_patient_id_nao_existe()
    {
        $response = $this->from(route('checkin.index'))
            ->post(route('checkin.store'), [
                'patient_id' => 9999,
            ]);

        $response->assertStatus(404);
    }
}
