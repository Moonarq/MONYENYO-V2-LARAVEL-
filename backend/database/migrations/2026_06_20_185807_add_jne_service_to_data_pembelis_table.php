<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('data_pembelis', function (Blueprint $table) {
        $table->string('jne_destination_code')->nullable()->after('no_resi');
        $table->string('jne_service_code')->nullable()->after('jne_destination_code');
    });
}

public function down(): void
{
    Schema::table('data_pembelis', function (Blueprint $table) {
        $table->dropColumn(['jne_destination_code', 'jne_service_code']);
    });
}
};
