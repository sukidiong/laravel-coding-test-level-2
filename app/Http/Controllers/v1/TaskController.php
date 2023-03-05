<?php

namespace App\Http\Controllers\v1;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() //GET
    {
        $tasks = Task::all();
        return [
            "success" => true,
            "data" => $tasks
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
            'title' => 'required',
            'status' => 'required',
            'project_id' => 'required',
            'user_id' => 'required',
        ]);
 
        $tasks = Task::create($request->all());
        return [
            "success" => true,
            "data" => $tasks
        ];  
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task) //GET BY ID
    {
        return [
            "success" => true,
            "data" =>$task
        ];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task) //NOT IMPLEMENTED
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task) //PUT OR PATCH
    {
        $request->validate([
            'title' => 'required',
            'status' => 'required',
            'project_id' => 'required',
            'user_id' => 'required',
        ]);
 
        $task->update($request->all());
 
        return [
            "success" => true,
            "data" => $task,
            "msg" => "Task updated successfully"
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) //DELETE
    {
        $ok = Task::where('id', $id)->delete();
        if($ok){
            return [
                "success" => true,
                "msg" => "Task deleted successfully"
            ];
        }else{
            return [
                'success' => false,
                'msg'=>"Unable to delete"
            ];
        }
    }
}
