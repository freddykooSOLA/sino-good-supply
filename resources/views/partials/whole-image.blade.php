{{--
    Whole catalogue picture: the image is shown uncropped (object-contain) over a
    blurred copy of itself. Never object-cover, never crop a deck picture.
    Params: $src, $alt, $class (frame), $mediaClass, $loading, $attrs (img extras)
--}}
<div class="whole-image {{ $class ?? 'h-full w-full' }}">
    <img src="{{ $src }}" alt="" aria-hidden="true" class="whole-image__backdrop" loading="{{ $loading ?? 'lazy' }}" decoding="async" draggable="false">
    <img
        src="{{ $src }}"
        alt="{{ $alt ?? '' }}"
        class="whole-image__media {{ $mediaClass ?? '' }}"
        loading="{{ $loading ?? 'lazy' }}"
        decoding="async"
        draggable="false"
        {!! $attrs ?? '' !!}
    >
</div>
