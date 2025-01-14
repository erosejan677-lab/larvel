<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterUserRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Resources\Api\V1\User\UserResource;
use App\Models\User;
use App\Services\Api\V1\Auth\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    use ApiResponse;
    protected $authService;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    public function register(RegisterUserRequest $request) {
        $input = $request->validated();

        $user = $this->authService->registerUser($input);
        $resource = new UserResource($user);
        return $this->createdResponse($resource, __('responses.auth.success.register'));
    }

    public function login(LoginRequest $request) {
        $input = $request->validated();

        $attempt = $this->authService->loginUser($input);

        if ($attempt) {
            return $this->successResponse($attempt, __('responses.auth.success.login'));
        }

        return $this->errorResponse(message: __('responses.auth.failed.login'));
    }

    public function logout(Request $request) {
        $this->authService->logoutUser();
        return $this->successResponse(message: __('responses.auth.success.logout'));
    }

    public function forgotPassword(Request $request) {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? $this->successResponse(__($status))
            : $this->errorResponse(__($status));

    }
    public function setNewPassword(string $token) {
        return view('auth.password_reset', ['token' => $token, 'email' => request()->input('email')]);

    }

    public function reset(ResetPasswordRequest $request)
    {
       $input = $request->validated();

        $status = Password::reset(
            $input,
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->successResponse(__('responses.auth.success.password_reset'))
            : $this->errorResponse(__($status));
    }

    public function resetSuccess() {
        return view('auth.password_reset_success');
    }


}
