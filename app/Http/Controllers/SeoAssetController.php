<?php

namespace App\Http\Controllers;

use App\Services\LlmsTxtService;
use App\Services\SitemapService;
use Illuminate\Http\Response;

class SeoAssetController extends Controller
{
    public function sitemap(SitemapService $sitemap): Response
    {
        return response($sitemap->xml(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function llms(LlmsTxtService $llms): Response
    {
        return response($llms->generate(), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $sitemap = url('/sitemap.xml');
        $body = <<<TXT
User-agent: *
Allow: /

User-agent: GPTBot
Allow: /

User-agent: ClaudeBot
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: Google-Extended
Allow: /

User-agent: OAI-SearchBot
Allow: /

User-agent: Bingbot
Allow: /

Sitemap: {$sitemap}

TXT;

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
