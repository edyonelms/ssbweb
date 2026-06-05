<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enquiry;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MarketingController extends Controller
{
    public function home(): View
    {
        // Pull live master-data so the landing page reflects what the admin
        // has actually configured. Stays safe before migrations run.
        $universities = collect();
        $courses      = collect();

        if (Schema::hasTable('universities')) {
            $universities = University::orderBy('name')->take(8)->get();
        }
        if (Schema::hasTable('courses')) {
            $courses = Course::with('university:id,name')->latest()->take(8)->get();
        }

        return view('marketing.home', [
            'universities' => $universities,
            'courses'      => $courses,
            'loginUrl'     => $this->loginUrl(),
        ]);
    }

    public function storeEnquiry(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['nullable', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $data['source'] = Enquiry::SOURCE_WEB;
        $data['status'] = Enquiry::STATUS_PENDING;

        Enquiry::create($data);

        return redirect()
            ->to(url()->previous().'#contact')
            ->with('status', 'Thanks! We will get back to you shortly.');
    }

    /**
     * Marketing's Login CTA points at the splash screen rather than the
     * raw login form so visitors see the campus image first and click
     * "Continue to Login" to actually sign in.
     */
    private function loginUrl(): string
    {
        return route('welcome');
    }
}
