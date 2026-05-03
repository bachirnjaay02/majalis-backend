<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(
            User::withCount('orders')->orderBy('created_at', 'desc')->get()->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'phone' => $u->phone ?? '',
                'joined' => $u->created_at->format('Y-m-d'),
                'orders_count' => $u->orders_count,
            ])
        );
    }
}