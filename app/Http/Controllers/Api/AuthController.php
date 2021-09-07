<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Curator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only(['email', 'password']))) {
            return regularResponse([], false, 'UNATHORIZED', Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }
        $user = auth()->user();
        return regularResponse($user->getLoginData());
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['username'] = User::generateUsername($data['email']);
        $user = User::create($data);
        Curator::create(['user_id' => $user->id]);
        return regularResponse($user->getLoginData(), true, null, Response::HTTP_CREATED);
    }

    public function destroy(Request $request)
    {
        $request->user()->token()->revoke();
        return regularResponse();
    }

    public function user()
    {
        $user = auth('api')->user();
        return regularResponse($user->getLoginData(false));
    }

    public function update(UserUpdateRequest $request)
    {
        $user = auth('api')->user();
        $data = $request->only(['first_name', 'last_name', 'username', 'email', 'password', 'paypal_email']);
        if ($data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return regularResponse($user->getLoginData(false));
    }

    public function usernameAvailable(Request $request)
    {
        $user = auth()->user();
        return regularResponse(['available' => User::isUsernameAvailable($request->username, $user)]);
    }
}
