<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {

        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        if ($search) {
            $products = Product::where('product_id', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%")
                ->orWhere('price', 'like', "%$search%")
                ->orWhere('stock', 'like', "%$search%")
                ->orderBy($sortBy, $sortOrder)
                ->paginate(10);
        } else {

            $products = Product::orderBy($sortBy, $sortOrder)->paginate(10);
        }


        return view('index', compact('products', 'search', 'sortBy', 'sortOrder'));
    }

    public function create()
    {
        return view('create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|unique:products',
            'name' => 'required',
            'price' => 'required|numeric',
            'description' => 'nullable',
            'stock' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/products');
            $imageName = basename($imagePath);
        } else {
            $imageName = null;
        }

        Product::create([
            'product_id' => $request->product_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imageName,
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('edit', compact('product'));
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'stock' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        $product = Product::findOrFail($id);

        $product->name = $request->input('name');
        $product->price = $request->input('price');
        $product->description = $request->input('description');
        $product->stock = $request->input('stock');

        if ($request->hasFile('image')) {
            if ($product->image && Storage::exists('public/products/' . $product->image)) {
                Storage::delete('public/products/' . $product->image);
            }

            $imagePath = $request->file('image')->store('public/products');
            $imageName = basename($imagePath);
        } else {
            $imageName = $product->image;
        }

        $product->update([
            'product_id' => $request->product_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imageName,
        ]);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully');
    }
}
