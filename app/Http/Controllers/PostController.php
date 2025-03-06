<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    public function getPostById($id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'meta' => [
                    'message'   => "Params must be a number",
                    'code'      => 400,
                    'status'    => false
                ],
                'error' => "Failed get post"
            ], 400);
        }

        try {
            $post = Post::with(['media', 'categories'])->findOrFail($id);
            if (!$post) {
                return response()->json([
                    'meta' => [
                        'message'   => "Post not found",
                        'code'      => 404,
                        'status'    => false
                    ],
                    'error' => "Post not found"
                ], 404);
            }

            $meta = [
                'message'   => "Successfully get post",
                'code'      => 200,
                'status'    => true
            ];

            return response()->json([
                'meta' => $meta,
                'data' => [
                    'post' => $post,
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

    public function getPostBySlug($slug)
    {
        if (!is_string($slug)) {
            return response()->json([
                'meta' => [
                    'message'   => "Params must be a string",
                    'code'      => 400,
                    'status'    => false
                ],
                'error' => "Failed get post"
            ], 400);
        }

        try {
            $post = Post::with(['media', 'categories'])->where('slug', $slug)->first();

            if (!$post) {
                return response()->json([
                    'meta' => [
                        'message'   => "Post not found",
                        'code'      => 404,
                        'status'    => false
                    ],
                    'error' => "Post not found"
                ], 404);
            }

            $meta = [
                'message'   => "Successfully get post",
                'code'      => 200,
                'status'    => true
            ];

            return response()->json([
                'meta' => $meta,
                'data' => [
                    'post' => $post,
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

    public function getAllPost()
    {
        try {
            $posts = Cache::remember("getPosts", now()->addMinutes(5), function () {
                return Post::with(['media', 'categories'])->get();
            });

            if ($posts->isEmpty()) {
                return response()->json([
                    'meta' => [
                        'message'   => "Post not found",
                        'code'      => 404,
                        'status'    => false
                    ],
                    'error' => "Post not found"
                ], 404);
            }

            $meta = [
                'message'   => "Successfully get post",
                'code'      => 200,
                'status'    => true
            ];

            return response()->json([
                'meta' => $meta,
                'data' => [
                    'posts' => $posts,
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

    public function createPost(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'seo_title' => 'required|string|max:255',
                'seo_desc' => 'required|string|max:255',
                'meta_keyword' => 'required|array|max:255',
                'status' => 'nullable|string|max:255',
                'category' => 'required|array',
            ]);

            // dd($request->all());
            $user = Auth::guard('api')->user();
            $newExcerpt = Str::limit($request->content, 100);
            $newMetaKeyword = implode(', ', $request->meta_keyword);
            $newStatus = $request->status ?? 'draft';

            $post = new Post();
            $post->author_id = $user->id;
            $post->title = $request->title;
            $post->slug = Str::slug($request->title);
            $post->excerpt = $newExcerpt;
            $post->content = $request->content;
            $post->seo_title = $request->seo_title;
            $post->seo_desc = $request->seo_desc;
            $post->meta_keywords = $newMetaKeyword;
            $post->status = $newStatus;

            $post->addMedia($request->file('image'))->toMediaCollection('images');

            $post->save();
            // if (!empty($request->category)) {
            //     $post->categories()->syncWithPivotValues($request->category, ['created_at' => now(), 'updated_at' => now()]);
            // }

            if (!empty($request->category)) {
                $categoryIds = [];

                foreach ($request->category as $categoryName) {
                    $category =  Categories::firstOrCreate(['name' => $categoryName, 'slug' => Str::slug($categoryName)]);
                    $categoryIds[] = $category->id;
                }
                $post->categories()->syncWithPivotValues($categoryIds, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            Cache::forget("getPosts");

            $meta = [
                'message'   => "Successfully create post",
                'code'      => 200,
                'status'    => true
            ];

            return response()->json([
                'meta' => $meta,
                'data' => [
                    'posts' => $post,
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

    public function updatePost(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'meta' => [
                    'message'   => "Params must be a number",
                    'code'      => 400,
                    'status'    => false
                ],
                'error' => "Failed get post"
            ], 400);
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'seo_title' => 'required|string|max:255',
                'seo_desc' => 'required|string|max:255',
                'meta_keyword' => 'required|array|max:255',
                'status' => 'nullable|string|max:255',
                'category' => 'required|array',
            ]);

            // dd($request->all());

            $user = Auth::guard('api')->user();
            $post = Post::find($id);
            $newExcerpt = Str::limit($request->content, 100);
            $newMetaKeyword = implode(', ', $request->meta_keyword);
            $newStatus = $request->status ?? 'draft';

            $post->author_id = $user->id;
            $post->title = $request->title;
            $post->slug = Str::slug($request->title);
            $post->excerpt = $newExcerpt;
            $post->content = $request->content;
            $post->seo_title = $request->seo_title;
            $post->seo_desc = $request->seo_desc;
            $post->meta_keywords = $newMetaKeyword;
            $post->status = $newStatus;
            if ($request->hasFile('image')) {
                $post->clearMediaCollection('images');
                $post->addMedia($request->file('image'))->toMediaCollection('images');
            }

            if ($request->status === 'published' && !$post->published_at) {
                $post->published_at = now()->toDateTimeString();
            } elseif ($request->status !== 'published') {
                $post->published_at = null;
            }

            $post->save();

            if (!empty($request->category)) {
                $categoryIds = [];

                foreach ($request->category as $categoryName) {
                    $category =  Categories::firstOrCreate(['name' => $categoryName, 'slug' => Str::slug($categoryName)]);
                    $categoryIds[] = $category->id;
                }
                $post->categories()->syncWithPivotValues($categoryIds, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            Cache::forget("getPosts");

            $meta = [
                'message'   => "Successfully update post",
                'code'      => 200,
                'status'    => true
            ];

            return response()->json([
                'meta' => $meta,
                'data' => [
                    'posts' => $post,
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

    public function deletePost($id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'meta' => [
                    'message'   => "Params must be a number",
                    'code'      => 400,
                    'status'    => false
                ],
                'error' => "Failed get post"
            ], 400);
        }

        try {
            $post = Post::find($id);

            $post->clearMediaCollection('images');
            $post->delete();

            Cache::forget("getPosts");

            $meta = [
                'message'   => "Successfully delete post",
                'code'      => 200,
                'status'    => true
            ];

            return response()->json([
                'meta' => $meta,
                'data' => [
                    'posts' => $post,
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
