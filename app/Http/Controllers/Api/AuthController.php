<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|string|email:rfc,dns|max:255|unique:users',
            'password' => 'required|string|min:6|max:255',
            'role' => 'required|in:admin,owner,user',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
            'role' => $request['role'],
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'User created successfully!',
            ],
            'data' => [
                'user' => $user,
                'access_token' => [
                    'token' => $token,
                    'type' => 'Bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 1440,
                ],
            ],
        ]);
    }

    public function login(Request $request)
    {
        // Validate the login request
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Attempt to authenticate the user and generate a token
        $credentials = $request->only('email', 'password');
        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            return response()->json(['errors' => ['message' => 'Invalid credentials']], 401);
        }

        // Return the response as JSON
        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Login successful',
            ],
            'data' => [
                'user' => auth()->user(),
                'access_token' => [
                    'token' => $token,
                    'type' => 'Bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 1440,
                ],
            ],
        ]);
    }

    public function logout()
    {
        $token = JWTAuth::getToken();

        $invalidate = JWTAuth::invalidate($token);

        if ($invalidate) {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Successfully logged out',
                ],
                'data' => [],
            ]);
        }

        return response()->json(['errors' => ['message' => 'Failed to log out']], 500);
    }

    public function update($id, Request $request)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|min:2|max:255',
            'email' => 'sometimes|required|string|email:rfc,dns|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:6|max:255',
            'phone_number' => 'sometimes|nullable|string|max:20',
            'gender' => 'sometimes|nullable|string|in:male,female,other',
            'birthdate' => 'sometimes|nullable|date',
            'address' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'photo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) $user->name = $request->input('name');
        if ($request->has('email')) $user->email = $request->input('email');
        if ($request->has('password')) $user->password = bcrypt($request->input('password'));
        if ($request->has('phone_number')) $user->phone_number = $request->input('phone_number');
        if ($request->has('gender')) $user->gender = $request->input('gender');
        if ($request->has('birthdate')) $user->birthdate = $request->input('birthdate');
        if ($request->has('address')) $user->address = $request->input('address');
        if ($request->has('description')) $user->description = $request->input('description');
        if ($request->has('photo')) {
            if ($user->has("photo")) {
                $existingPhoto = $user->photo;
                if($existingPhoto) {
                    $existingPhotoPath = public_path('storage/user-photo' . $existingPhoto);
                    if (File::exists($existingPhotoPath)) {
                        File::delete($existingPhotoPath);
                    }
                }
            }
            $filePathPhoto = $request->file("photo")->store('user-photo', 'public');
            $user->photo = $filePathPhoto;
        }

        $user->save();

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'User updated successfully!',
            ],
            'data' => [
                'user' => $user,
            ],
        ]);
    }
}
