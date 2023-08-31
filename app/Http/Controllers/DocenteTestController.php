<?php

namespace App\Http\Controllers;

use App\Models\DocenteTest;
use App\Models\User;
use Illuminate\Http\Request;

class DocenteTestController extends Controller
{
    public function show($id)
    {
        return DocenteTest::find($id);
    }

    public function store(Request $request, $idTest)
    {
        $request->validate([
            "id_user" => "required"
        ]);

        $user = User::findOrFail($request->id_user);
        $exists = DocenteTest::where("id_docente", $request->id_user)->where("id_test", $idTest)->first();
        if($user->id_rol == 1 || $exists) return response()->json(["mensaje" => "No permitido"], 201);
        
        $professorTest = new DocenteTest();
        $professorTest->id_docente = $request->id_user;
        $professorTest->id_test = $idTest;
        $professorTest->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }
}
