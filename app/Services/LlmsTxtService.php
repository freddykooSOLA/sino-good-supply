<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Setting;

class LlmsTxtService
{
    public function generate(): string
    {
        $contact = site_contact();
        $company = localized_setting($contact, 'company') ?: 'SINO GOOD QY Supply Chain CO., LTD';
        $email = $contact['email'] ?? '';
        $phone = $contact['phone'] ?? '';
        $whatsapp = $contact['whatsapp'] ?? '';

        $lines = [
            '# '.$company,
            '',
            '> '.__('messages.tagline', [], 'en'),
            '',
            '## About',
            '- Customized hotel supply chain services for European hospitality projects.',
            '- Series, case studies, order process, and contact details are available in English, Simplified Chinese, and Traditional Chinese.',
            '',
            '## Contact',
        ];

        if ($email) {
            $lines[] = '- Email: '.$email;
        }
        if ($phone) {
            $lines[] = '- Phone: '.$phone;
        }
        if ($whatsapp) {
            $lines[] = '- WhatsApp: '.$whatsapp;
        }

        $lines[] = '';
        $lines[] = '## Main pages';
        foreach (['', 'products', 'cases', 'order-process', 'about', 'faq', 'contact'] as $path) {
            $label = $path === '' ? 'Home' : ucfirst(str_replace('-', ' ', $path));
            $lines[] = '- ['.$label.']('.locale_url($path, 'en').')';
        }

        $lines[] = '';
        $lines[] = '## Product categories';
        Category::query()->active()->orderBy('sort_order')->get()->each(function (Category $category) use (&$lines) {
            $lines[] = '- ['.$category->localizedName('en').']('.locale_url('category/'.$category->localizedSlug('en'), 'en').')';
        });

        $lines[] = '';
        $lines[] = '## SINO Daily';
        $news = Setting::getValue('latest_news', []) ?: [];
        foreach (is_array($news) ? $news : [] as $item) {
            if (! is_array($item)) {
                continue;
            }
            $title = localized_setting($item, 'title', 'en');
            $link = $item['link'] ?? locale_url('', 'en');
            if ($title) {
                $lines[] = '- ['.$title.']('.$link.')';
            }
        }

        return implode("\n", $lines)."\n";
    }
}
