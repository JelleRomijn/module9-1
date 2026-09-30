<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Koppeltabel: een post kan meerdere categorieen hebben en een categorie
     * hoort bij meerdere posts.
     */
    public function up(): void
    {
        Schema::create('category_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->unique(['post_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_post');
    }
};
