<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Category;
use App\Models\User;
//use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;



class UserController extends Controller
{
    public function getChildrenCategories( $parent_id){
       $children= Category::findOrFail($parent_id);
        $childrenCategories=$children->children()->get();
        return response([
            'message'=>'Operation Copleted Successfully',
            'information'=>$childrenCategories
        ],200);
    }
    public function getParentCategory($children_id){
        $parent=Category::findOrFail($children_id);
        $parentCategory=$parent->parent()->get();
        return response([
            'message'=>'Operation Copleted Successfully',
            'information'=>$parentCategory
        ],200);
    }
  
    public function register(UserStoreRequest $request)
{
    $validatedData = $request->validated();

    $validatedData['password'] = Hash::make($validatedData['password']);

    $user = User::create($validatedData);

    return response()->json([
        'message' => 'Your mono_style account has been created successfully',
        'user' => $user
    ], 201);
}

 







    public function getUsers(){
       $users= User::all();
       $count= $users->count();
       return response([
        'message'=>'Operation Completed Successfully',
        'total_users'=>$count,
        'users'=>$users
       ],200);
    }
    public function getUser($user_id){
         $user=User::findOrFail($user_id);
         
         return response([
            'message'=>'Operation Completed Successfully',
            'user'=>$user
         ]);
    }
    public function update( UserUpdateRequest $request,$user_id){
     $user=User::findOrFail($user_id);
     $user_validate=$request->validated();
      $user->update($user_validate);
      return response([
            'message'=>'Operation Completed Successfully',
            'user'=>$user
      ],200);
    }
    public function delete($user_id){
      $user= User::findOrFail($user_id);
      $user->delete();
        return response()->noContent();
         
    }
   public function deleteall(){
    User::query()->delete();
     return response()->noContent();
   }
  public function login(Request $request)
{
    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $user = User::where('email', $request->email)->first();

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'token' => $token,
        'user' => $user
    ], 200);
}
public function logout( Request $request){
 $request->user()->currentAccessToken()->delete();
 return response()->json([
    'message'=>'User Loggedout Successfully'
 ],200);
}












}

