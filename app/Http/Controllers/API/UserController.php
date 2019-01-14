<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;

class UserController extends Controller 
{
    public function login(){ 
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){ 
            $user = Auth::user(); 
            $token =  $user->createToken('MyApp')-> accessToken;

            $res = $this->prepareUser($user, $token);
            return response()->json($res, 200); 
        } 
        else{ 
            return response()->json(['error'=>'Incorrect username and password!'], 401); 
        } 
    }

    public function register(Request $request) 
    { 
        $input = $request->all();
        $validator = Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email', 
            'password' => 'required', 
            'c_password' => 'required|same:password', 
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }

        if(User::where('email', '=', $input['email'])->first()){
            return response()->json(['error' => 'User with this email already exists!'], 401);
        }
        
         
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $token = $user->createToken('MyApp')-> accessToken; 
        $user = User::where('email', '=', $input['email'])->first();
        $res = $this->prepareUser($user, $token);
        return response()->json($res, 200); 
    }
    
    public function prepareUser($user, $token)
    { 
        $response = array();
        $response['id'] = $user->id;
        $response['name'] = $user->name;
        $response['email'] = $user->email;
        $response['token'] = $token;
        return $response;
    }
}
