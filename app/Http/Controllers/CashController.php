<?php

namespace App\Http\Controllers;

use App\Http\Resources\CashResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CashController extends Controller
{
    public function index()
    {
        $debit = Auth::user()->cashes()
            ->whereBetween('when', [now()->firstOfMonth(), now()])
            ->where('amount', '>=', 0)
            ->get('amount')->sum('amount');

        $credit = Auth::user()->cashes()
            ->whereBetween('when', [now()->firstOfMonth(), now()])
            ->where('amount', '<', 0)
            ->get('amount')->sum('amount');

        $transaction = Auth::user()->cashes()
            ->whereBetween('when', [now()->firstOfMonth(), now()])
            ->latest()->get();

        $balance = $debit + $credit;

        return response()->json([
            'debit' => formatPrice($debit),
            'credit' => formatPrice($credit),
            'balance' => formatPrice($balance),
            'transaction' => CashResource::collection($transaction),
        ]);
    }

    public function store()
    {
        request()->validate([
            'name' => 'required',
            'amount' => 'required|numeric',
        ]);

        $when = request('when') ?? now();
        $slug = request('name') . '-' . Str::random(6);

        Auth::user()->cashes()->create([
            'name' => request('name'),
            'slug' => Str::slug($slug),
            'when' => $when,
            'amount' => request('amount'),
            'description' => request('description'),
        ]);

        return response()->json([
            'message' => 'The transaction has been saved.'
        ]);
    }
}
