<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $orders = Order::with('user')->orderBy('created_at', 'desc')->get();
        } else {
            $orders = Order::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        }

        return response()->json($orders->map(fn($o) => $this->formatOrder($o)));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.productId' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'total' => 'required|numeric',
            'payment' => 'nullable|string',
        ]);

        $user = $request->user();
        $reference = 'CMD-' . strtoupper(substr(uniqid(), -6));

        $order = Order::create([
            'reference' => $reference,
            'user_id' => $user->id,
            'client_name' => $user->name,
            'client_phone' => $user->phone ?? '',
            'items' => $request->items,
            'total' => $request->total,
            'payment' => $request->payment ?? 'Cash',
            'status' => 'en attente',
        ]);

        return response()->json($this->formatOrder($order), 201);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:en attente,en cours,livré,annulé',
        ]);

        $previousStatus = $order->status;
        $newStatus = $request->status;

        if ($newStatus === 'livré' && $previousStatus !== 'livré') {
            foreach ($order->items as $item) {
                $product = Product::find($item['productId']);
                if ($product) {
                    $product->stock = max(0, $product->stock - $item['qty']);
                    $product->save();
                }
            }
        }

        $order->update(['status' => $newStatus]);

        return response()->json($this->formatOrder($order));
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->reference,
            'dbId' => $order->id,
            'date' => $order->created_at->format('Y-m-d'),
            'client' => $order->client_name,
            'clientId' => $order->user_id,
            'phone' => $order->client_phone,
            'items' => $order->items,
            'total' => (float) $order->total,
            'payment' => $order->payment,
            'status' => $order->status,
        ];
    }
}