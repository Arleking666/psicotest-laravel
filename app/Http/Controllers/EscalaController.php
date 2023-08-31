<?php

namespace App\Http\Controllers;

use App\Models\Escala;
use App\Models\EscalaDimension;
use App\Traits\PuntuacionesNaturales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EscalaController extends Controller
{
    use PuntuacionesNaturales;

    public function store(Request $request)
    {
        $request->validate([
            "id_test" => "required",
            "descripcion" => "required"
        ]);

        $escala = new Escala();
        $escala->id_test = $request->id_test;
        $escala->descripcion = $request->descripcion;
        $escala->save();

        $dimensiones = DB::select("SELECT id FROM dimensions WHERE id_test='$request->id_test'");
        foreach($dimensiones as $dimension) {
            $escala_dimension = new EscalaDimension();
            $escala_dimension->id_escala = $escala->id;
            $escala_dimension->id_dimension = $dimension->id;
            $escala_dimension->save();
        }
        $naturales = $this->getPuntuacionesNaturales($dimensiones);

        $data = array(
            "escala" => $escala,
            "naturales" => $naturales
        );

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $data], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "descripcion" => "required"
        ]);

        $escala = Escala::findOrFail($id);
        $escala->descripcion = $request->descripcion;
        $escala->save();

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $escala], 201);
    }

    public function destroy($id)
    {
        return Escala::destroy($id);
    }
}
