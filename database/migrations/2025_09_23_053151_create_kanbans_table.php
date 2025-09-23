<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used.
     */
    protected $connection = 'kanban';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->create('kanbans', function (Blueprint $table) {
            $table->id();
            $table->string('content');
            $table->string('module');
            $table->string('functionality');
            $table->string('assigned');
            $table->string('status')->default('todo'); // 'todo', 'in-progress-ricardo', etc.
            $table->integer('order')->default(0); // Para el orden vertical
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('kanbans');
    }
};
