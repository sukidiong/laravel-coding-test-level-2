<?php

namespace App\Http\Controllers\v1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make($request->all(), 
            [
                'username' => 'required',
                'password' => 'required',
                'role'=>['required',Rule::in([User::ADMIN,User::PRODUCT_OWNER,User::TEAM_MEMBER])]
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'success' => false,
                    'msg' => 'Validation error: '.$validateUser->errors(),
                ], 401);
            }

            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->role
            ]);

            return response()->json([
                'success' => true,
                'msg' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN", ['role:'.$request->role])->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'msg' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'username' => 'required',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'success' => false,
                    'msg' => 'Validation error: '.$validateUser->errors(),
                ], 401);
            }
            if(!Auth::attempt($request->only(['username', 'password']))){
                return response()->json([
                    'success' => false,
                    'msg' => 'Username & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('username', $request->username)->first();

            return response()->json([
                'success' => true,
                'msg' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN", ['role:'.$user->role])->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'msg' => $th->getMessage()
            ], 500);
        }
    }
}