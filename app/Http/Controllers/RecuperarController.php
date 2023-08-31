<?php

namespace App\Http\Controllers;

use App\Mail\RecuperarMailable;
use Illuminate\Support\Facades\Mail;
use App\Models\Recuperar;
use App\Models\User;
use Illuminate\Http\Request;

class RecuperarController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            "email" => "required"
        ]);

        $user = User::where('email', $request->email)->first();
        if(!$user) return response()->json(["error" => "No existe el usuario con este correo."], 201);
        if($user->id_rol === 3) return response()->json(["error" => "Algo salió mal."], 201);

        $codigo = rand(100000, 999999);

        $recover = new Recuperar();
        $recover->id_usuario = $user->id;
        $recover->codigo = $codigo;
        $recover->estado = 0;
        $recover->save();

        $correo = new RecuperarMailable($codigo, $user->nombre);
        Mail::to($user->email)->send($correo);

        return response()->json(["message" => "Correo enviado correctamente", "data" => $recover], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "codigo" => "required"
        ]);

        $recover = Recuperar::findOrFail($id);
        $minutosPasados = (strtotime($recover->created_at) - strtotime(gmdate("M d Y H:i:s"))) / -60;

        if($request->codigo != $recover->codigo) return response()->json(["error" => "El código es incorrecto."], 201);
        if($minutosPasados >= 10) return response()->json(["error" => "Este código ya expiró."], 201);
        if($recover->estado) return response()->json(["error" => "El código ya fué utilizado."], 201);

        $recover->estado = 1;
        $recover->save();
        return response()->json(["message" => "El código es correcto"], 201);
    }

    public function changeContrasena(Request $request, $id)
    {
        $request->validate([
            "password" => "required"
        ]);

        $recover = Recuperar::findOrFail($id);
        $user = User::findOrFail($recover->id_usuario);
        $user->password = bcrypt($request->password);
        $user->save();

        $data = array(
            "email" => $user->email,
            "password" => $request->password
        );

        return response()->json(["message" => "Se cambió la contraseña correctamente", "data" => $data], 201);
    }
}
