@props(['categories'])

<div class="space-x-2">
    @foreach ($categories as $category)
        <a href="#"
           class="px-3 py-1 border rounded-full text-xs uppercase font-semibold {{ $category->badgeClasses() }}"
           style="font-size: 10px">{{ $category->name }}</a>
    @endforeach
</div>
