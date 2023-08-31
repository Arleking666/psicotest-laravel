<?php

namespace App\Http\Controllers;

use App\Models\Seccion;
use App\Traits\PuntuacionesNaturales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeccionController extends Controller
{
    use PuntuacionesNaturales;

    public function index()
    {
        return Seccion::all();
    }
    
    public function getFullSeccion($id)
    {
        $seccion = DB::select("SELECT * FROM seccions WHERE id='$id'")[0];
        $preguntas = DB::select("SELECT * FROM preguntas WHERE id_seccion='$id' ORDER BY id");
        $reactivos = DB::select("SELECT * FROM reactivos WHERE id_seccion='$id' ORDER BY id");
        $puntuaciones = DB::select(
            "SELECT DISTINCT on (pu.id) pu.id, pu.id_pregunta, pu.id_reactivo, pu.asignado 
            FROM puntuacions as pu, preguntas as pr, reactivos as r
            WHERE (pu.id_pregunta=pr.id AND pr.id_seccion='$id') OR (pu.id_reactivo=r.id AND r.id_seccion='$id')"
        );
        $seccion->preguntas = $preguntas;
        $seccion->reactivos = $reactivos;
        $seccion->puntuaciones = $puntuaciones;
        return $seccion;
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_test' => 'required',
        ]);

        $seccion = new Seccion();
        $seccion->instruccion = "";
        $seccion->id_test = $request->id_test;

        $ultimoOrden = DB::select("SELECT MAX(orden) FROM seccions WHERE id_test='$request->id_test'")[0]->max + 1;
        $seccion->orden = $ultimoOrden;
        $seccion->nombre = "Sección $ultimoOrden";

        $seccion->save();

        $seccion = $this->getFullSeccion($seccion->id);

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $seccion], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required'
        ]);

        $seccion = Seccion::findOrFail($id);
        $seccion->nombre = $request->nombre;
        if($request->instruccion) {
            $seccion->instruccion = $request->instruccion;
        } else {
            $seccion->instruccion = "";
        }
        $seccion->save();

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $seccion], 201);
    }

    public function destroy($id)
    {
        return Seccion::destroy($id);
    }

    public function seccionByTest($idTest) 
    {
        return DB::select("SELECT *
                           FROM seccions
                           WHERE id_test='$idTest'
                           ORDER BY id");
    }

    public function changeMultimarcado($id) 
    {
        $seccion = Seccion::findOrFail($id);
        $multimarcado = $seccion->multimarcado;
        $seccion->multimarcado = !$multimarcado;
        $seccion->save();

        $dimensiones = DB::select(
            "SELECT DISTINCT ON (d.id) d.id
            FROM preguntas as p, pregunta_dimensions as pd, dimensions as d 
            WHERE p.id_seccion='$id' AND pd.id_pregunta=p.id AND pd.id_dimension=d.id"
        );
        $naturales = $this->getPuntuacionesNaturales($dimensiones);

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $naturales], 201);
    }

    public function changeVacio($id) 
    {
        $seccion = Seccion::findOrFail($id);
        $seccion->vacio = !$seccion->vacio;
        $seccion->save();

        $dimensiones = DB::select(
            "SELECT DISTINCT ON (d.id) d.id
            FROM preguntas as p, pregunta_dimensions as pd, dimensions as d 
            WHERE p.id_seccion='$id' AND pd.id_pregunta=p.id AND pd.id_dimension=d.id"
        );
        $naturales = $this->getPuntuacionesNaturales($dimensiones);

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $naturales], 201);
    }

    public function changeOrden(Request $request)
    {
        $request->validate([
            'ordenes' => 'required'
        ]);
        foreach($request->ordenes as $id => $orden) {
            $seccion = Seccion::findOrFail($id);
            $seccion->orden = $orden;
            $seccion->save();
        }
        return response()->json(["mensaje" => "se guardó correctamente"], 201);
    }
} 
