<?php

namespace App\Http\Controllers\v1;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() //GET
    {
        $projects = Project::all();
        return [
            "success" => true,
            "data" => $projects
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
            'name' => 'required',
        ]);
        if(Project::where('name',$request->post('name'))->count() > 0){
            return [
                'success'=>false,
                'msg'=>'Project name already taken'
            ];
        }
        $projects = Project::create($request->all());
        return [
            "success" => true,
            "data" => $projects
        ];  
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project) //GET BY ID
    {
        return [
            "success" => true,
            "data" =>$project
        ];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $proejct) //NOT IMPLEMENTED
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project) //PUT OR PATCH
    {
        $request->validate([
            'name' => 'required',
        ]);
 
        $project->update($request->all());
 
        return [
            "success" => true,
            "data" => $project,
            "msg" => "Project updated successfully"
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) //DELETE
    {
        $ok = Project::where('id', $id)->delete();
        if($ok){
            return [
                "success" => true,
                "msg" => "Project deleted successfully"
            ];
        }else{
            return [
                'success' => false,
                'msg'=>"Unable to delete"
            ];
        }
    }
}
