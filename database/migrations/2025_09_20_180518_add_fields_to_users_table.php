<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['manager', 'healer']) // Removido 'attendant' e 'patient'
                ->default('healer')
                ->after('password');
            $table->date('birth_date')->nullable()->after('type');
            $table->string('phone')->nullable()->after('birth_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['type', 'birth_date', 'phone']);
        });
    }
};
