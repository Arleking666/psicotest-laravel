<?php

namespace App\Http\Controllers;

use App\Models\Caracteristica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaracteristicaController extends Controller
{
    public function store(Request $request)
    {
        $request -> validate([
            'titulo' => 'required',
            'descripcion' => 'required',
            'id_test' => 'required'
        ]);

        $caracteristica = new Caracteristica();
        $caracteristica->titulo = $request->titulo;
        $caracteristica->descripcion = $request->descripcion;
        $caracteristica->id_test = $request->id_test;
        $caracteristica->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

   public function CaracteristicasByTest($id)
    {
        $caracteristicas = DB::select(
            "SELECT * FROM caracteristicas WHERE id_test='$id' ORDER BY id"
        );

        return $caracteristicas;
    }

    public function update(Request $request, $id)
    {
        $request -> validate([
            'titulo' => 'required',
            'descripcion' => 'required'
        ]);

        $caracteristica = Caracteristica::findOrFail($id);
        $caracteristica->titulo = $request->titulo;
        $caracteristica->descripcion = $request->descripcion;
        $caracteristica->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function destroy($id)
    {
        return Caracteristica::destroy($id);
    }
}
