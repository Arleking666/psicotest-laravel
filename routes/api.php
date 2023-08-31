<?php

use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeneficiarioDocenteController;
use App\Http\Controllers\CaracteristicaController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ConversionController;
use App\Http\Controllers\DimensionController;
use App\Http\Controllers\DocenteTestController;
use App\Http\Controllers\EscalaController;
use App\Http\Controllers\GrupoBeneficiarioController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\PreguntaController;
use App\Http\Controllers\PreguntaDimensionController;
use App\Http\Controllers\PuntuacionController;
use App\Http\Controllers\ReactivoController;
use App\Http\Controllers\RecuperarController;
use App\Http\Controllers\RespuestaController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/login/google', [AuthController::class, 'googleLogin']); 
Route::apiResource('recover', RecuperarController::class);
Route::put('recover/new/{id}', [RecuperarController::class, 'changeContrasena']);

Route::group(['middleware' => 'api'], function(){
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth/me', [AuthController::class, 'me']);

    //USER ROUTES
    Route::apiResource("user", UserController::class);
    Route::get('user/pic/{id}', [UserController::class, 'getPic']);
    Route::put('user/able/{id}', [UserController::class, 'able']);
    Route::get('user/getProfessors/{id}', [UserController::class, 'getProfessor']);

    //GRUPO ROUTES
    Route::apiResource("grupo", GrupoController::class);
    Route::get('grupo/docente/{id_docente}', [GrupoController::class, 'indexDocente']);
    Route::put('grupo/able/{id}', [GrupoController::class, 'able']);

    //GRUPOBENEFICIARIO ROUTES
    Route::apiResource("grupobeneficiario", GrupoBeneficiarioController::class);

    //TEST ROUTES
    Route::apiResource("test", TestController::class);
    Route::get("test/user/{id}", [TestController::class, 'getTestsForUser']);
    Route::get("test/edit/{id}", [TestController::class, 'getTestToEdit']);
    Route::get("test/full/{idUser}/{idRespuestaTest}", [TestController::class, 'getFullTest']);
    Route::get("test/ids/{id}", [DocenteTestController::class, 'show']);
    Route::get("test/assignBenef/{id}", [BeneficiarioDocenteController::class, 'getBenefToAssign']);
    Route::post("test/assigning/benef", [BeneficiarioDocenteController::class, 'assignBenefToTest']);
    Route::post("test/collab/{idTest}", [DocenteTestController::class, 'store']);
    Route::patch("test/tipo/{id}", [TestController::class, 'cambiarTipo']);

    //CARACTERISTICAS ROUTES
    Route::apiResource("caracteristica", CaracteristicaController::class);
    Route::get("caracteristica/test/{id}", [CaracteristicaController::class, 'CaracteristicasByTest']);

    //SECCIONES ROUTES
    Route::apiResource("seccion", SeccionController::class);
    Route::get("seccion/test/{idTest}", [SeccionController::class, 'seccionByTest']);

    //PREGUNTAS ROUTES
    Route::apiResource("pregunta", PreguntaController::class);
    Route::get("pregunta/seccion/{idSeccion}", [PreguntaController::class, 'preguntasBySeccion']);
    Route::post("pregunta/destroy", [PreguntaController::class, 'massDestroy']);

    //REACTIVOS ROUTES
    Route::apiResource("reactivo", ReactivoController::class);
    Route::get("reactivo/seccion/{idSeccion}", [ReactivoController::class, 'reactivosBySeccion']);
    Route::put("reactivo/predeterminado/{id}", [ReactivoController::class, 'changePredeterminado']);

    //SECCIONES ROUTES
    Route::put("seccion/multimarcado/{id}", [SeccionController::class, 'changeMultimarcado']);
    Route::put("seccion/vacio/{id}", [SeccionController::class, 'changeVacio']);
    Route::put("seccion/change/orden", [SeccionController::class, 'changeOrden']);

    //PUNTUACIONES ROUTES
    Route::post("puntuacion/reactivo", [PuntuacionController::class, 'puntuacionesByReactivos']);
    Route::get("puntuacion/seccion/{id}", [PuntuacionController::class, 'puntuacionesBySeccion']);
    Route::put("puntuacion/update", [PuntuacionController::class, 'massUpdate']);
    Route::put("puntuacion/voltear/{idPregunta}", [PuntuacionController::class, 'voltearPuntuaciones']);
    Route::put("puntuacion/invertir/{idPregunta}", [PuntuacionController::class, 'invertirPuntuaciones']);

    //DIMENSIONES ROUTES
    Route::apiResource("dimension", DimensionController::class);
    Route::put("pregunta/dimension/{id}", [PreguntaDimensionController::class, 'storeVarious']);
    Route::put("dimension/constante/{id}", [DimensionController::class, 'changeConstante']);

    //ESCALA ROUTES
    Route::apiResource("escala", EscalaController::class);

    //CONVERSION ROUTES
    Route::put("conversion/update", [ConversionController::class, 'massUpdate']);

    //RESPUESTA ROUTES
    Route::apiResource("respuesta", RespuestaController::class);
    Route::get("respuesta/user/{id}", [RespuestaController::class, 'getRespuestasByUser']);
    Route::patch("respuesta/interpretation/{id}", [RespuestaController::class, 'generateInterpretation']);

    //HORARIOS ROUTES
    Route::apiResource("horario", HorarioController::class);

    //CITAS ROUTES
    Route::put('cita/schedule/{id_schedule}', [CitaController::class, 'scheduleAppointment']);
    Route::get('cita/horario/{idHorario}', [CitaController::class, 'getAppointmentsByHorario']);
    Route::put('cita/accept/{idHorario}/{idCita}', [CitaController::class, 'scheduleAccept']);
    Route::put('cita/allschedule/{idHorario}/{idCita}', [CitaController::class, 'cancelAppointment']); 
    Route::get('cita/all/{id}', [CitaController::class, 'getHorariosYCitas']);

    //ARTICULOS ROUTES
    Route::apiResource("articulo", ArticuloController::class);
    Route::get('articulo/docente/{id_docente}', [ArticuloController::class, 'getArticlesByDocente']);
    Route::get('articulo/documento/{id}', [ArticuloController::class, 'getArticlePdf']);
    Route::get('articulo/landing/destacados', [ArticuloController::class, 'getArticlesDestacados']);
    Route::put('articulo/destacado/{id}', [ArticuloController::class, 'changeDestacado']);
    Route::post('articulo/update/{id}', [ArticuloController::class, 'update']);

    //CHANGECOLUMNS
    /* Route::get('change', [SeccionController::class, 'change']); */
});