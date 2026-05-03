<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'image' => 'nullable',
        ]);

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $uploaded = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'majalis-store/products',
            ]);
            $imageUrl = $uploaded->getSecurePath();
        } elseif ($request->image && str_starts_with($request->image, 'http')) {
            $imageUrl = $request->image;
        }

        $product = Product::create([
            'name' => $request->name,
            'category' => $request->category,
            'sku' => $request->sku,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imageUrl,
        ]);

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'category' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'image' => 'nullable',
        ]);

        $imageUrl = $product->image;

        if ($request->hasFile('image')) {
            $uploaded = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'majalis-store/products',
            ]);
            $imageUrl = $uploaded->getSecurePath();
        } elseif ($request->has('image') && str_starts_with($request->image ?? '', 'http')) {
            $imageUrl = $request->image;
        }

        $product->update([
            'name' => $request->name ?? $product->name,
            'category' => $request->category ?? $product->category,
            'sku' => $request->sku ?? $product->sku,
            'price' => $request->price ?? $product->price,
            'stock' => $request->stock ?? $product->stock,
            'image' => $imageUrl,
        ]);

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Produit supprimé']);
    }
}