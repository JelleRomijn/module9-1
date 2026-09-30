<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

#[Fillable(['author_id', 'title', 'slug', 'image', 'excerpt', 'body', 'published_at'])]
class Post extends Model
{
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Zorgt dat /posts/{post} de slug uit de URL gebruikt in plaats van het id.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Alleen posts die daadwerkelijk gepubliceerd zijn, nieuwste eerst.
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at');
    }

    /**
     * De samenvatting opgesplitst in losse alinea's, zodat de kaart op de
     * overzichtspagina er hetzelfde uitziet als in het ontwerp.
     *
     * @return array<int, string>
     */
    public function excerptParagraphs(): array
    {
        return preg_split('/\R\s*\R/', trim($this->excerpt)) ?: [];
    }

    /**
     * De tekst staat als Markdown in de database en wordt hier omgezet naar
     * HTML voor de detailpagina.
     */
    public function bodyHtml(): HtmlString
    {
        return new HtmlString(Str::markdown($this->body));
    }
}
