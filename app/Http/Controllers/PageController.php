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

        return view('pages.about', compact('stats', 'contact'));
    }

    public function contact(): View
    {
        $contact = Setting::getValue('contact', []);

        return view('pages.contact', compact('contact'));
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
