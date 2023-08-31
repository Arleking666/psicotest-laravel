<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait PuntuacionesNaturales {
    public function getPuntuacionesNaturales($dimensiones) {
        $naturales = [];
        foreach($dimensiones as $dimension) {
            $natural = $this->getPuntuacionNatural($dimension->id);
            $naturales[] = array(
                "id" => $dimension->id,
                "valores" => $natural
            );
        }
        return $naturales;
    }

    public function getPuntuacionNatural($id) {
        $select = DB::select(
            "SELECT p.id, s.vacio, s.multimarcado, pu.asignado, d.constante
            FROM seccions as s, preguntas as p, pregunta_dimensions as pd, dimensions as d, puntuacions as pu
            WHERE p.id_seccion=s.id AND pu.id_pregunta=p.id
            AND pd.id_pregunta=p.id AND pd.id_dimension=d.id AND d.id=$id"
        );
        $preguntas = array_unique(array_column($select, 'id'));
        $naturales = [];
        if(count($preguntas)) {
            $constante = $select[0]->constante;
            $arrayDeArrays = [];
            foreach($preguntas as $pregunta) {
                $new = $this->array_filter_column($select, 'id', $pregunta);
                $array = array_column($new, 'asignado');
                $options = $new[0];
                $vacio = $options->vacio;
                $multimarcado = $options->multimarcado;
                if($multimarcado) {
                    $array = $this->possibleCombinations($array);
                }
                if($vacio && !in_array(0, $array)) {
                    $array[] = 0;
                }
                $arrayDeArrays[] = $array;
            }
            $posibilidades = $this->calcularPosibilidad($arrayDeArrays, 0);
            sort($posibilidades);
            foreach($posibilidades as $posibilidad) {
                $naturales[] = $posibilidad + $constante;
            }
        }

        //CODIGO PARA OBTENER LAS CONVERSIONES
        $conversiones = DB::select(
            "SELECT c.id_escala_dimension, c.convertido, c.natural, e.id as id_escala
            FROM conversions as c, dimensions as d, escalas as e, escala_dimensions as ed
            WHERE ed.id_dimension=d.id AND ed.id_escala=e.id 
            AND c.id_escala_dimension=ed.id 
            AND d.id='$id'"
        );
        $ids = DB::select("SELECT id as id_escala_dimension, id_escala FROM escala_dimensions WHERE id_dimension='$id'");
        $idsEscalaDimension = array_unique(array_column($ids, 'id_escala_dimension'));
        $idsEscala = array_unique(array_column($ids, 'id_escala'));
        $newNaturales = [];
        foreach($naturales as $natural) {
            $conversionesPorNatural = [];
            foreach($idsEscalaDimension as $i => $escalaDimension) {
                $flagEncontrado = false;
                foreach($conversiones as $conversion) {
                    if($conversion->natural == $natural && $conversion->id_escala_dimension == $escalaDimension) {
                        $conversionesPorNatural[] = $conversion;
                        $flagEncontrado = true;
                    }
                }
                if(!$flagEncontrado) {
                    $conversionesPorNatural[] = array(
                        "id_escala_dimension" => $escalaDimension,
                        "id_escala" => $idsEscala[$i],
                        "convertido" => "",
                        "natural" => $natural
                    );
                }
            }
            $newNaturales[] = array(
                "natural" => $natural,
                "conversiones" => $conversionesPorNatural
            );
        }

        return $newNaturales;
    }

    public function calcularPosibilidad($arrayDeArrays, $index) {
        if(count($arrayDeArrays) - 1 === $index) {
            return array_unique($arrayDeArrays[$index]);
        } else {
            $primerArray = $arrayDeArrays[$index];
            $segundoArray = $this->calcularPosibilidad($arrayDeArrays, $index + 1);
            $posibilidadesNuevas = $this->sumarArrays($primerArray, $segundoArray);
            return $posibilidadesNuevas;
        }
    }

    public function sumarArrays($primerArray, $segundoArray) {
        $arrayNuevo = [];
        foreach($primerArray as $primero) {
            foreach($segundoArray as $segundo) {
                $posibilidad = $primero + $segundo;
                if(!in_array($posibilidad, $arrayNuevo)) {
                    $arrayNuevo[] = $posibilidad;
                }
            }
        }
        return $arrayNuevo;
    }

    public function possibleCombinations($array) {
        $combinations = [];
        $iterations = pow(2, count($array));
        for($i = 1; $i < $iterations; $i++) {
            $bin = sprintf("%0".count($array)."d", decbin($i));
            $combination = 0;
            for($j = 0; $j < strlen($bin); $j++) {
                if(intval($bin[$j])) {
                    $combination += $array[$j];
                }
            }
            if(!in_array($combination, $combinations)) {
                $combinations[] = $combination;
            }
        }
        return $combinations;
    }

    public function array_filter_column($array, $column, $value) {
        $newArray = [];
        foreach($array as $val) {
            if($val->$column == $value) {
                $newArray[] = $val;
            }
        }
        return $newArray;
    }
}