<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{
    public function index()
    {
        return DB::select("SELECT h.id, h.fecha, h.hora_inicio, h.hora_final, h.disponible, u.email,u.nombre 
        from horarios h, users u 
        where h.id_docente=u.id
        ");
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required',
            'hora_inicio' => 'required',
            'hora_final' => 'required',
            'id_docente' => 'required',
        ]);

        $grupo = new Horario();
        $grupo->fecha = $request->fecha;
        $grupo->hora_inicio = $request->hora_inicio;
        $grupo->hora_final = $request->hora_final;
        $grupo->id_docente = $request->id_docente;
        $grupo->disponible = true;

        $grupo->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function show($id)
    {
        return Horario::find($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fecha' => 'required',
            'hora_inicio' => 'required',
            'hora_final' => 'required',
        ]);

        $grupo = Horario::findOrFail($id);
        $grupo->fecha = $request->fecha;
        $grupo->hora_inicio = $request->hora_inicio;
        $grupo->hora_final = $request->hora_final;
        $grupo->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function destroy($id)
    {
        return Horario::destroy($id);
    }
}
