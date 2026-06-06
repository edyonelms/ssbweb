<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WalletController extends Controller
{
    private const TABS = ['history', 'transactions', 'requests'];

    /** Allowed scope filters used on the History tab for admin. */
    private const SCOPE_OPTIONS = ['all', 'self', 'others'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $tab = in_array($request->query('tab'), self::TABS, true)
            ? $request->query('tab')
            : 'history';

        $mode = in_array($request->query('mode'), array_merge(['all'], WalletTransaction::MODES), true)
            ? $request->query('mode')
            : 'all';

        $search = trim((string) $request->query('q', ''));

        $scope = in_array($request->query('scope'), self::SCOPE_OPTIONS, true)
            ? $request->query('scope')
            : 'all';

        // ─── Build the list query for the active tab ───
        $query = WalletTransaction::with([
                'user:id,name,mobile,role',
                'creator:id,name,role',
                'paymentRequest:id,wallet_transaction_id,topic',
            ])
            ->orderByDesc('id');

        if ($isAdmin) {
            // History → only what *I* disbursed.
            // Transactions → everything in the system.
            if ($tab === 'history') {
                $query->where('created_by', $user->id);

                // Scope chip narrows the history further:
                //   self   → credits I gave to my own wallet
                //   others → credits I gave to anyone else (typically sub-admins)
                if ($scope === 'self') {
                    $query->where('user_id', $user->id);
                } elseif ($scope === 'others') {
                    $query->where('user_id', '!=', $user->id);
                }
            }
        } else {
            // Sub-admin: both tabs are scoped to their own credits.
            $query->where('user_id', $user->id);
        }

        if ($mode !== 'all') {
            $query->where('mode', $mode);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', $like))
                  ->orWhere('note', 'like', $like);
            });
        }

        $transactions = $query->get();

        // ─── Stats (role-aware, NOT affected by chip selection) ───
        $myBalance = WalletTransaction::balanceFor($user->id);

        if ($isAdmin) {
            $disbursed = (float) WalletTransaction::where('created_by', $user->id)->sum('amount');
            $stats = [
                'balance'      => $myBalance,
                'disbursed'    => $disbursed,
                'transactions' => WalletTransaction::count(),
                'system_total' => (float) WalletTransaction::sum('amount'),
            ];
        } else {
            $stats = [
                'balance'      => $myBalance,
                'received'     => (float) WalletTransaction::where('user_id', $user->id)->sum('amount'),
                'transactions' => WalletTransaction::where('user_id', $user->id)->count(),
            ];
        }

        // ─── Wallet recipients (admin first, then sub-admins alphabetically). ───
        $users = User::orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get(['id', 'name', 'mobile', 'role']);

        // ─── Payment requests for the "Ask Payment" tab ───
        $requestsQuery = PaymentRequest::with([
                'user:id,name,mobile,role,avatar_path',
                'decidedBy:id,name,role',
                'walletTransaction:id,amount,mode',
            ])
            ->orderByDesc('id');

        if (! $isAdmin) {
            // Sub-admin only sees their own asks.
            $requestsQuery->where('user_id', $user->id);
        }

        if ($search !== '' && $tab === 'requests') {
            $like = '%'.$search.'%';
            $requestsQuery->where(function ($q) use ($like, $isAdmin) {
                $q->where('topic', 'like', $like)
                  ->orWhere('admin_note', 'like', $like);
                if ($isAdmin) {
                    $q->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like));
                }
            });
        }

        $paymentRequests = $requestsQuery->get();

        $requestStats = [
            'pending'  => $paymentRequests->where('status', PaymentRequest::STATUS_PENDING)->count(),
            'approved' => $paymentRequests->where('status', PaymentRequest::STATUS_APPROVED)->count(),
            'rejected' => $paymentRequests->where('status', PaymentRequest::STATUS_REJECTED)->count(),
        ];

        return view('wallet.index', [
            'tab'             => $tab,
            'mode'            => $mode,
            'scope'           => $scope,
            'search'          => $search,
            'transactions'    => $transactions,
            'stats'           => $stats,
            'users'           => $users,
            'isAdmin'         => $isAdmin,
            'authUser'        => $user,
            'paymentRequests' => $paymentRequests,
            'requestStats'    => $requestStats,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount'  => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'mode'    => ['required', 'in:'.implode(',', WalletTransaction::MODES)],
            'note'    => ['nullable', 'string', 'max:500'],
        ]);

        $data['created_by'] = $request->user()->id;

        $txn = WalletTransaction::create($data);

        $recipient = User::find($txn->user_id);
        $action = $txn->amount >= 0 ? 'wallet.credited' : 'wallet.debited';
        $verb   = $txn->amount >= 0 ? 'Credited' : 'Debited';
        $summary = $verb.' ₹'.number_format(abs((float) $txn->amount), 2)
            .' '.($txn->amount >= 0 ? 'to ' : 'from ').($recipient->name ?? 'wallet');

        ActivityLog::record($action, $summary, $txn, [
            'recipient_id' => $txn->user_id,
            'amount'       => (float) $txn->amount,
            'mode'         => $txn->mode,
        ]);

        return redirect()
            ->route('wallet.index')
            ->with('status', 'Wallet updated.');
    }

    // ────────────────────────────────────────────────────────────────────
    //  Payment Requests ("Ask Payment")
    // ────────────────────────────────────────────────────────────────────

    public function storeRequest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'amount'     => ['required', 'numeric', 'min:1', 'max:99999999'],
            'topic'      => ['required', 'string', 'max:255'],
            'screenshot' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ], [
            'screenshot.image' => 'Screenshot must be a PNG/JPG/WEBP image.',
            'screenshot.max'   => 'Screenshot must be 4MB or smaller.',
        ]);

        $payload = [
            'user_id' => $request->user()->id,
            'amount'  => $data['amount'],
            'topic'   => $data['topic'],
            'status'  => PaymentRequest::STATUS_PENDING,
        ];

        if ($request->hasFile('screenshot')) {
            $payload['screenshot_path'] = $request->file('screenshot')->store('uploads/payment-requests', 'public');
        }

        $reqModel = PaymentRequest::create($payload);

        ActivityLog::record(
            'wallet.requested',
            'Requested ₹'.number_format((float) $reqModel->amount, 2).' — '.$reqModel->topic,
            $reqModel,
            ['amount' => (float) $reqModel->amount]
        );

        return redirect()
            ->route('wallet.index', ['tab' => 'requests'])
            ->with('status', 'Payment request submitted.');
    }

    public function approveRequest(Request $request, PaymentRequest $paymentRequest): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($paymentRequest->isPending(), 422, 'This request has already been decided.');

        $data = $request->validate([
            'approved_amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'mode'            => ['required', 'in:'.implode(',', WalletTransaction::MODES)],
            'admin_note'      => ['nullable', 'string', 'max:500'],
        ]);

        $txn = WalletTransaction::create([
            'user_id'    => $paymentRequest->user_id,
            'amount'     => $data['approved_amount'],
            'mode'       => $data['mode'],
            'note'       => 'Approved request: '.$paymentRequest->topic
                .($data['admin_note'] ?? '' ? ' — '.$data['admin_note'] : ''),
            'created_by' => $request->user()->id,
        ]);

        $paymentRequest->update([
            'approved_amount'       => $data['approved_amount'],
            'status'                => PaymentRequest::STATUS_APPROVED,
            'decided_by'            => $request->user()->id,
            'decided_at'            => now(),
            'wallet_transaction_id' => $txn->id,
            'admin_note'            => $data['admin_note'] ?? null,
        ]);

        $recipient = $paymentRequest->user;
        ActivityLog::record(
            'wallet.request_approved',
            'Approved ₹'.number_format((float) $data['approved_amount'], 2)
                .' for '.($recipient->name ?? 'sub-admin').' — '.$paymentRequest->topic,
            $paymentRequest,
            [
                'requested'   => (float) $paymentRequest->amount,
                'approved'    => (float) $data['approved_amount'],
                'recipient_id'=> $paymentRequest->user_id,
            ]
        );

        return redirect()
            ->route('wallet.index', ['tab' => 'requests'])
            ->with('status', 'Request approved and wallet credited.');
    }

    public function rejectRequest(Request $request, PaymentRequest $paymentRequest): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($paymentRequest->isPending(), 422, 'This request has already been decided.');

        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $paymentRequest->update([
            'status'     => PaymentRequest::STATUS_REJECTED,
            'decided_by' => $request->user()->id,
            'decided_at' => now(),
            'admin_note' => $data['admin_note'] ?? null,
        ]);

        ActivityLog::record(
            'wallet.request_rejected',
            'Rejected request "'.$paymentRequest->topic.'" from '.($paymentRequest->user->name ?? 'sub-admin'),
            $paymentRequest
        );

        return redirect()
            ->route('wallet.index', ['tab' => 'requests'])
            ->with('status', 'Request rejected.');
    }

    public function destroyRequest(Request $request, PaymentRequest $paymentRequest): RedirectResponse
    {
        $user = $request->user();

        // Owners can withdraw a pending request; admins can clear any request.
        if (! $user->isAdmin()) {
            abort_if($paymentRequest->user_id !== $user->id, 403);
            abort_unless($paymentRequest->isPending(), 403, 'Decided requests can no longer be withdrawn.');
        }

        if ($paymentRequest->screenshot_path && Storage::disk('public')->exists($paymentRequest->screenshot_path)) {
            Storage::disk('public')->delete($paymentRequest->screenshot_path);
        }

        $paymentRequest->delete();

        return redirect()
            ->route('wallet.index', ['tab' => 'requests'])
            ->with('status', 'Request removed.');
    }
}
