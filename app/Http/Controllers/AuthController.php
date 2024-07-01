<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Interfaces\DiagnoseRepositoryInterface;
use App\Interfaces\PatientRepositoryInterface;
use Carbon\Carbon;
use Auth;
use Http;
use Storage;

class AuthController extends Controller
{

    function login(LoginRequest $request) {

        $credentials = $request->all();

        $auth = Auth::guard('web');

        if (!$auth->attempt($credentials)) {
            return response()->json(['error'=>'Email or password incorrect'], 401);
        }

        $token = $auth->user()->createToken('authToken');

        return baseResponse(
            'success',
            'success login!',
            [
                'user' => $auth->user(),
                'access_token' => $token->accessToken,
                'expired' => $token->token->expires_at->diffInSeconds(Carbon::now()),
            ],
            null,
            200
        );
    }

    function profile() {

        $user = Auth::user();
        $role = Auth::user()->getRoleNames()[0];

        return baseResponse(
            'success',
            'success get profile!',
            [
                'user' => collect($user->setAttribute('role',$role)),
                'profile' => $role !== 'admin' ? collect($user->$role->setAttribute('relations', $user->$role->pivot))->except(['pivot']) : null,
            ],
            null,
            200
        );
    }

    function logout() {

        $user = Auth::user();

        if (!$user){
            return baseResponse(
                'failed',
                'You do not have the required authorization!',
                null,
                null,
                401
            );
        }

        $user->token()->revoke();

        return baseResponse(
            'success',
            'success logout!',
            $user,
            null,
            200
        );
    }

}
