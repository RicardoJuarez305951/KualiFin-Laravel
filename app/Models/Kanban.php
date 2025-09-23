<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kanban extends Model
{
    protected $fillable = [
        'content',
        'module',
        'functionality',
        'assigned',
        'status',
        'order',
        ];

    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('content');
            $table->string('module');
            $table->string('functionality');
            $table->string('assigned');
            $table->string('status')->default('todo');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
        
    }
}
