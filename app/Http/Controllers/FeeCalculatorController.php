<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\FeeStructure;
use App\Models\University;
use Illuminate\View\View;

class FeeCalculatorController extends Controller
{
    public function index(): View
    {
        $universities = University::orderBy('name')->get(['id', 'name', 'type', 'registration_fee']);

        // Every course + its fee structure, shipped to the page so the
        // form can filter the course dropdown by university and run the
        // calculation client-side as the user types.
        $coursesData = Course::with('feeStructure')
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id'             => $c->id,
                'university_id'  => $c->university_id,
                'name'           => $c->name,
                'mode'           => $c->mode,
                'duration_years' => (float) $c->duration_years,
                'semesters'      => $c->semesterCount(),
                'fee_per_sem'    => (float) ($c->feeStructure?->fee_per_sem ?? 0),
                'has_fee'        => $c->feeStructure !== null,
            ])
            ->values();

        $stats = [
            'universities' => $universities->count(),
            'courses'      => $coursesData->count(),
            'fees'         => FeeStructure::count(),
        ];

        return view('fee-calculator.index', [
            'universities' => $universities,
            'coursesData'  => $coursesData,
            'stats'        => $stats,
        ]);
    }
}
