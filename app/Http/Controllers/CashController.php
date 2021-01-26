<?php

namespace App\Http\Controllers;

use App\Http\Resources\CashResource;
use App\Models\Cash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CashController extends Controller
{
    public function index()
    {
        $from = request('from');
        $to = request('to');

        if ($from && $to) {
            $debit = $this->getBalances($from, $to, '>=');
            $credit = $this->getBalances($from, $to, '<=');

            $transaction = Auth::user()->cashes()
                ->whereBetween('when', [$from . ' 00:00:00', $to . ' 23:59:59'])
                ->latest()->get();
        } else {
            $debit = $this->getBalances(now()->firstOfMonth(), now(), '>=');
            $credit = $this->getBalances(now()->firstOfMonth(), now(), '<');

            $transaction = Auth::user()->cashes()
                ->whereBetween('when', [now()->firstOfMonth() . ' 00:00:00', now() . ' 23:59:59'])
                ->latest()->get();
        }


        return response()->json([
            'debit' => formatPrice($debit),
            'credit' => formatPrice($credit),
            'balances' => formatPrice(Auth::user()->cashes()->get('amount')->sum('amount')),
            'transactions' => CashResource::collection($transaction),
            'now' => now()->format('Y-m-d'),
            'firstOfMonth' => now()->firstOfMonth()->format('Y-m-d'),
        ]);
    }

    public function store()
    {
        request()->validate([
            'name' => 'required',
            'amount' => 'required|numeric',
            'when' => 'required|date',
        ]);

        $when = request('when') ?? now();
        $slug = request('name') . '-' . Str::random(6);

        $cash = Auth::user()->cashes()->create([
            'name' => request('name'),
            'slug' => Str::slug($slug),
            'when' => $when,
            'amount' => request('amount'),
            'description' => request('description'),
        ]);

        return response()->json([
            'message' => 'The transaction has been saved.',
            'cash' => new CashResource($cash),
        ]);
    }

    public function show(Cash $cash)
    {
        $this->authorize('show', $cash);

        return new CashResource($cash);
    }

    public function getBalances($from, $to, $operator)
    {
        return Auth::user()->cashes()
            ->whereBetween('when', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('amount', $operator, 0)
            ->get('amount')->sum('amount');
    }
}
