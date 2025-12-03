<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('healers', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('license_number');
        });
    }

    public function down(): void
    {
        Schema::table('healers', function (Blueprint $table) {
            $table->dropColumn('bio');
        });
    }
};
