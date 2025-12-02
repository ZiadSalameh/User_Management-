<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Requests\loginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateRequest;
use App\Http\Resources\UserResource;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'role' => $validatedData['role'],
            'password' => Hash::make($validatedData['password'])
        ]);

        $token = JWTAuth::attempt([
            'email' => $user->email,
            'password' => $validatedData['password']
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(loginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $token = auth('api')->attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'message' => 'User login  successfully',
            'user' => new  UserResource(auth('api')->user()),
            'token' => $token
        ]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json([
            'message' => 'User logout successfully'
        ]);
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $currentUser = auth('api')->user();
            if (!$user) {
                return response()->json([
                    'message' => 'user not found '
                ], 404);
            }
            $validatedData = $request->validated();
            if ($currentUser->id != $id && $currentUser->role !== 'admin') {
                return response()->json([
                    'message' => 'Sorry, you are forbidden ,'
                ], 403);
            } else {
                $user->fill($validatedData)->save();
                return response()->json([
                    'message' => 'User updated successfully',
                    'user' => $user
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error updating user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json([
                'message' => 'User deleted successfully',
                'user' => new UserResource($user)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    public function getallusers()
    {
        $users = User::all();
        return response()->json([
            'users' => $users,
        ], 200);
    }

    public function getuser($id)
    {
        try {
            $user = User::findOrFail($id);
            $currentUserId = auth('api')->id();

            if ($currentUserId != $id) {
                return response()->json([
                    'message' => 'Sorry, you are forbidden ,'
                ], 403);
            }
            return response()->json([
                'user' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
