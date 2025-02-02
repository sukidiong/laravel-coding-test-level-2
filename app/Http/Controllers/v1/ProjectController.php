<?php

namespace App\Http\Controllers\v1;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
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
            $projects = Project::whereRaw("json_contains(members,'".$request->user()->id."')")->get();
        }else{
            $projects = Project::all();
        }

        // Pagination
        // q -> search keyword, will search for name
        // pageIndex -> the index of the page to shown, default 0
        // pageSize -> how many items to return, default 3
        // sortBy -> attribute to sort, default name
        // sortDirection -> direction of the sort, default ASC
        $offset = !empty($request->get('pageIndex'))?$request->get('pageIndex'):0;
        $limit = !empty($request->get('pageSize'))?$request->get('pageSize'):3;
        if(!empty($request->get('q'))){
            $search = $request->get('q');
            $projects = $projects->filter(function ($value, $key) use ($search) {
                return false !== stripos($value->name, $search);
            });
        }
        
        $sortBy = !empty($request->get('sortBy'))?$request->get('sortBy'):'name';
        $sortDirection = !empty($request->get('sortDirection'))?strtolower($request->get('sortDirection')):'asc';
        $projects = $projects->sortBy([[$sortBy, $sortDirection]]);
        $page = $projects->forPage($offset+1,$limit)->values();

        return response()->json([
            "success" => true,
            "total"=> $projects->count(),
            "per_page"=> $limit,
            "current_page"=> $offset+1,
            "data" => $page
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
            'name' => 'required',
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'msg' => 'Validation error: '.$validate->errors(),
            ], 401);
        }

        if(Project::where('name',$request->post('name'))->count() > 0){
            return response()->json([
                'success'=>false,
                'msg'=>'Project name already taken'
            ], 401);
        }
        
        $projects = Project::create([
            'name'=>$request->name,
            'members'=>!empty($request->members)?json_encode($request->members):null
        ]);

        return response()->json([
            "success" => true,
            'msg' => 'Project Created Successfully',
            "data" => $projects
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) //GET BY ID
    {
        $project = Project::find($id);
        if(empty($project)){
            $project = [];
        }
        // Team member only have visibility to their projects.
        if($request->user()->tokenCan('role:team_member')){
            $members = json_decode($project->members,true);
            if(!in_array($request->user()->id,$members)){
                return response()->json([
                    "success" => false,
                    "msg" => "User does not have permission to view this project"
                ], 401);
            }
        }else{
            $projects = Project::all();
        }
        //Team member only have visibility to their projects.
        return response()->json([
            "success" => true,
            "data" =>$project
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) //PUT OR PATCH
    {
        //Only PRODUCT_OWNER role can create a project and tasks
        if(!$request->user()->tokenCan('role:product_owner')){
            throw new AccessDeniedHttpException($request);
        }
        $validate = Validator::make($request->all(), 
        [
            'name' => 'required',
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'msg' => 'Validation error: '.$validate->errors(),
            ], 401);
        }
        
        $project = Project::find($id);
        if(empty($project)){
            return response()->json([
                "success" => false,
                "msg" => "Project not found"
            ], 401);
        }
        $project->update([
            'name'=>$request->name,
            'members'=>!empty($request->members)?$request->members:null
        ]);
 
        return response()->json([
            "success" => true,
            "data" => $project,
            "msg" => "Project updated successfully"
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id) //DELETE
    {
        if(!$request->user()->tokenCan('role:product_owner')){
            throw new AccessDeniedHttpException($request);
        }
        $ok = Project::where('id', $id)->delete();
        if($ok){
            return response()->json([
                "success" => true,
                "msg" => "Project deleted successfully"
            ], 200);
        }else{
            return response()->json([
                'success' => false,
                'msg'=>"Unable to delete"
            ], 401);
        }
    }
}
