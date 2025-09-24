<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'kanban';

    public function up(): void
    {
        Schema::connection($this->connection)->create('kanbans', function (Blueprint $table) {
            $table->id();
            $table->string('content');
            $table->string('module');
            $table->string('functionality');
            $table->string('assigned');
            $table->string('status')->default('todo'); // 'todo', 'in-progress-ricardo', etc.
            $table->integer('sort_order')->default(0); // antes: 'order'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('kanbans'); // antes: 'kanbans'
    }
};
