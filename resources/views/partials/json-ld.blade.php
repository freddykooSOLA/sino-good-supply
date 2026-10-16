@if(!empty($structuredData))
    @foreach($structuredData as $graph)
        <script type="application/ld+json">{!! json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) !!}</script>
    @endforeach
@endif
