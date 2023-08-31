<?php

namespace App\Http\Controllers;

use App\Models\PreguntaDimension;
use App\Traits\PuntuacionesNaturales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreguntaDimensionController extends Controller
{
    use PuntuacionesNaturales;
    
    public function storeVarious($id, Request $request)
    {
        $preguntasExistentes = PreguntaDimension::where('id_dimension',$id)->pluck('id_pregunta')->toArray();

        foreach($request->preguntas as $pregunta) {
            if(!in_array($pregunta, $preguntasExistentes)) {
                //NO EXISTE EN LA BASE DE DATOS POR LO QUE SE GUARDA
                $pregunta_dimension = new PreguntaDimension();
                $pregunta_dimension->id_pregunta = $pregunta;
                $pregunta_dimension->id_dimension = $id;
                $pregunta_dimension->save();
            }

            //ELIMINAR DEL ARRAY DE EXISTENTES PARA VER LOS FALTANTES
            if (($key = array_search($pregunta, $preguntasExistentes)) !== false) {
                unset($preguntasExistentes[$key]);
            }
        }
        foreach($preguntasExistentes as $idPregunta) {
            DB::delete("DELETE FROM pregunta_dimensions WHERE id_pregunta='$idPregunta' AND id_dimension='$id'");
        }

        $preguntas = PreguntaDimension::where('id_dimension',$id)->pluck('id_pregunta')->toArray();
        $natural = $this->getPuntuacionNatural($id);

        $data = array(
            "preguntas" => $preguntas,
            "natural" => $natural
        );

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $data], 201);
    }
}
