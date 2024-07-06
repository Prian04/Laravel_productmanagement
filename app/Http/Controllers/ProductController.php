<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str; 

class ProductController extends Controller
{
    public function index(Request $request){
        // $products = Product::all();
        // return response()->json($products);
        //return view('products');

         //  $products = Product::all();
         // return view('products', compact('products'));
         return view('products');
    }

    // public function fetch_product()
    // {
    //     $products = Product::all();
    //     return response()->json([
    //         'products'=>$products,
    //     ]);
    // }

    public function getProducts(){

        $products = Product::all();
        $data = [];

        foreach ($products as $product) {
            $images = json_decode($product->product_images);
            $imageHtml = '';
            if (is_array($images)) {
                foreach ($images as $image) {
                    $imageHtml .= '<img src="' . $image . '" height="50" style="margin-right: 5px;" />';
                }
            }
            $data[] = [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'product_description' => $product->product_description,
                'product_price' => $product->product_price,
                'product_images' => $imageHtml,
                'action' => '<div class="d-flex align-items-center"><button class="btn btn-primary edit-product mx-1" data-id="' . $product->id . '"><i class="fas fa-edit"></i></button><button class="btn btn-danger delete-product mx-1" data-id="' . $product->id . '"><i class="fa fa-trash" aria-hidden="true"></i>
</button></div>'
            ];
        }

        return response()->json(['data' => $data]);
}

    public function store(Request $request){
        $data = $request->validate([
            'product_name' => 'required',
            'product_price' => 'required|numeric',
            'product_description' => 'required',
            'product_images.*' => 'required|image',
        ]);

        $images = [];
        if($request->hasFile('product_images')){
            foreach($request->file('product_images') as $image){
                // $path = $image->store('products', 'public');
                // $images[] = $path;
                $fileName = Str::random(20) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $fileName);
            $images[] = 'uploads/' . $fileName; // Store relative path to database
            }
        } else {
            $error = $image->getErrorMessage();
            \Log::error("Failed to upload image: $error");

        }

        $data['product_images'] = json_encode($images);

        Product::create($data);

        return response()->json(['success'=>'Product added successfully.']);
    }

    public function edit(Request $request){
        $id = request()->route('id');
        $data = Product::find($id);
        return response()->json($data);
    }

    public function update(Request $request){
       $data = $request->validate([
            'id' => 'required|exists:products,id',
            'product_name' => 'required',
            'product_price' => 'required|numeric',
            'product_description' => 'required',
            'product_images.*' => 'sometimes|image',
        ]);

        $product = Product::findOrFail($data['id']);

        // Handle product image updates
        $images = json_decode($product->product_images, true);
        if ($request->hasFile('product_images')) {
            foreach ($request->file('product_images') as $image) {
                // $path = $image->store('products', 'public');
                // $images[] = $path;

                $fileName = Str::random(20) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $fileName);
            $images[] = 'uploads/' . $fileName; 
            }
        } 

        $data['product_images'] = json_encode($images);

        // Update product data
        $product->update([
            'product_name' => $data['product_name'],
            'product_price' => $data['product_price'],
            'product_description' => $data['product_description'],
            'product_images' => $data['product_images'],
        ]);

        return response()->json(['success' => 'Product updated successfully.']);
    }

    public function destroy(Request $request){
        $id = request()->route('id');
        $product = Product::find($id);
        $product->delete(); 
        

        return response()->json(['success' => 'Product deleted successfully.']);
    }
}
