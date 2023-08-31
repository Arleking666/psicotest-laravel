<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'googleLogin']]);
    }

    public function login()
    {
        $email = request('email');
        $autorizado = DB::select("SELECT estado FROM users WHERE email='$email'");

        if ($autorizado != []) {
            if (!$autorizado[0]->estado) {
                return response()->json(['error' => 'No se puede ingresar a esta cuenta'], 403);
            }
        }

        $credentials = request(['email', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Correo o contraseña incorrectos'], 401);
        }

        return response()->json(["token" => $token]);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /* public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    } */

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required',
            'email' => 'required',
            'password' => 'required',
            'genero' => 'required',
            'edad' => 'required',
            'id_sede' => 'required',
            'id_rol' => 'required',
            'estado' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
            $validator->validate(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json([
            'message' => '¡Registro correcto!',
            'user' => $user
        ], 209);
    }

    function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public function googleLogin(Request $request) 
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if ($user) {
            $token = JWTAuth::fromUser($user);
            return response()->json(["token" => $token, "newUser" => false]);
        }
        $newPassword = $this->randomPassword();
        $user = new User();
        $user->nombre = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($newPassword);
        $user->perfil = $request->perfil;
        $user->genero = "sin definir";
        $user->edad = 0;
        $user->id_sede = 5;
        $user->id_rol = 1;
        $user->estado = 1;
        $user->save();
        $credentials = ["email" => $user->email, "password" => $newPassword];
        $token = auth()->attempt($credentials);
        return response()->json(["token" => $token, "newUser" => true, "user" => $user]);
    }
}
