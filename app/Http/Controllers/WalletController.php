<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    private const TABS = ['history', 'transactions'];

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

        // ─── Build the list query for the active tab ───
        $query = WalletTransaction::with(['user:id,name,mobile,role', 'creator:id,name,role'])
            ->orderByDesc('id');

        if ($isAdmin) {
            // History → only what *I* disbursed.
            // Transactions → everything in the system.
            if ($tab === 'history') {
                $query->where('created_by', $user->id);
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

        return view('wallet.index', [
            'tab'          => $tab,
            'mode'         => $mode,
            'search'       => $search,
            'transactions' => $transactions,
            'stats'        => $stats,
            'users'        => $users,
            'isAdmin'      => $isAdmin,
            'authUser'     => $user,
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

        WalletTransaction::create($data);

        return redirect()
            ->route('wallet.index')
            ->with('status', 'Wallet updated.');
    }
}
