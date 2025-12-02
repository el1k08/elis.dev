<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index()
    {
        return Inertia::render('Accounts/Index', [
            'accounts' => Account::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'type'   => ['required', 'in:cash,checking,savings,credit_card,investment,loan,mortgage,other'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        Account::create($data); // user_id проставится через BelongsToUser

        return redirect()->route('accounts.index');
    }
}
