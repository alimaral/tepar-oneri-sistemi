<a href="/">
    <img {{ $attributes->merge(['class' => 'h-12 w-auto']) }} src="{{ asset('images/backgrounds/teparlogo1.png') }}" alt="Tepar Tekstil Logo">
    {{-- Logo yolunu ve class'larını (h-12 w-auto gibi Tailwind class'ları) isteğinize göre ayarlayın --}}
    {{-- Eğer SVG kullanıyorsanız:
    <svg viewBox="0 0 SIZIN_SVG_VIEWBOX_DEGERLERINIZ" {{ $attributes->merge(['class' => 'w-16 h-16 fill-current text-gray-500']) }}>
        <!-- SVG PATH VERİLERİNİZ BURAYA -->
    </svg>
    --}}
</a>
