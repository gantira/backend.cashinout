<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CashController extends Controller
{
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
