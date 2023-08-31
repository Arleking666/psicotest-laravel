<?php

namespace App\Http\Controllers;

use App\Models\GrupoBeneficiario;
use Illuminate\Http\Request;

class GrupoBeneficiarioController extends Controller
{
    public function index()
    {
        return GrupoBeneficiario::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_grupo' => 'required',
            'id_beneficiario' => 'required',
        ]);

        $grupoestudiante = new GrupoBeneficiario();
        $grupoestudiante->id_grupo = $request->id_grupo;
        $grupoestudiante->id_beneficiario = $request->id_beneficiario;

        $grupoestudiante->save();

        return response()->json(["mensaje" => "se inscribio correctamente"], 201);
    }

    public function show($id)
    {
        return GrupoBeneficiario::find($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_grupo' => 'required',
            'id_beneficiario' => 'required',
        ]);

        $grupoestudiante = GrupoBeneficiario::findOrFail($id);
        $grupoestudiante->id_grupo = $request->id_grupo;
        $grupoestudiante->id_beneficiario = $request->id_beneficiario;

        $grupoestudiante->save();

        return response()->json(["mensaje" => "se modifico correctamente"], 201);
    }

    public function destroy($id)
    {
        return GrupoBeneficiario::destroy($id);
    }



}
