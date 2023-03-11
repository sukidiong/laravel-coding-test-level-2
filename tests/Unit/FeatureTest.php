<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Laravel\Sanctum\Sanctum;
use Faker\Generator as Faker;

class FeatureTest extends TestCase
{
    public function testUserChangeTaskStatus()
    {
        //i. Creating a user by calling the API
        Sanctum::actingAs(
            User::factory()->create(),
            ['role:admin']
        );

        $create_users = ['product_owner','team_member','team_member'];
        
        foreach($create_users as $role){
            $faker = app(Faker::class);
            //Create Product Owner
            $post = [
                'username' => $faker->userName,
                'password' => "password",
                'role' => $role,
            ];
            
            $response = $this->json('POST', '/api/v1/users', $post);
            $response->assertStatus(200);
            $response->assertJson(['success' => true]);
            $response->assertJson(['msg' => "User Created Successfully"]);
            $users[] = $response->getData()->data->id;
        }
        
        $product_owner = array_shift($users);
        
        //ii. Creating a project and assign 2 users to it.
        Sanctum::actingAs(
            User::find($product_owner),
            ['role:product_owner']
        );
        
        $post = [
            'name' => $faker->word,
            'members' => $users,
        ];
        
        $response = $this->json('POST', '/api/v1/projects', $post);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJson(['msg' => "Project Created Successfully"]);
        $project_id = $response->getData()->data->id;

        foreach($users as $team_member){
            $post = [
                'title' => $faker->word,
                'project_id' => $project_id,
                'user_id' => $team_member
            ];
            
            $response = $this->json('POST', '/api/v1/tasks', $post);
            $response->assertStatus(200);
            $response->assertJson(['success' => true]);
            $response->assertJson(['msg' => "Task Created Successfully"]);
            $tasks[] = [
                'id'=>$response->getData()->data->id,
                'user_id'=>$response->getData()->data->user_id,
                'project_id'=>$response->getData()->data->project_id,
                'title'=>$response->getData()->data->title,
            ];
        }
        
        ///iii. User change the status of a task assigned to themselves
        foreach($tasks as $task){
            Sanctum::actingAs(
                User::find($task['user_id']),
                ['role:team_member']
            );
            
            $post = [
                'title' => $task['title'],
                'project_id' => $task['project_id'],
                'user_id' => $task['user_id'],
                'status' => Task::IN_PROGRESS,
            ];
            
            $response = $this->json('PUT', '/api/v1/tasks/'.$task['id'], $post);
            $response->assertStatus(200);
            $response->assertJson(['success' => true]);
            $response->assertJson(['msg' => "Task updated successfully"]);
        }
        
    }
}
