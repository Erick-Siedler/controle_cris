<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('groups_id')->constrained()->cascadeOnDelete();
            $table->date('date')->index();
            $table->text('content');
            $table->string('color', 20)->default('yellow');
            $table->decimal('position_x', 5, 2)->default(5);
            $table->decimal('position_y', 5, 2)->default(5);
            $table->boolean('is_pinned')->default(false);
            $table->unsignedInteger('z_index')->default(1);
            $table->timestamps();

            $table->index(['groups_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_notes');
    }
};
