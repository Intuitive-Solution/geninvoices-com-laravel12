<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add archived_at column to employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->timestamp('archived_at', 6)->nullable()->after('email');
            $table->index(['company_id', 'archived_at']);
        });

        // Since the status column has been dropped in a previous migration,
        // we cannot migrate based on status. The archived_at column will be null
        // for all existing employees, which means they are considered active.
        // This is the expected behavior for the EntityState pattern.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'archived_at']);
            $table->dropColumn('archived_at');
        });
    }
};
