<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->unsignedInteger('waitlist_position')->nullable()->after('status');
            $table->index(['workshop_id', 'status', 'waitlist_position'], 'registrations_workshop_status_waitlist_idx');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropIndex('registrations_workshop_status_waitlist_idx');
            $table->dropColumn('waitlist_position');
        });
    }
};
