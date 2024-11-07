<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                400,
                'Bad Request',
                $validator->errors(),
            );
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'name' => $request->name,
            ]);

            $token = $user->createToken('auth_token')->accessToken;
            $data = [
                'user' => $user,
                'token' => $token,
            ];

            // Event to send email verification
            event(new Registered($user));

            DB::commit();

            return $this->successResponse(
                201,
                'User registered successfully',
                $data
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                500,
                'Internal Server Error',
                $e->getMessage(),
            );
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                400,
                'Bad Request',
                $validator->errors(),
            );
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->errorResponse(
                    401,
                    'Invalid username or password'
                );
            }

            $token = $user->createToken('auth_token')->accessToken;
            $data = [
                'user' => $user,
                'token' => $token,
            ];

            if (!$user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();
            }

            return $this->successResponse(
                200,
                'User logged in successfully',
                $data
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                500,
                'Internal Server Error',
                $e->getMessage(),
            );
        }
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse(404, 'User Not Found');
            }

            if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
                return $this->errorResponse(
                    404,
                    'Invalid verification link'
                );

                // If you want to redirect to a frontend URL, you can use the following code and adjust the URL:
                // return redirect()->away(config('app.frontend_url') . '/verification/verification-error');
            }

            if ($user->hasVerifiedEmail()) {
                return $this->errorResponse(
                    400,
                    'Email already verified',
                );

                // If you want to redirect to a frontend URL, you can use the following code and adjust the URL:
                // return redirect()->away(config('app.frontend_url') . '/verification/already-verified');
            }

            $user->markEmailAsVerified();
            event(new Verified($user));

            return $this->successResponse(
                200,
                'Email verified successfully',
                null,
            );

            // If you want to redirect to a frontend URL, you can use the following code and adjust the URL:
            // return redirect()->away(config('app.frontend_url') . '/verification/success');
        } catch (\Exception $e) {
            return $this->errorResponse(
                500,
                'Internal Server Error',
                $e->getMessage(),
            );
        }
    }

    public function resendEmailVerification(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                return $this->errorResponse(
                    400,
                    'Email is already verified.'
                );
            }

            // Event to send email verification
            $user->sendEmailVerificationNotification();

            return $this->successResponse(
                200,
                'Verification email has been resent. Please check your inbox.',
                null,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                500,
                'An error occurred while resending the verification email.',
                $e->getMessage(),
            );
        }
    }

    public function sendResetPasswordLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                400,
                'Bad Request',
                $validator->errors(),
            );
        }

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return $this->successResponse(
                    200,
                    __($status),
                    null
                );
            } else {
                return $this->errorResponse(
                    400,
                    __($status)
                );
            }
        } catch (\Exception $e) {
            return $this->errorResponse(
                500,
                'Internal Server Error',
                $e->getMessage(),
            );
        }
    }

    public function resetPassword(Request $request)
    {
        $validatedData = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        try {
            $status = Password::reset(
                $validatedData,
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return $this->successResponse(
                    200,
                    __($status),
                    null
                );
            } else {
                return $this->errorResponse(
                    400,
                    __($status)
                );
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                422,
                'Validation Error',
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                500,
                'Internal Server Error',
                $e->getMessage()
            );
        }
    }
}
