<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'color'])]
class Category extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    /**
     * De volledige Tailwind-klassen voor het label bij een post. De kleur staat
     * als 'blue' of 'red' in de database; hier maken we er klassen van.
     */
    public function badgeClasses(): string
    {
        return match ($this->color) {
            'red' => 'border-red-300 text-red-300',
            'green' => 'border-green-300 text-green-300',
            'purple' => 'border-purple-300 text-purple-300',
            default => 'border-blue-300 text-blue-300',
        };
    }
}
