<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Intervention\Image\ImageManager;

class ProductsController extends Controller
{
     public function index()
    {
        return Inertia::render('Product/Creaciones', [
            'products' => Product::all()->load('images')
        ]);
    }

    public function create()
    {
        return inertia('Product/Create');
    }

    public function viewImages()
    {
        return Inertia::render('Product/ShowImages', [
            'images' => Image::get()->map(function($image) {
                return [
                    'image' => $image,
                    'src' => Storage::url($image->url),
                    'size' => Storage::disk('public')->size($image->url)
                ];
            })
        ]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'landing' => 'required',
            'images' => 'required|array',
        ]);

        if ($request->landing && !collect($request->images)->contains('isMain', true)) {
            return back()->withErrors(['images' => 'You must select a main image']);
        }
        
        $product = Product::create(
            $request->only('name', 'description', 'landing')
        );

        foreach ($request->images as $image) {
            $url = $image['file']->store('creaciones_images', 'public');

            $interventionImage = ImageManager::gd()->read(storage_path('app/public/' . $url));
            $interventionImage->coverDown(400,600);
            $interventionImage->save(storage_path('app/public/' . $url), 75);

            $product->images()->create([
                'url' => $url,
                'alt' => $product->name,
                'main' => $image['isMain']
            ]);
        }

        return redirect()->route('creaciones');
    }

    public function show(Product $product)
    {
        return inertia('Products/Show', [
            'product' => $product
        ]);
    }

    public function edit(Product $product)
    {
        return inertia('Product/Update', [
            'product' => $product->load('images')
        ]);
    }

    public function update(Request $request, Product $product)
    {

        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'landing' => 'required',
            'images' => 'array',
        ]);

        if ($request->landing && !collect($request->images)->contains('isMain', true)) {
            return back()->withErrors(['images' => 'You must select a main image']);
        }
        
        $product->update(
            $request->only('name', 'description', 'landing')
        );   

        if (array_filter($request->images, fn($image) => $image['file'] !== null)) {
            
            foreach($product->images as $image) {
                Storage::disk('public')->delete($image->url);
            }
            if($product->images()->count() > 0)
                $product->images()->delete();

            foreach ($request->images as $image) {
                $url = $image['file']->store('creaciones_images', 'public');
                $interventionImage = ImageManager::gd()->read(storage_path('app/public/' . $url));
                $interventionImage->coverDown(400,600);
                $interventionImage->save(storage_path('app/public/' . $url), 75);
                $product->images()->create([
                    'url' => $url,
                    'alt' => $product->name,
                    'main' => $image['isMain']
                ]);
            }
        }


        return redirect()->route('creaciones');
    }

    public function destroy(Product $product)
    {
        if($product->images()->count() > 0) {
            foreach($product->images as $image) {
                Storage::disk('public')->delete($image->url);
            }
        }

        $product->delete();

        return redirect()->route('creaciones');
    }

    public function optimizeImages()
    {
        $images = Image::get();

        $images->each(function($image) use (&$urls) {
            $url = $image->url;
            $interventionImage = ImageManager::gd()->read(storage_path('app/public/' . $url));
            $interventionImage->coverDown(400,600);
            $interventionImage->save(storage_path('app/public/' . $url), 75);
        });

        return redirect()->route('creaciones.images');
    }    
}
