<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $lary = User::updateOrCreate(
            ['email' => 'lary@laracasts.com'],
            [
                'name' => 'Lary Laracore',
                'job_title' => 'Mascot at Laracasts',
                'avatar' => 'lary-avatar.svg',
                'password' => 'wachtwoord',
            ],
        );

        $categories = collect([
            ['name' => 'Techniques', 'color' => 'blue'],
            ['name' => 'Updates', 'color' => 'red'],
            ['name' => 'Testing', 'color' => 'green'],
            ['name' => 'Tooling', 'color' => 'purple'],
        ])->mapWithKeys(fn (array $category) => [
            $category['name'] => Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                ['name' => $category['name'], 'color' => $category['color']],
            ),
        ]);

        foreach ($this->posts() as $index => $attributes) {
            $post = Post::updateOrCreate(
                ['slug' => Str::slug($attributes['title'])],
                [
                    'author_id' => $lary->id,
                    'title' => $attributes['title'],
                    'image' => $attributes['image'],
                    'excerpt' => $attributes['excerpt'],
                    'body' => $attributes['body'],
                    // De bovenste post is het nieuwst; daarna loopt het terug in de tijd.
                    'published_at' => now()->subDays($attributes['days_ago']),
                ],
            );

            $post->categories()->sync(
                $categories->only($attributes['categories'])->pluck('id')
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function posts(): array
    {
        $excerpt = <<<'TEXT'
        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.

        Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
        TEXT;

        $body = <<<'MARKDOWN'
        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.

        Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.

        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit.

        ## Sed quia consequuntur

        Magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.

        Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?

        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
        MARKDOWN;

        $posts = [
            [
                'title' => 'This is a big title and it will look great on two or even three lines. Wooohoo!',
                'image' => 'illustration-1.png',
                'categories' => ['Techniques', 'Updates'],
                'days_ago' => 1,
            ],
            [
                'title' => 'Blade components keep your markup from repeating itself',
                'image' => 'illustration-1.png',
                'categories' => ['Techniques'],
                'days_ago' => 3,
            ],
            [
                'title' => 'Eloquent relationships, explained without the jargon',
                'image' => 'illustration-2.png',
                'categories' => ['Techniques', 'Updates'],
                'days_ago' => 6,
            ],
            [
                'title' => 'Why route model binding saves you a controller line',
                'image' => 'illustration-3.png',
                'categories' => ['Techniques', 'Tooling'],
                'days_ago' => 10,
            ],
            [
                'title' => 'Seeding a database with data you can actually read',
                'image' => 'illustration-4.png',
                'categories' => ['Testing'],
                'days_ago' => 14,
            ],
            [
                'title' => 'Ten minutes with the query builder',
                'image' => 'illustration-5.png',
                'categories' => ['Techniques', 'Testing'],
                'days_ago' => 21,
            ],
        ];

        // Alle posts delen dezelfde voorbeeldtekst uit de HTML-template.
        return array_map(
            fn (array $post): array => [...$post, 'excerpt' => $excerpt, 'body' => $body],
            $posts,
        );
    }
}
