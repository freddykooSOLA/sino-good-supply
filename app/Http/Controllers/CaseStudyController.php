<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use Illuminate\View\View;

class CaseStudyController extends Controller
{
    public function index(): View
    {
        $cases = CaseStudy::query()
            ->active()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(12);

        return view('pages.cases', compact('cases'));
    }
}
