<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{

    public function getBookmarkByUser()
    {
        $user = Auth::guard('api')->user();

        try {
            $bookmark = Bookmark::with(['posts.media', 'posts.categories'])->where('user_id', $user->id)->get();
            if ($bookmark->isEmpty()) {
                return response()->json([
                    'meta' => [
                        'message'   => "Bookmark not found",
                        'code'      => 404,
                        'status'    => false
                    ],
                    'error' => "Bookmark not found"
                ], 404);
            }

            $meta = [
                'message'   => "Successfully get bookmark",
                'code'      => 200,
                'status'    => true
            ];

            return response()->json([
                'meta' => $meta,
                'data' => [
                    'bookmark' => $bookmark,
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

    public function addBookmark(Request $request)
    {
        $user = Auth::guard('api')->user();
        $post_id = $request->idPost;


        if (!is_numeric($post_id)) {
            return response()->json([
                'meta' => [
                    'message'   => "Params must be a number",
                    'code'      => 400,
                    'status'    => false
                ],
                'error' => "Failed add bookmark"
            ], 400);
        }

        $bookmark = Bookmark::where('user_id', $user->id)->where('post_id', $post_id)->first();
        if ($bookmark) {
            return response()->json([
                'meta' => [
                    'message'   => "Bookmark already exists",
                    'code'      => 400,
                    'status'    => false
                ],
                'error' => "Failed add bookmark"
            ], 400);
        }

        try {
            $bookmark = Bookmark::create([
                'user_id' => $user->id,
                'post_id' => $post_id
            ]);
            $meta = [
                'message'   => "Successfully add bookmark",
                'code'      => 200,
                'status'    => true
            ];
            return response()->json([
                'meta' => $meta,
                'data' => [
                    'bookmark' => $bookmark,
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

    public function deleteBookmark($postId)
    {
        $user = Auth::guard('api')->user();
        if (!is_numeric($postId)) {
            return response()->json([
                'meta' => [
                    'message'   => "Params must be a number",
                    'code'      => 400,
                    'status'    => false
                ],
                'error' => "Failed delete bookmark"
            ], 400);
        }
        $bookmark = Bookmark::where('user_id', $user->id)->where('post_id', $postId)->first();

        if (!$bookmark) {
            return response()->json([
                'meta' => [
                    'message'   => "Bookmark not found",
                    'code'      => 404,
                    'status'    => false
                ],
                'error' => "Bookmark not found"
            ], 404);
        }

        try {
            $bookmark->delete();
            $meta = [
                'message'   => "Successfully delete bookmark",
                'code'      => 200,
                'status'    => true
            ];
            return response()->json([
                'meta' => $meta,
                'data' => [
                    'bookmark' => $bookmark,
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
