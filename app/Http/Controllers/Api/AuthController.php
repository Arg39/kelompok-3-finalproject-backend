<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

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
            return new UserResource(false, 'Validation errors', null, null, $validator->errors());
        }

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
            'role' => $request['role'],
        ]);

        $token = JWTAuth::fromUser($user);

        return new UserResource(true, 'User created successfully!', $user, [
            'token' => $token,
            'type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60, 
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
            return new UserResource(false, 'Validation errors', null, null, $validator->errors());
        }

        // Attempt to authenticate the user and generate a token
        $credentials = $request->only('email', 'password');
        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            return new UserResource(false, 'Invalid credentials', null, null, ['message' => 'Invalid credentials']);
        }

        // Return the response as JSON
        return new UserResource(true, 'Login successful', auth()->user(), [
            'token' => $token,
            'type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
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

        return new UserResource(false, 'Failed to log out', null, null, ['message' => 'Failed to log out']);
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
            return new UserResource(false, 'Validation errors', null, null, $validator->errors());
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
            if ($user->photo) {
                $existingPhotoPath = public_path('storage/user-photo/' . $user->photo);
                if (File::exists($existingPhotoPath)) {
                    File::delete($existingPhotoPath);
                }
            }
            $filePathPhoto = $request->file("photo")->store('user-photo', 'public');
            $user->photo = $filePathPhoto;
        }

        $user->save();

        return new UserResource(true, 'User updated successfully!', $user, null);
    }

    public function me(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try {
            $user = Auth::setToken($token)->user();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return new UserResource(true, 'User information', $user, null);
    }
}
