<?php

namespace App\Http\Controllers\v1;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) //GET
    {
        //Team member only have visibility to their projects.
        if($request->user()->tokenCan('role:team_member')){
            $tasks = Task::join("projects","projects.id","=","tasks.project_id")
                ->whereRaw("json_contains(members,'".$request->user()->id."')")
                ->get();
        }else{
            $tasks = Task::all();
        }
        return response()->json([
            "success" => true,
            "data" => $tasks
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
        if(!$request->user()->tokenCan('role:product_owner')){
            throw new AccessDeniedHttpException($request);
        }

        $validate = Validator::make($request->all(),
            [
                'title' => 'required',
                'project_id' => 'required',
                'user_id' => 'required',
            ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'msg' => 'Validation error: '.$validate->errors(),
            ], 401);
        }

        $project = Project::find($request->project_id);
        if(empty($project)){
            return response()->json([
                'success' => false,
                'msg' => 'Project not found',
            ], 401);
        }
        $member = json_decode($project->members,true);
        $member = !empty($member)?$member:[];
        if(!in_array($request->user_id,$member)){
            return response()->json([
                'success' => false,
                'msg' => 'User assigned not part of project members',
            ], 401);
        }
        
        $tasks = Task::create([
            'title' => $request->title,
            'project_id' => $request->project_id,
            'user_id' => $request->user_id,
            'status'=>Task::NOT_STARTED
        ]);
        return response()->json([
            "success" => true,
            'msg' => 'Task Created Successfully',
            "data" => $tasks
        ], 200);  
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) //GET BY ID
    {
        //Team member only have visibility to their projects.
        if($request->user()->tokenCan('role:team_member')){
            $task = Task::join("projects","projects.id","=","tasks.project_id")
                ->whereRaw("json_contains(members,'".$request->user()->id."')")
                ->where('tasks.id','=',$id)
                ->get();
        }else{
            $task = Task::find($id);
        }
        if(empty($task)){
            $task = [];
        }
        return response()->json([
            "success" => true,
            "data" =>$task
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) //PUT OR PATCH
    {
        if($request->user()->tokenCan('role:product_owner')){
            $validate = Validator::make($request->all(),
            [
                'title' => 'required',
                'project_id' => 'required',
                'user_id' => 'required',
                'status' => ['required',Rule::in([Task::NOT_STARTED,Task::IN_PROGRESS,Task::READY_FOR_TEST,Task::COMPLETED])]
            ]);

            if($validate->fails()){
                return response()->json([
                    'success' => false,
                    'msg' => 'Validation error: '.$validate->errors(),
                ], 401);
            }
            $update = [
                'title' => $request->title,
                'status' => $request->status,
                'project_id' => $request->project_id,
                'user_id' => $request->user_id,
                'status' => $request->status,
            ];
        }else{
            //check if is user task
            $validate = Validator::make($request->all(),
            [
                'status' => 'required'
            ]);
            if($validate->fails()){
                return response()->json([
                    'success' => false,
                    'msg' => 'Validation error: '.$validate->errors(),
                ], 401);
            }
            $update = [
                'status' => $request->status,
            ];
        }
        
        $task = Task::find($id);
        if(empty($task)){
            return response()->json([
                "success" => false,
                "msg" => "Task not found"
            ], 401);
        }
        if($request->user()->tokenCan('role:team_member')){
            if($task->user_id != $request->user()->id){
                return response()->json([
                    "success" => false,
                    "msg" => "User does not have permission to update task"
                ], 401);
            }
        }
        $task->update($update);
 
        return response()->json([
            "success" => true,
            "data" => $task,
            "msg" => "Task updated successfully"
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id) //DELETE
    {
        if(!$request->user()->tokenCan('role:product_owner')){
            throw new AccessDeniedHttpException($request);
        }
        $ok = Task::where('id', $id)->delete();
        if($ok){
            return response()->json([
                "success" => true,
                "msg" => "Task deleted successfully"
            ], 200);
        }else{
            return response()->json([
                'success' => false,
                'msg'=>"Unable to delete"
            ], 401);
        }
    }
}
