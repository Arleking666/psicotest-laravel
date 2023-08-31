<?php

namespace App\Http\Controllers;

use App\Models\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeneficiarioDocenteController extends Controller
{
    public function getBenefToAssign($id) //BENEFICIARIOS NO ASIGNADOS PARA ASIGNARLOS
    {
        $users = DB::select("SELECT id, nombre, email, perfil FROM users WHERE id_rol=1");
        $emails = array_column(DB::select(
            "SELECT u.email
            from respuestas as bdt, users u, sedes as s
            where bdt.email_user=u.email and s.id=u.id_sede and bdt.id_docente_test=$id"
        ), 'email');

        return response()->json(["users" => $users, "emails" => $emails]);
    }

   public function assignBenefToTest(Request $request)
    {
        $request->validate([
            "id_docente_test" => "required"
        ]);

        $objeto = $request->objeto;
        foreach ($objeto as $valor) {
            $old = Respuesta::where("email_user", $valor)->where("id_docente_test", $request->id_docente_test)->first();
            if(!$old) {
                $benefTest = new Respuesta();
                $benefTest->email_user = $valor;
                $benefTest->id_docente_test = $request->id_docente_test;
                $benefTest->estado = 0;
                $benefTest->save();
            }
        }
        DB::delete("DELETE FROM respuestas WHERE id_docente_test='$request->id_docente_test' AND email_user NOT IN ('".implode("','", $objeto)."')");

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }
}
