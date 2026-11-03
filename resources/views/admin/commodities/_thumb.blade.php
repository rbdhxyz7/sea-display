@if ($c->photo_url)
    <img src="{{ $c->photo_url }}" alt="{{ $c->name }}" class="{{ $size }} shrink-0 rounded-lg object-cover" loading="lazy">
@else
    <span class="{{ $size }} grid shrink-0 place-items-center rounded-lg bg-cyan-100 text-lg">🐟</span>
@endif