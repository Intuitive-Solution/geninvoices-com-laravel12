<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->index();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('assigned_user_id')->nullable();
            
            // Resource specific fields
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('rate_per_hour', 16, 4)->default(0);
            $table->decimal('rate_per_day', 16, 4)->default(0);
            $table->decimal('rate_per_week', 16, 4)->default(0);
            $table->decimal('rate_per_month', 16, 4)->default(0);
            
            
            // Standard fields following Invoice Ninja patterns
            $table->string('custom_value1')->nullable();
            $table->string('custom_value2')->nullable();
            $table->string('custom_value3')->nullable();
            $table->string('custom_value4')->nullable();
            
            $table->boolean('is_deleted')->default(false);
            $table->softDeletes('deleted_at', 6);
            $table->timestamps(6);
            
            // Indexes
            $table->index(['company_id', 'deleted_at']);
            
            // Foreign key constraints
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
