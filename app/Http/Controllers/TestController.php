<?php

namespace App\Http\Controllers;

use App\Models\DocenteTest;
use App\Models\Test;
use App\Traits\PuntuacionesNaturales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    use PuntuacionesNaturales;

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
            'autor' => 'required',
            'tiempo' => 'required',
        ]);

        $test = new Test();
        $test->nombre = $request->nombre;
        $test->descripcion = $request->descripcion;
        $test->autor = $request->autor;
        $test->tiempo = $request->tiempo;
        $test->save();

        $assignMyself = new DocenteTest();
        $assignMyself->id_docente = $request->autor;
        $assignMyself->id_test = $test->id;
        $assignMyself->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function getTestToEdit($id)
    {
        $test = DB::select(
            "SELECT *
            FROM tests
            WHERE id='$id'"
        )[0];
        
        $escalas = DB::select("SELECT * FROM escalas WHERE id_test='$id' ORDER BY id");
        $escalaNatural = array("descripcion" => "Natural");
        array_unshift($escalas, $escalaNatural);
        $test->escalas = $escalas;

        $dimensiones = DB::select("SELECT * FROM dimensions WHERE id_test='$id' ORDER BY id");
        if(count($dimensiones)) {
            $idDimensiones = array_column($dimensiones, 'id');
            $preguntasPorDimension = DB::select(
                "SELECT id_pregunta, id_dimension FROM pregunta_dimensions WHERE id_dimension IN (".implode(',', $idDimensiones).")"
            );
            foreach($dimensiones as $dimension) {
                $dimension->preguntas = array_column($this->array_filter_column($preguntasPorDimension, 'id_dimension', $dimension->id), 'id_pregunta');
    
                //CALCULAR ESCALA NATURAL DE CADA DIMENSION
                $naturales = $this->getPuntuacionNatural($dimension->id);
                $dimension->valores = $naturales;
            }
        }
        
        $test->dimensiones = $dimensiones;

        $secciones = DB::select(
            "SELECT *
            FROM seccions
            WHERE id_test='$id'
            ORDER BY orden"
        );
        $preguntasPorSecciones = DB::select(
            "SELECT p.id, p.id_seccion, p.descripcion 
            FROM preguntas as p, seccions as s 
            WHERE p.id_seccion=s.id AND s.id_test='$id'
            ORDER BY p.id"
        );
        $reactivosPorSecciones = DB::select(
            "SELECT r.id, r.id_seccion, r.descripcion, r.predeterminado
            FROM reactivos as r, seccions as s 
            WHERE r.id_seccion=s.id AND s.id_test='$id'
            ORDER BY r.id"
        );
        $puntuacionesPorSecciones = DB::select(
            "SELECT pu.id, pu.id_pregunta, pu.id_reactivo, pu.asignado, s.id as id_seccion 
            FROM puntuacions as pu, reactivos as r, seccions as s
            WHERE pu.id_reactivo=r.id AND r.id_seccion=s.id AND s.id_test='$id'
            ORDER BY r.id"
        );
        foreach($secciones as $seccion) {
            $seccion->preguntas = $this->array_filter_column($preguntasPorSecciones, 'id_seccion', $seccion->id);
            $seccion->reactivos = $this->array_filter_column($reactivosPorSecciones, 'id_seccion', $seccion->id);
            $seccion->puntuaciones = $this->array_filter_column($puntuacionesPorSecciones, 'id_seccion', $seccion->id);;
        }
        $test->secciones = $secciones;

        return response()->json($test);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
            'tiempo' => 'required',
        ]);

        $test = Test::findOrFail($id);
        $test->nombre = $request->nombre;
        $test->descripcion = $request->descripcion;
        $test->tiempo = $request->tiempo;
        $test->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function destroy($id)
    {
        return Test::destroy($id);
    }

    public function getFullTest($idUser, $idRespuestaTest)
    {
        $user = DB::select("SELECT id_rol FROM users WHERE id='$idUser'");
        $rol = $user[0]->id_rol;

        if($rol == 1) {
            $respuesta = DB::select(" 
                SELECT r.estado, dt.id_test, d.email as email_docente
                FROM respuestas as r, docente_tests as dt, users as d
                WHERE r.id='$idRespuestaTest' AND r.id_docente_test=dt.id AND dt.id_docente=d.id
            ");
            $idTest = $respuesta[0]->id_test;
        } else {
            $idTest = $idRespuestaTest;
        }

        $test = DB::select("SELECT * FROM tests WHERE id='$idTest'");
        $test = $test[0];

        $caracteristicas = DB::select("SELECT * FROM caracteristicas WHERE id_test='$idTest' ORDER BY id");
        $test->caracteristicas = $caracteristicas;

        if($rol == 1) {
            $test->email_docente = $respuesta[0]->email_docente;
            $test->estado = $respuesta[0]->estado;
        }

        $secciones = DB::select("SELECT id, multimarcado, vacio, instruccion FROM seccions WHERE id_test='$idTest' ORDER BY orden");
        foreach($secciones as $seccion) {
            $id_seccion = $seccion->id;
            $preguntas =DB::select("SELECT id, descripcion FROM preguntas WHERE id_seccion='$id_seccion' ORDER BY id");
            $reactivos =DB::select("SELECT id, descripcion FROM reactivos WHERE id_seccion='$id_seccion' ORDER BY id");
            foreach($preguntas as $pregunta) {
                $id_pregunta = $pregunta->id;
                $puntuaciones = DB::select("SELECT id, id_reactivo, asignado FROM puntuacions WHERE id_pregunta='$id_pregunta' ORDER BY id_reactivo");
                $pregunta->puntuaciones = $puntuaciones;
            }
            $seccion->preguntas = $preguntas;
            $seccion->reactivos = $reactivos;
        }

        $test->secciones = $secciones;
        return $test;
    }

    public function getTestsForUser($id)
    {
        $data = DB::select("SELECT id_rol, email FROM users WHERE id='$id'");
        $rol = $data[0]->id_rol;

        switch($rol) {
            case 1:
                $email = $data[0]->email;
                $respuestas = DB::select("SELECT * FROM respuestas WHERE email_user='$email'");

                foreach($respuestas as $respuesta) {
                    $docente_test = DB::select(
                        "SELECT t.id, t.nombre, t.descripcion, a.nombre as autor, a.perfil, t.tiempo, u.nombre as nombre_docente
                        FROM tests as t, users as u, docente_tests as dt, users as a
                        WHERE t.id=dt.id_test AND u.id=dt.id_docente AND dt.id='$respuesta->id_docente_test' AND t.autor=a.id"
                    );
                    $respuesta->nombre_docente = $docente_test[0]->nombre_docente;
                    $respuesta->id_test = $docente_test[0]->id;
                    $respuesta->nombre_test = $docente_test[0]->nombre;
                    $respuesta->descripcion_test = $docente_test[0]->descripcion;
                    $respuesta->autor_test = $docente_test[0]->autor;
                    $respuesta->tiempo_test = $docente_test[0]->tiempo;
                    $respuesta->perfil = $docente_test[0]->perfil;
                }

                return $respuestas;
            case 2:
            case 3:
                $tests = DB::select("SELECT dt.id, dt.id_docente, dt.id_test, t.nombre, t.tiempo, t.descripcion, u.nombre as autor, u.perfil, t.autor as id_autor
                                     from docente_tests as dt, tests as t, users as u
                                     where dt.id_docente=$id and dt.id_test=t.id AND t.autor=u.id");
                foreach($tests as $test) {
                    $id_dt = $test->id;
                    $usuarios = DB::select("SELECT u.id, u.nombre, u.perfil
                                            FROM users as u, respuestas as r, docente_tests as dt
                                            WHERE r.email_user = u.email AND r.id_docente_test=dt.id AND dt.id='$id_dt'
                                            LIMIT 10");
                    $test->usuarios = $usuarios;
                }  

                return $tests;
        }
    }

    public function cambiarTipo(Request $request, $id) {
        $nuevoTipo = $request->tipo;
        if($nuevoTipo == "sin seleccionar") {
            DB::update("UPDATE tests SET type=NULL WHERE id=$id");
        } else {
            DB::update("UPDATE tests SET type='$nuevoTipo' WHERE id=$id");
        }

        return response()->json(["mensaje" => "se actualizó correctamente"], 201);
    }
}