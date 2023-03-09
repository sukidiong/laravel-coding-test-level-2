<?php

namespace App\Http\Controllers\v1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() //GET
    {
        $users = User::all();
        return response()->json([
            "success" => true,
            "data" => $users
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) //POST 
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

            if(User::where('username',$request->post('username'))->count() > 0){
                return response()->json([
                    'success'=>false,
                    'msg'=>'Username already taken'
                ], 401);
            }
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->role
            ]);

            $token = $user->createToken("API TOKEN", ['role:'.$request->role])->plainTextToken;
            $user->token = $token;

            return response()->json([
                'success' => true,
                'msg' => 'User Created Successfully',
                'data' => $user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'msg' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id) //GET BY ID
    {
        $user = User::find($id);
        if(empty($user)){
            $user = [];
        }
        return response()->json([
            "success" => true,
            "data" =>$user
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user) //PUT OR PATCH
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        
        $oldRole = $user->role;
        $user->update([
            'username'=>$request->username,
            'password'=>$request->password,
            'role'=>$request->role
        ]);
        if($oldRole <> $request->role){
            $token = $user->createToken("API TOKEN", ['role:'.$request->role])->plainTextToken;
            $user->token = $token;
        }

        return response()->json([
            "success" => true,
            "data" => $user,
            "msg" => "User updated successfully"
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) //DELETE
    {
        $ok = User::where('id', $id)->delete();
        if($ok){
            return response()->json([
                "success" => true,
                "msg" => "User deleted successfully"
            ], 200);
        }else{
            return response()->json([
                'success' => false,
                'msg'=>"Unable to delete"
            ], 401);
        }
    }
}
