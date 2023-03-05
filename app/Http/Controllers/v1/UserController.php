<?php

namespace App\Http\Controllers\v1;

use App\Models\User;
use Illuminate\Http\Request;

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
        return [
            "success" => true,
            "data" => $users
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() //NOT USED
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) //POST 
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
       if(User::where('username',$request->post('username'))->count() > 0){
            return [
                'success'=>false,
                'msg'=>'Username already taken'
            ];
        }
        $users = User::create($request->all());
        return [
            "success" => true,
            "data" => $users
        ];  
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user) //GET BY ID
    {
        return [
            "success" => true,
            "data" =>$user
        ];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user) //NOT IMPLEMENTED
    {
        //
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
        $user->update($request->all());
        
        return [
            "success" => true,
            "data" => $user,
            "msg" => "User updated successfully"
        ];
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
            return [
                "success" => true,
                "msg" => "User deleted successfully"
            ];
        }else{
            return [
                'success' => false,
                'msg'=>"Unable to delete"
            ];
        }
    }
}
