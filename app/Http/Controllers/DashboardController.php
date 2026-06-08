<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\Enquiry;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\SupportQuery;
use App\Models\University;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $startOfThisMonth = now()->startOfMonth();
        $startOfLastMonth = now()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth   = now()->subMonthNoOverflow()->endOfMonth();
        $startOfToday     = now()->startOfDay();
        $startOfThisWeek  = now()->startOfWeek();

        // ───────────────────── Students analytics ─────────────────────
        // Sub-admin only sees their own students; admin sees everyone's.

        $studentsBase = Student::query();
        if (! $isAdmin) {
            $studentsBase->where('created_by', $user->id);
        }

        $studentCounts = [
            'today'      => (clone $studentsBase)->whereDate('created_at', today())->count(),
            'yesterday'  => (clone $studentsBase)->whereDate('created_at', today()->subDay())->count(),
            '7'          => (clone $studentsBase)->where('created_at', '>=', now()->subDays(7))->count(),
            '15'         => (clone $studentsBase)->where('created_at', '>=', now()->subDays(15))->count(),
            '30'         => (clone $studentsBase)->where('created_at', '>=', now()->subDays(30))->count(),
            'total'      => (clone $studentsBase)->count(),
            'this_month' => (clone $studentsBase)->where('created_at', '>=', $startOfThisMonth)->count(),
            'last_month' => (clone $studentsBase)
                                ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                                ->count(),
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

        // University-wise student breakdown (top 6 by count).
        $studentsByUniversity = (clone $studentsBase)
            ->select('university_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('university_id')
            ->groupBy('university_id')
            ->get()
            ->map(function ($row) {
                return [
                    'university' => University::find($row->university_id)?->name ?? '—',
                    'total'      => (int) $row->total,
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->take(6);

        // ───────────────────── Fee analytics ─────────────────────
        //
        // Total fee to collect is the sum, across every student in the
        // scope, of that student's full course fee. We compute it once
        // per course (total = registration + fee_per_period × periods)
        // and multiply by the student count for that course, which
        // keeps the query small even for thousands of students.

        $courseTotals = Course::all()->mapWithKeys(fn ($c) => [$c->id => (float) $c->totalFee()]);

        $studentCourseCounts = (clone $studentsBase)
            ->select('course_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('course_id')
            ->groupBy('course_id')
            ->pluck('total', 'course_id');

        $totalToCollect = 0.0;
        foreach ($studentCourseCounts as $courseId => $studentCount) {
            $totalToCollect += ($courseTotals[$courseId] ?? 0) * (int) $studentCount;
        }

        $paymentsBase = FeePayment::query();
        if (! $isAdmin) {
            $paymentsBase->whereHas('student', fn ($q) => $q->where('created_by', $user->id));
        }

        $totalCollected      = (float) (clone $paymentsBase)->sum('amount');
        $totalRemaining      = max(0, $totalToCollect - $totalCollected);
        $collectedThisMonth  = (float) (clone $paymentsBase)
            ->where('paid_at', '>=', $startOfThisMonth)
            ->sum('amount');
        $collectedLastMonth  = (float) (clone $paymentsBase)
            ->whereBetween('paid_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('amount');
        $collectedToday      = (float) (clone $paymentsBase)
            ->where('paid_at', '>=', $startOfToday)
            ->sum('amount');
        $collectedThisWeek   = (float) (clone $paymentsBase)
            ->where('paid_at', '>=', $startOfThisWeek)
            ->sum('amount');

        // Master fee-structure-level summary (admin only — sub-admin
        // sees the per-student version above which is what they care
        // about). Cheap to compute even for many courses.
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

        // 14-day collection trend (paid_at) for the area chart on the
        // detailed fee section — gives admin a quick eye on cash flow.
        $collectionWindow = (clone $paymentsBase)
            ->where('paid_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['paid_at', 'amount'])
            ->groupBy(fn ($p) => $p->paid_at?->format('Y-m-d') ?? now()->format('Y-m-d'))
            ->map(fn ($g) => (float) $g->sum('amount'));

        $collectionTrend = ['labels' => [], 'data' => []];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $collectionTrend['labels'][] = $d->format('d M');
            $collectionTrend['data'][]   = $collectionWindow[$d->format('Y-m-d')] ?? 0;
        }

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

        // ───────────────────── Enquiries (admin only) ─────────────────────
        // Sub-admin doesn't see enquiries on their dashboard.
        $enquiryStats = ['total' => 0, 'pending' => 0, 'approved' => 0];
        if ($isAdmin && Schema::hasTable('enquiries')) {
            $enquiryStats = [
                'total'    => Enquiry::count(),
                'pending'  => Enquiry::where('status', Enquiry::STATUS_PENDING)->count(),
                'approved' => Enquiry::where('status', Enquiry::STATUS_APPROVED)->count(),
            ];
        }

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
            if (Schema::hasTable('enquiries')) {
                Enquiry::latest()->limit(5)->get(['id', 'name', 'subject', 'created_at'])
                    ->each(function ($e) use (&$activities) {
                        $activities->push([
                            'type'  => 'enquiry',
                            'title' => 'New enquiry',
                            'meta'  => $e->name.($e->subject ? ' — '.$e->subject : ''),
                            'at'    => $e->created_at,
                            'href'  => route('enquiries.index'),
                        ]);
                    });
            }

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

        // Roll the live fee aggregates into one array the view destructures.
        $feeSummary = [
            'total_to_collect'      => $totalToCollect,
            'total_collected'       => $totalCollected,
            'total_remaining'       => $totalRemaining,
            'collected_this_month'  => $collectedThisMonth,
            'collected_last_month'  => $collectedLastMonth,
            'collected_today'       => $collectedToday,
            'collected_this_week'   => $collectedThisWeek,
            'student_count_with_fee'=> (int) $studentCourseCounts->sum(),
        ];

        return view('dashboard', compact(
            'user',
            'isAdmin',
            'studentCounts',
            'studentChart',
            'studentsByUniversity',
            'feeSummary',
            'collectionTrend',
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
