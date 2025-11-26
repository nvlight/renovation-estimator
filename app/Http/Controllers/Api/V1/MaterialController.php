<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Models\Material;

class MaterialController extends Controller
{
    public function index()
    {
        //
    }

    public function store(StoreMaterialRequest $request)
    {
        //
    }

    public function show(Material $material)
    {
        //
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        //
    }

    public function destroy(Material $material)
    {
        //
    }
}
