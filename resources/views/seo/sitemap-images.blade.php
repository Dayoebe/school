{!! '<' . '?xml version="1.0" encoding="UTF-8"?>' . "\n" !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach ($entries as $entry)
    <url>
        <loc>{{ $entry['loc'] }}</loc>
        <image:image>
            <image:loc>{{ $entry['image'] }}</image:loc>
            <image:title>{{ $entry['title'] }}</image:title>
            <image:caption>{{ $entry['caption'] }}</image:caption>
        </image:image>
    </url>
@endforeach
</urlset>
