<?php

namespace App\Http\Controllers;

use App\PhysicalProduct;
use App\Product;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        // If user is logged in
        if(!auth() -> guest())
            $this->authorize('view', $product);
        elseif($product -> active == false)
            abort(404);


        return view('product.index', [
            'product' => $product,
        ]);
    }

    public function showRules(Product $product)
    {
        // If user is logged in
        if(!auth() -> guest())
            $this->authorize('view', $product);
        elseif($product -> active == false)
            abort(404);


        return view('product.rules', [
            'product' => $product,
        ]);
    }

    public function showFeedback(Product $product)
    {
        // If user is logged in
        if(!auth() -> guest())
            $this->authorize('view', $product);
        elseif($product -> active == false)
            abort(404);

        return view('product.feedback', [
            'product' => $product,
        ]);
    }

    public function showDelivery(PhysicalProduct $product)
    {
        // If user is logged in
        if(auth() -> check())
            $this->authorize('view', $product -> product);
        elseif($product -> product -> active == false)
            abort(404);

        return view('product.delivery', [
            'product' => $product -> product,
        ]);
    }

    public function showVendor(Product $product)
    {
        // If user is logged in
        if(!auth() -> guest())
            $this->authorize('view', $product);
        elseif($product -> active == false)
            abort(404);

        return view('product.vendor', [
            'product' => $product,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
