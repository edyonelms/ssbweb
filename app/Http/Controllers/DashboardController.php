<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\SupportQuery;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        // ───────────────────── Students analytics ─────────────────────

        $studentsBase = Student::query();
        if (! $isAdmin) {
            $studentsBase->where('created_by', $user->id);
        }

        $studentCounts = [
            'today'     => (clone $studentsBase)->whereDate('created_at', today())->count(),
            'yesterday' => (clone $studentsBase)->whereDate('created_at', today()->subDay())->count(),
            '7'         => (clone $studentsBase)->where('created_at', '>=', now()->subDays(7))->count(),
            '15'        => (clone $studentsBase)->where('created_at', '>=', now()->subDays(15))->count(),
            '30'        => (clone $studentsBase)->where('created_at', '>=', now()->subDays(30))->count(),
        ];

        // Day-by-day breakdown for the last 30 days (for the line chart).
        $window = (clone $studentsBase)
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->get(['created_at'])
            ->groupBy(fn ($s) => $s->created_at->format('Y-m-d'))
            ->map->count();

        $studentChart = [
            'labels' => [],
            'data'   => [],
        ];
        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $studentChart['labels'][] = $d->format('d M');
            $studentChart['data'][]   = $window[$d->format('Y-m-d')] ?? 0;
        }

        // ───────────────────── Fee analytics ─────────────────────

        $feeStructures = FeeStructure::with(['university:id,name', 'course:id,name,duration_years'])->get();
        $totalRegistrationFee = (float) University::sum('registration_fee');
        $totalCourseFees      = (float) $feeStructures->sum(fn ($f) => $f->totalFee());
        $totalCourses         = $feeStructures->count();

        $feesByUniversity = $feeStructures
            ->groupBy('university_id')
            ->map(function ($group) {
                return [
                    'university' => $group->first()->university?->name ?? '—',
                    'total'      => (float) $group->sum(fn ($f) => $f->totalFee()),
                    'count'      => $group->count(),
                ];
            })
            ->values()
            ->sortByDesc('total')
            ->values()
            ->take(6);

        $feeChart = [
            'labels' => $feesByUniversity->pluck('university')->all(),
            'data'   => $feesByUniversity->pluck('total')->all(),
        ];

        // ───────────────────── Support analytics ─────────────────────

        $supportBase = SupportQuery::query();
        if (! $isAdmin) {
            $supportBase->where('user_id', $user->id);
        }
        $supportStats = [
            'total'   => (clone $supportBase)->count(),
            'pending' => (clone $supportBase)->where('status', SupportQuery::STATUS_PENDING)->count(),
            'replied' => (clone $supportBase)->where('status', SupportQuery::STATUS_APPROVED)->count(),
        ];

        // ───────────────────── Enquiries (placeholder) ─────────────────────
        // No dedicated enquiries module yet — surface zeros so the card slot
        // is wired up and ready when the module lands.
        $enquiryStats = [
            'total'    => 0,
            'pending'  => 0,
            'approved' => 0,
        ];

        // ───────────────────── Recent activity ─────────────────────

        $activities = collect();

        // Students they (or anyone, for admin) added
        (clone $studentsBase)->latest()->limit(8)->get(['id', 'name', 'created_at'])
            ->each(function ($s) use (&$activities) {
                $activities->push([
                    'type'  => 'student',
                    'title' => 'Student added',
                    'meta'  => $s->name,
                    'at'    => $s->created_at,
                    'href'  => route('students.index'),
                ]);
            });

        // Announcements (admin sees all; sub-admin sees those addressed to them)
        $annQuery = Announcement::query()
            ->where('created_at', '>=', now()->subDays(60))
            ->latest()
            ->limit(8);
        if (! $isAdmin) {
            $annQuery->where(function ($q) use ($user) {
                $q->where('audience', Announcement::AUDIENCE_ALL)
                  ->orWhereHas('recipients', fn ($r) => $r->where('users.id', $user->id));
            });
        }
        $annQuery->get(['id', 'heading', 'created_at'])
            ->each(function ($a) use (&$activities, $isAdmin) {
                $activities->push([
                    'type'  => 'announcement',
                    'title' => $isAdmin ? 'Announcement published' : 'New announcement',
                    'meta'  => $a->heading,
                    'at'    => $a->created_at,
                    'href'  => route('announcements.index'),
                ]);
            });

        // Support — created and replied (latest activity at the query)
        (clone $supportBase)->latest('updated_at')->limit(8)->get(['id', 'subject', 'status', 'updated_at', 'created_at'])
            ->each(function ($q) use (&$activities) {
                $isReply = $q->status === SupportQuery::STATUS_APPROVED;
                $activities->push([
                    'type'  => 'support',
                    'title' => $isReply ? 'Support replied' : 'Support raised',
                    'meta'  => $q->subject,
                    'at'    => $q->updated_at ?? $q->created_at,
                    'href'  => route('support.index'),
                ]);
            });

        if ($isAdmin) {
            FeeStructure::with('course:id,name')->latest()->limit(5)->get()
                ->each(function ($f) use (&$activities) {
                    $activities->push([
                        'type'  => 'fee',
                        'title' => 'Fee structure updated',
                        'meta'  => $f->course?->name ?? 'course',
                        'at'    => $f->updated_at ?? $f->created_at,
                        'href'  => route('master.index', ['tab' => 'fees']),
                    ]);
                });

            University::latest()->limit(5)->get(['id', 'name', 'created_at'])
                ->each(function ($u) use (&$activities) {
                    $activities->push([
                        'type'  => 'university',
                        'title' => 'University added',
                        'meta'  => $u->name,
                        'at'    => $u->created_at,
                        'href'  => route('master.index', ['tab' => 'university']),
                    ]);
                });
        }

        $activities = $activities
            ->filter(fn ($a) => $a['at'] !== null)
            ->sortByDesc('at')
            ->take(12)
            ->values();

        return view('dashboard', compact(
            'user',
            'isAdmin',
            'studentCounts',
            'studentChart',
            'totalRegistrationFee',
            'totalCourseFees',
            'totalCourses',
            'feeChart',
            'feesByUniversity',
            'supportStats',
            'enquiryStats',
            'activities',
        ));
    }
}
