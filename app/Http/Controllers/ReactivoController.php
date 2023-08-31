<?php

namespace App\Http\Controllers;

use App\Models\Puntuacion;
use App\Models\Reactivo;
use App\Traits\PuntuacionesNaturales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReactivoController extends Controller
{
    use PuntuacionesNaturales;

    public function index()
    {
        return Reactivo::all();
    }

    public function reactivosBySeccion($idSeccion)
    {
        return DB::select("SELECT * FROM reactivos WHERE id_seccion='$idSeccion' ORDER BY id");
    }

    public function store(Request $request)
    {
        $request->validate([
            "id_seccion" => "required",
            "descripcion" => "required"
        ]);

        $reactivo = new Reactivo();
        $reactivo->id_seccion = $request->id_seccion;
        $reactivo->descripcion = $request->descripcion;
        $reactivo->predeterminado = 0;
        $reactivo->save();

        //AÑADIR PUNTUACIONES DEL REACTIVO DEPENDIENDO DE CUANTAS PREGUNTAS HAYA
        $id_reactivo = $reactivo->id;
        $id_seccion = $request->id_seccion;
        $preguntas = DB::select("SELECT * FROM preguntas WHERE id_seccion='$id_seccion' ORDER BY id");

        $puntuaciones = [];
        foreach($preguntas as $pregunta) {
            $puntuacion = new Puntuacion();
            $puntuacion->id_pregunta = $pregunta->id;
            $puntuacion->id_reactivo = $id_reactivo;
            $puntuacion->asignado = 0;
            $puntuacion->save();
            $puntuaciones[] = $puntuacion;
        }

        $dimensiones = DB::select(
            "SELECT d.id
            FROM reactivos as r, puntuacions as pt, preguntas as pr, pregunta_dimensions as pd, dimensions as d
            WHERE r.id='$id_reactivo' AND pt.id_reactivo=r.id AND pt.id_pregunta=pr.id AND pd.id_pregunta=pr.id AND pd.id_dimension=d.id"
        );
        $naturales = $this->getPuntuacionesNaturales($dimensiones);

        $data = array(
            "reactivo" => $reactivo,
            "puntuaciones" => $puntuaciones,
            "valores" => $naturales
        );

        //RETORNAR
        return response()->json(["mensaje" => "se guardo correctamente", "data" => $data], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "id_seccion" => "required",
            "descripcion" => "required"
        ]);

        $reactivo = Reactivo::findOrFail($id);
        $reactivo->id_seccion = $request->id_seccion;
        $reactivo->descripcion = $request->descripcion;
        $reactivo->save();

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $reactivo], 201);
    }

    public function destroy($id)
    {
        $dimensiones = DB::select(
            "SELECT d.id
            FROM reactivos as r, puntuacions as pt, preguntas as pr, pregunta_dimensions as pd, dimensions as d
            WHERE r.id='$id' AND pt.id_reactivo=r.id AND pt.id_pregunta=pr.id AND pd.id_pregunta=pr.id AND pd.id_dimension=d.id"
        );
        Reactivo::destroy($id);
        $naturales = $this->getPuntuacionesNaturales($dimensiones);
        return response()->json(["mensaje" => "se guardo correctamente", "data" => $naturales], 201);
    }

    public function changePredeterminado(Request $request, $id)
    {
        $request->validate([
            "predeterminado" => "required",
        ]);

        DB::update("UPDATE reactivos SET predeterminado='$request->predeterminado' WHERE id='$id'");
        DB::select("UPDATE puntuacions SET asignado='$request->predeterminado' WHERE id_reactivo='$id'");

        $dimensiones = DB::select(
            "SELECT d.id
            FROM reactivos as r, puntuacions as pt, preguntas as pr, pregunta_dimensions as pd, dimensions as d
            WHERE r.id='$id' AND pt.id_reactivo=r.id AND pt.id_pregunta=pr.id AND pd.id_pregunta=pr.id AND pd.id_dimension=d.id"
        );
        $naturales = $this->getPuntuacionesNaturales($dimensiones);

        $data = array(
            "predeterminado" => $request->predeterminado,
            "valores" => $naturales
        );

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $data], 201);
    }
}
