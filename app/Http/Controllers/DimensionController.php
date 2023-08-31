<?php

namespace App\Http\Controllers;

use App\Models\Dimension;
use App\Models\EscalaDimension;
use App\Traits\PuntuacionesNaturales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DimensionController extends Controller
{
    use PuntuacionesNaturales;

    public function store(Request $request)
    {
        $request->validate([
            "id_test" => "required",
            "descripcion" => "required"
        ]);

        $dimension = new Dimension();
        $dimension->id_test = $request->id_test;
        $dimension->descripcion = $request->descripcion;
        $dimension->constante = 0;
        $dimension->save();
        $dimension->preguntas = [];
        $dimension->valores = [];

        $escalas = DB::select("SELECT id FROM escalas WHERE id_test='$request->id_test'");
        foreach($escalas as $escala) {
            $escala_dimension = new EscalaDimension();
            $escala_dimension->id_escala = $escala->id;
            $escala_dimension->id_dimension = $dimension->id;
            $escala_dimension->save();
        }

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $dimension], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "descripcion" => "required"
        ]);

        $dimension = Dimension::findOrFail($id);
        $dimension->descripcion = $request->descripcion;
        $dimension->save();

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $dimension], 201);
    }

    public function destroy($id)
    {
        return Dimension::destroy($id);
    }

    public function changeConstante(Request $request, $id)
    {
        $request->validate([
            "constante" => "required"
        ]);

        $dimension = Dimension::findOrFail($id);
        $dimension->constante = $request->constante;
        $dimension->save();

        $naturales = $this->getPuntuacionNatural($id);

        return response()->json(["mensaje" => "se guardo correctamente", "data" => array("constante" => $dimension->constante, "valores" => $naturales)], 201);
    }
}
