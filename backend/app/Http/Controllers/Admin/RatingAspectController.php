<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RatingAspect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingAspectController extends Controller
{
    public function index()
    {
        $aspects = RatingAspect::with('category')->get();
        return response()->json($aspects);
    }

    public function getByCategory($category_id)
    {
        $aspects = RatingAspect::where('category_id', $category_id)
            ->with('category')
            ->get();
        return response()->json($aspects);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:business_categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $aspect = RatingAspect::create($request->all());
        return response()->json($aspect, 201);
    }

    public function show($id)
    {
        $aspect = RatingAspect::with('category')->findOrFail($id);
        return response()->json($aspect);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:business_categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $aspect = RatingAspect::findOrFail($id);
        $aspect->update($request->all());
        return response()->json($aspect);
    }

    public function destroy($id)
    {
        $aspect = RatingAspect::findOrFail($id);
        $aspect->delete();
        return response()->json(null, 204);
    }
} 