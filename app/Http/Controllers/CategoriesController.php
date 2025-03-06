<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    public function getCategoryById($id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'meta' => [
                    'message'   => "Params must be a number",
                    'code'      => 400,
                    'status'    => false
                ],
                'error' => "Failed get category"
            ], 400);
        }

        try {
            $category = Categories::find($id);

            if (!$category) {
                return response()->json([
                    'meta' => [
                        'message'   => "Category not found",
                        'code'      => 404,
                        'status'    => false
                    ],
                    'error' => "Category not found"
                ], 404);
            }

            $meta = [
                'message'   => "Successfully get category",
                'code'      => 200,
                'status'    => true
            ];
            return response()->json([
                'meta' => $meta,
                'data' => [
                    'category' => $category,
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'meta' => [
                    'message'   => "Something went wrong",
                    'code'      => 500,
                    'status'    => false
                ],
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getAllCategory()
    {
        try {
            $categories = Cache::remember('getCategories', now()->addMinutes(5), function () {
                return Categories::all();
            });


            if ($categories->isEmpty()) {
                return response()->json([
                    'meta' => [
                        'message'   => "Categories not found",
                        'code'      => 404,
                        'status'    => false
                    ],
                    'error' => "Categories not found"
                ], 404);
            }

            $meta = [
                'message'   => "Successfully get all categories",
                'code'      => 200,
                'status'    => true
            ];
            return response()->json([
                'meta' => $meta,
                'data' => [
                    'categories' => $categories,
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'meta' => [
                    'message'   => "Something went wrong",
                    'code'      => 500,
                    'status'    => false
                ],
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function createCategory(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:255',
            ]);

            $data = Categories::create([
                'name' => $request->name,
                'description' => $request->description,
                'slug' => Str::slug($request->name),
            ]);

            Cache::forget('getCategories');

            $meta = [
                'message'   => "Successfully create category",
                'code'      => 200,
                'status'    => true
            ];
            return response()->json([
                'meta' => $meta,
                'data' => [
                    'category' => $data,
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'meta' => [
                    'message'   => "Validation failed",
                    'code'      => 422,
                    'status'    => false
                ],
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'meta' => [
                    'message'   => "Something went wrong",
                    'code'      => 500,
                    'status'    => false
                ],
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function updateCategory(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'meta' => [
                    'message'   => "Params must be a number",
                    'code'      => 400,
                    'status'    => false
                ],
                'error' => "Failed update category"
            ], 400);
        }
        try {
            $request->validate([
                'name' => 'string|max:255',
                'description' => 'string|max:255',
            ]);
            $category = Categories::find($id);
            if (!$category) {
                return response()->json([
                    'meta' => [
                        'message'   => "Category not found",
                        'code'      => 404,
                        'status'    => false
                    ],
                    'error' => "Category not found"
                ], 404);
            }
            $category->name = $request->name;
            $category->description = $request->description;
            $category->slug = Str::slug($request->name);
            $category->save();

            Cache::forget('getCategories');

            $meta = [
                'message'   => "Successfully update category",
                'code'      => 200,
                'status'    => true
            ];
            return response()->json([
                'meta' => $meta,
                'data' => [
                    'category' => $category,
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'meta' => [
                    'message'   => "Validation failed",
                    'code'      => 422,
                    'status'    => false
                ],
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'meta' => [
                    'message'   => "Something went wrong",
                    'code'      => 500,
                    'status'    => false
                ],
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function deleteCategory($id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'meta' => [
                    'message'   => "Params must be a number",
                    'code'      => 400,
                    'status'    => false
                ],
                'error' => "Failed delete category"
            ], 400);
        }
        try {
            $category = Categories::find($id);
            if (!$category) {
                return response()->json([
                    'meta' => [
                        'message'   => "Category not found",
                        'code'      => 404,
                        'status'    => false
                    ],
                    'error' => "Category not found"
                ], 404);
            }
            $category->delete();
            Cache::forget('getCategories');
            $meta = [
                'message'   => "Successfully delete category",
                'code'      => 200,
                'status'    => true
            ];

            return response()->json([
                'meta' => $meta,
                'data' => [
                    'category' => $category,
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'meta' => [
                    'message'   => "Something went wrong",
                    'code'      => 500,
                    'status'    => false
                ],
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
