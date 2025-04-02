<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:1',
            ]);

            if ($validator->fails()) {
                return responseMessage(
                    false,
                    401,
                    $validator->errors()
                );

            }

            $user = new User;
            $user->full_name = $request["full_name"];
            $user->email = $request["email"];
            $user->password = bcrypt($request["password"]);
            $user->save();

            $token = $user->createToken('token')->accessToken;
            $user->token = $token;

            return responseMessage(
                true,
                201,
                "User signed up successfully",
                $user
            );
        } catch (\Throwable $e) {

            return responseMessage(
                false,
                401,
                $e->getMessage()
            );
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return responseMessage(
                    false,
                    401,
                    $validator->errors()
                );
            }

            $user = User::where('email', $request["email"])->first();

            if (!$user || !Hash::check($request["password"], $user->password)) {
                return responseMessage(
                    false,
                    401,
                    "Invalid credentials"
                );
            }

            $token = $user->createToken('token')->accessToken;
            $user->token = $token;

            return responseMessage(
                true,
                200,
                'Login Successful',
                $user
            );
        } catch (\Throwable $e) {
            return responseMessage(
                false,
                401,
                $e->getMessage()
            );
        }
    }
}
