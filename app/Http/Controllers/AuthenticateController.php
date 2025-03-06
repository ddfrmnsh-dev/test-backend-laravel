<?php

namespace App\Http\Controllers;

use App\Jobs\SendVerificationReminderEmail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticateController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'success'   => false,
                    'message' => ['These credentials do not match our records.']
                ], 404);
            }

            if (!$user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Please verify your email before logging in.'], 403);
            }

            $token = $user->createToken('ApiToken')->plainTextToken;

            $meta = [
                'message'   => "Successfully login",
                'code'      => 200,
                'status'    => true
            ];

            return response()->json([
                'meta' => $meta,
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
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

    public function logout()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'No authenticated user'], 401);
        }

        $user->currentAccessToken()->delete();

        $meta = [
            'message'   => "Logged out successfully",
            'code'      => 200,
            'status'    => true
        ];

        return response()->json([
            'meta' => $meta,
            'data'    => null
        ], 200);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset password link sent to your email.'], 200)
            : response()->json(['message' => 'Failed to send reset password link.'], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password has been reset successfully.'], 200)
            : response()->json(['message' => 'Failed to reset password.'], 400);
    }

    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        event(new Registered($user));
        SendVerificationReminderEmail::dispatch($user)->delay(now()->addMinute(5));

        return response()->json(['message' => 'User registered successfully. Please verify your email.'], 201);
    }

    public function verifyUser($id, $hash)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
                return response()->json(['message' => 'Invalid verification hash'], 403);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email already verified'], 400);
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return response()->json(['message' => 'Email verified successfully']);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
