<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function getUserById($id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'meta' => [
                    'message'   => "Params must be a number",
                    'code'      => 400,
                    'status'    => false
                ],
                'error' => "Failed get user"
            ], 400);
        }

        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'meta' => [
                        'message'   => "User not found",
                        'code'      => 404,
                        'status'    => false
                    ],
                    'error' => "User not found"
                ], 404);
            }
            $meta = [
                'message'   => "Successfully get user",
                'code'      => 200,
                'status'    => true
            ];
            return response()->json([
                'meta' => $meta,
                'data' => [
                    'user' => $user,
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

    public function getAllUser()
    {
        try {
            $user = Cache::remember('getUsers', now()->addMinutes(5), function () {
                return User::all();
            });

            $meta = [
                'message'   => "Successfully get all users",
                'code'      => 200,
                'status'    => true
            ];

            return response()->json([
                'meta' => $meta,
                'data' => [
                    'user' => $user,
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

    public function createUser(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:users|max:50',
                'email' => 'required|email:dns|unique:users',
                'password' => 'required|confirmed|min:8',
            ]);

            $data = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'email_verified_at' => null
            ]);

            Cache::forget('getUsers');

            $meta = [
                'message'   => "Successfully create user",
                'code'      => 201,
                'status'    => true
            ];

            return response()->json([
                'meta' => $meta,
                'data' => [
                    'user' => $data,
                ]
            ], 201);
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

    public function updateUser(Request $request, $id)
    {


        if (!is_numeric($id)) {
            return response()->json([
                'meta' => [
                    'message'   => "Params must be a number",
                    'code'      => 400,
                    'status'    => false
                ],
                'error' => "Failed update user"
            ], 400);
        }
        try {
            $request->validate([
                'name' => 'required|max:50',
                'email' => 'required|email:dns',
                'password' => 'confirmed|min:8',
            ]);

            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'meta' => [
                        'message'   => "User not found",
                        'code'      => 404,
                        'status'    => false
                    ],
                    'error' => "User not found"
                ], 404);
            }

            if ($request->password) {
                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = bcrypt($request->password);
                $user->save();
            } else {
                $user->name = $request->name;
                $user->email = $request->email;
                $user->save();
            }

            Cache::forget('getUsers');

            $meta = [
                'message'   => "Successfully update user",
                'code'      => 200,
                'status'    => true
            ];

            return response()->json([
                'meta' => $meta,
                'data' => [
                    'user' => $user,
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

    public function deleteUser($id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'meta' => [
                    'message'   => "Params must be a number",
                    'code'      => 400,
                    'status'    => false
                ],
                'error' => "Failed delete user"
            ], 400);
        }

        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'meta' => [
                        'message'   => "User not found",
                        'code'      => 404,
                        'status'    => false
                    ],
                    'error' => "User not found"
                ], 404);
            }

            $user->delete();

            $meta = [
                'message'   => "Successfully delete user",
                'code'      => 200,
                'status'    => true
            ];

            Cache::forget('getUsers');

            return response()->json([
                'meta' => $meta,
                'data' => [
                    'user' => $user,
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
