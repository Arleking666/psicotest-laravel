<?php

namespace App\Http\Controllers;

use App\Models\Conversion;
use App\Models\EscalaDimension;
use Illuminate\Http\Request;

class ConversionController extends Controller
{
    public function massUpdate(Request $request)
    {
        $valores = $request->valores;
        foreach($valores as $valor) {
            //BUSCAR SI EXISTE
            $conversion = Conversion::where('id_escala_dimension', $valor['id_escala_dimension'])
                                    ->where('natural', $valor['natural'])
                                    ->first();
            if($conversion) {
                if($valor['convertido']) {
                    //MODIFICAR SI EXISTE Y HAY UN CONVERTIDO
                    $conversion->convertido = $valor['convertido'];
                    $conversion->save();
                } else {
                    //ELIMINAR SI EXISTE Y NO HAY VALOR CONVERTIDO
                    Conversion::destroy($conversion->id);
                }
            } else if($valor['convertido'] || $valor['convertido'] == "0") {
                //CREAR SI NO EXISTE Y HAY VALOR CONVERTIDO
                $conversion = new Conversion();
                $conversion->id_escala_dimension = $valor['id_escala_dimension'];
                $conversion->natural = $valor['natural'];
                $conversion->convertido = $valor['convertido'];
                $conversion->save();
            }
        }
        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }
}
