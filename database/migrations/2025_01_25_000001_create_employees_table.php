<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->index();
            $table->unsignedInteger('user_id');
            
            $table->string('name');
            $table->string('emp_id')->unique();
            $table->string('department');
            $table->string('designation');
            $table->string('email')->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            $table->boolean('is_deleted')->default(false);
            $table->softDeletes('deleted_at', 6);
            $table->timestamps(6);
            
            // Indexes
            $table->index(['company_id', 'deleted_at']);
            $table->index(['emp_id', 'company_id']);
            
            // Foreign key constraints
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};