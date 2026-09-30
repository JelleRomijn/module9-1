<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            // De slug staat in de URL: /posts/mijn-eerste-post
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            // Korte samenvatting op de overzichtspagina.
            $table->text('excerpt');
            // Volledige tekst op de detailpagina, geschreven in Markdown.
            $table->text('body');
            // Leeg betekent: nog een concept, dus niet zichtbaar op de site.
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
