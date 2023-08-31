<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupoController extends Controller
{
    public function index()
    {
        return DB::select("SELECT g.id, g.titulo, g.descripcion, g.id_docente, u.nombre as nombre_docente
                           FROM grupos g, users u
                           WHERE u.id = g.id_docente
                           ORDER BY g.id
        ");
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required',
            'descripcion' => 'required',
            'id_docente' => 'required',
        ]);

        $grupo = new Grupo();
        $grupo->titulo = $request->titulo;
        $grupo->descripcion = $request->descripcion;
        $grupo->id_docente = $request->id_docente;
        $grupo->estado = 1;

        $grupo->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function show($id)
    {
        return Grupo::find($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required',
            'descripcion' => 'required',
            'id_docente' => 'required',
        ]);

        $grupo = Grupo::findOrFail($id);
        $grupo->titulo = $request->titulo;
        $grupo->descripcion = $request->descripcion;
        $grupo->id_docente = $request->id_docente;

        $grupo->save();

        return response()->json(["mensaje" => "se actualizo correctamente"], 201);
    }

    public function destroy($id) 
    {
        return Grupo::destroy($id);
    }

    public function indexDocente($id_docente)
    {
        return DB::select("SELECT *
                           FROM grupos 
                           WHERE id_docente='$id_docente' AND estado='true'
                           ORDER BY id");
    }

    public function able($id)
    {
        $grupo = Grupo::findOrFail($id);

        if (!$grupo->estado) {
            $grupo->estado = true;
        } else {
            $grupo->estado = false;
        }

        $grupo->save();

        return response()->json(["mensaje" => "se actualizo correctamente"], 201);
    }
}
