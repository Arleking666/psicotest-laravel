<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index() //PEDIR USUARIOS PARA LA PAGINA USERS DEL ADMINISTRADOR
    {
        $showUser = DB::select("SELECT u.id, u.nombre as nombre_user, u.email, u.genero, u.perfil, 
                                u.edad, u.id_sede, u.id_rol, u.estado,
                                s.nombre as nombre_sede,
                                CASE 
                                    WHEN r.id=1 THEN 'beneficiario' 
                                    WHEN r.id=2 THEN 'psicólogo' 
                                    WHEN r.id=3 THEN 'administrador' 
                                END as nombre_rol
                                from users u, rols r, sedes s 
                                where u.id_rol=r.id 
                                and u.id_sede=s.id
                                order by u.id;");
        return response()->json($showUser);
    }

    public function getProfessor($id)
    {
        $showUser = DB::select("SELECT u.id, u.nombre as nombre_user, u.email, u.perfil, u.genero, 
        u.edad, u.id_sede, u.id_rol, u.estado, r.nombre as nombre_rol, 
        s.nombre as nombre_sede 
        from users u, rols r, sedes s 
        where u.id_sede=s.id and u.id_rol=r.id and r.nombre='docente'");

        return response()->json($showUser);
    }

    public function getPic($id)
    {
        $usuario = DB::select("SELECT perfil from users where id='$id'");
        $foto = $usuario[0]->perfil;
        return response()->json(["perfil" => $foto], 201);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'email' => 'required',
            'password' => 'required',
            'genero' => 'required',
            'edad' => 'required',
            'id_sede' => 'required',
            'id_rol' => 'required',
            'estado' => 'required',
        ]);

        $user = new User();
        $user->nombre = $request->nombre;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->genero = $request->genero;
        $user->edad = $request->edad;
        $user->id_sede = $request->id_sede;
        $user->id_rol = $request->id_rol;
        $user->estado = $request->estado;

        $user->save();

        return response()->json(["mensaje" => "se guardo correctamente", "user" => $user], 209);
    }

    public function show($id)
    {
        $user = User::find($id);
        return $user;
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'perfil' => '',
            'nombre' => 'required',
            'genero' => 'required',
            'edad' => 'required',
            'id_sede' => 'required',
        ]);

        $user = User::findOrFail($id);
        $user->perfil = $request->perfil;
        $user->nombre = $request->nombre;
        $user->genero = $request->genero;
        $user->edad = $request->edad;
        $user->id_sede = $request->id_sede;
        $user->save();

        return response()->json(["mensaje" => "se actualizo correctamente", "user" => $user], 209);
    }

    public function able($id)
    {
        $user = User::findOrFail($id);

        if (!$user->estado) {
            $user->estado = true;
        } else {
            $user->estado = false;
        }

        $user->save();

        return response()->json(["mensaje" => "se actualizo correctamente"], 201);
    }

    public function destroy($id)
    {
        return User::destroy($id);
    }
}
