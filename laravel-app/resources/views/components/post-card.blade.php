@props(['post'])

<article
    class="transition-colors duration-300 hover:bg-gray-100 border border-black border-opacity-0 hover:border-opacity-5 rounded-xl">
    <div class="py-6 px-5">
        <div>
            <img src="/images/{{ $post->image }}" alt="Blog Post illustration" class="rounded-xl">
        </div>

        <div class="mt-8 flex flex-col justify-between">
            <header>
                <x-category-badges :categories="$post->categories"/>

                <div class="mt-4">
                    <h1 class="text-3xl">
                        <a href="{{ route('posts.show', $post) }}" class="hover:text-blue-500">{{ $post->title }}</a>
                    </h1>

                    <span class="mt-2 block text-gray-400 text-xs">
                        Published <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->diffForHumans() }}</time>
                    </span>
                </div>
            </header>

            <div class="text-sm mt-4">
                @foreach ($post->excerptParagraphs() as $paragraph)
                    <p @class(['mt-4' => ! $loop->first])>{{ $paragraph }}</p>
                @endforeach
            </div>

            <footer class="flex justify-between items-center mt-8">
                <div class="flex items-center text-sm">
                    <img src="/images/{{ $post->author->avatar }}" alt="{{ $post->author->name }}">
                    <div class="ml-3">
                        <h5 class="font-bold">{{ $post->author->name }}</h5>
                        <h6>{{ $post->author->job_title }}</h6>
                    </div>
                </div>

                <div>
                    <a href="{{ route('posts.show', $post) }}"
                       class="transition-colors duration-300 text-xs font-semibold bg-gray-200 hover:bg-gray-300 rounded-full py-2 px-8"
                    >
                        Read More
                    </a>
                </div>
            </footer>
        </div>
    </div>
</article>
