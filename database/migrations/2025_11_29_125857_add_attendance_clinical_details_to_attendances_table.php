<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->enum('symptoms_status', ['worsened', 'same', 'improved'])->nullable()->after('status');
            $table->text('pre_notes')->nullable()->after('symptoms_status');
            $table->integer('wellness_score')->nullable()->after('pre_notes'); // 0-10
            $table->text('procedure_report')->nullable()->after('wellness_score');
            $table->text('magnetizer_notes')->nullable()->after('procedure_report');
        });
    }

    public function down(): void
    {
        // CORREÇÃO: nome correto da tabela
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'symptoms_status',
                'pre_notes',
                'wellness_score',
                'procedure_report',
                'magnetizer_notes'
            ]);
        });
    }
};
