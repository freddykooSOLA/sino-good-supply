<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        $stats = Setting::getValue('company_stats', []);
        $contact = Setting::getValue('contact', []);
        $pageHero = page_hero('about');
        $aboutContent = Setting::getValue('about_content', []) ?: [];
        $advantages = Setting::getValue('advantages', []) ?: [];

        return view('pages.about', compact('stats', 'contact', 'pageHero', 'aboutContent', 'advantages'));
    }

    public function orderProcess(): View
    {
        $orderProcess = Setting::getValue('order_process', []) ?: [];
        $pageHero = page_hero('order_process');

        return view('pages.order-process', compact('orderProcess', 'pageHero'));
    }

    public function contact(): View
    {
        $contact = Setting::getValue('contact', []);
        $pageHero = page_hero('contact');

        return view('pages.contact', compact('contact', 'pageHero'));
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'company' => ['nullable', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $contact = Setting::getValue('contact', []);
        $to = $contact['email'] ?? config('mail.from.address');

        if ($to) {
            Mail::to($to)->send(new ContactFormMail($data));
        }

        return back()->with('success', __('messages.contact_success'));
    }
}
