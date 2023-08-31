<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArticuloController extends Controller
{ 
    public function store(Request $request)
    {
        $file = $request->documento;
        $randomName = $this->generateRandomString();
        $name = $randomName.'.'.$file->extension();
        $file->storeAs('', $name, 'public');
        
        $articulo = new Articulo();
        $articulo->id_docente = $request->id_docente;
        $articulo->titulo = $request->titulo;
        $articulo->descripcion = $request->descripcion;
        $articulo->documento = $name;
        $articulo->save();
         
        return response()->json(["message" => "Se guardo correctamente"], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "titulo" => "required",
            "descripcion" => "required"
        ]);

        $article = Articulo::findOrFail($id);
        $article->titulo = $request->titulo;
        $article->descripcion = $request->descripcion;
        
        if($request->documento) {
            Storage::delete("public/".$article->documento);
            $file = $request->documento;
            $randomName = $this->generateRandomString();
            $name = $randomName.'.'.$file->extension();
            $file->storeAs('', $name, 'public');
            $article->documento = $name;
        }

        $article->save();

        return response()->json(["message" => "Se guardo correctamente"], 201);
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    public function getArticlesByDocente($id_docente)
    {
        $user = User::findOrFail($id_docente);
        if($user->id_rol === 3) {
            $articles = DB::select(
                "SELECT a.id, a.titulo, a.descripcion, a.documento, a.id_docente, a.destacado, d.nombre as nombre_autor, d.perfil
                FROM articulos as a, users as d 
                WHERE d.id=a.id_docente ORDER BY id"
            );
        } else {
            $articles = DB::select(
                "SELECT a.id, a.titulo, a.descripcion, a.documento, a.id_docente, a.destacado, d.nombre as nombre_autor, d.perfil
                FROM articulos as a, users as d 
                WHERE d.id=a.id_docente AND (a.id_docente='$id_docente' OR a.destacado=true) ORDER BY id"
            );
        }
        return $articles;
    }

    public function getArticlePdf($id)
    {
        $article = Articulo::findOrFail($id);
        $name = $article->documento;
        $file = "storage/".$name;
        return response()->file($file);
    }

    public function getArticlesDestacados()
    {
        $articles = DB::select(
            "SELECT a.id, a.titulo, a.descripcion, a.documento, a.id_docente, d.nombre as nombre_autor, d.perfil
            FROM articulos as a, users as d 
            WHERE d.id=a.id_docente AND destacado=true ORDER BY id"
        );
        return $articles;
    }

    public function changeDestacado($id)
    {
        $article = Articulo::findOrFail($id);
        $article->destacado = !$article->destacado;
        $article->save();
        
        return response()->json(["message" => "Se guardo correctamente"], 201);
    }

    public function destroy($id)
    {
        $article = Articulo::findOrFail($id);
        Storage::delete("public/".$article->documento);
        return Articulo::destroy($id);
    }
}
