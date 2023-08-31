<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Cast\Object_;

class CitaController extends Controller
{
    public function getAppointmentsByHorario($idHorario)
    {
        $citas = DB::select("SELECT ci.id, u.nombre
                             FROM citas ci, users u
                             WHERE ci.id_usuario=u.id
                             AND ci.id_horario='$idHorario'
                             ORDER BY id");
        return $citas;
    }

    public function scheduleAppointment(Request $request, $id) //ASIGNAR UNA CITA PENDIENTE A UN HORARIO
    {
        $request->validate([
            'idUsuario' => 'required',
        ]);

        /* $horario = Horario::findOrFail($id);
        $horario->disponible = false;
        $horario->save(); */

        $cita = new Cita();
        $cita->id_horario = $id;
        $cita->id_usuario = $request->idUsuario;
        $cita->aceptado = false;
        $cita->save();

        return response()->json(["mensaje" => "se asigno las cita correctamente"], 201);
    }

    public function scheduleAccept($idHorario, $idCita) //ACEPTAR UNA CITA
    {
        $cita = Cita::findOrFail($idCita);
        $cita->aceptado = true;
        $cita->save();

        $citas = DB::select("SELECT id
                            FROM citas
                            WHERE id_horario='$idHorario'
                            AND aceptado=false");
        foreach($citas as $ct) {
            Cita::destroy($ct->id);
        }

        $horario = Horario::findOrFail($idHorario);
        $horario->disponible = false;
        $horario->save();
        
        return response()->json(["mensaje" => "se modifico correctamente"], 201);
    }

    public function cancelAppointment($idHorario, $idCita)
    {
        $horario = Horario::findOrFail($idHorario);
        $horario->disponible = true;
        $horario->save();

        return Cita::destroy($idCita);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_horario' => 'required',
            'id_usuario' => 'required',
        ]);

        $cita = Cita::findOrFail($id);
        $cita->id_horario = $request->id_horario;
        $cita->id_usuario = $request->id_usuario;

        $cita->save();

        return response()->json(["mensaje" => "se modifico correctamente"], 201);
    }

    public function getHorariosYCitas($id) {
        $user = DB::select("SELECT id_rol, email FROM users WHERE id='$id'");
        $rol = $user[0]->id_rol;
        $email = $user[0]->email;

        if($rol == 1) {
            // CONSEGUIR CITAS PARA BENEFICIARIOS
            $appointments = DB::select("SELECT c.id, c.aceptado, h.fecha, h.hora_inicio, h.hora_final, u.email, u.nombre, c.id_horario 
                                    from citas c, horarios h, users u 
                                    where c.id_horario = h.id and h.id_docente=u.id and c.id_usuario=$id");
            foreach($appointments as $appointment) {
                $appointment->fecha = date_create($appointment->fecha);
                $appointment->fecha = date_format($appointment->fecha, "d/m/Y");
            }

            // CONSEGUIR HORARIOS PARA BENEFICIARIOS
            $horarios = DB::select("SELECT DISTINCT on (h.id) h.id, h.fecha, h.hora_inicio, h.hora_final, h.disponible, d.email, d.nombre
                                    FROM horarios h, users d, respuestas r, docente_tests dt
                                    WHERE r.email_user='$email'
                                    AND r.id_docente_test=dt.id
                                    AND dt.id_docente=d.id
                                    AND h.id_docente=d.id 
                                    AND h.disponible=true");

            $newHorarios = [];
            foreach($horarios as $horario) {
                //USUARIOS DE LAS CITAS DE CADA HORARIO
                $citas = DB::select("SELECT u.email
                                    FROM citas c, users u
                                    WHERE c.id_horario='$horario->id' AND c.id_usuario=u.id");

                $flag = true;
                //QUITAR HORARIO SI EXISTE EL USUARIO EN UNA CITA DE ESTE
                foreach($citas as $cita) {
                    if($cita->email == $email) {
                        $flag = false;
                    }
                }

                if($flag) {
                    //FORMATEAR FECHA
                    $horario->fecha = date_create($horario->fecha);
                    $horario->fecha = date_format($horario->fecha, "d/m/Y");
        
                    //PUSHEAR AL NUEVO ARRAY DE HORARIOS
                    $newHorarios[] = $horario;
                }
            }

            $tasks = array(
                "horarios" => $newHorarios,
                "citas" => $appointments
            );
            return $tasks;
        } else if($rol == 2 || $rol == 3) {
            // HORARIOS PARA DOCENTES
            $horarios = DB::select("SELECT h.id, h.fecha, h.hora_inicio, h.hora_final, h.disponible, u.email, u.nombre 
                                    from horarios h, users u 
                                    where h.id_docente=u.id and u.id=$id");

            $newHorarios = [];
            foreach($horarios as $horario) {
                //USUARIOS DE LAS CITAS DE CADA HORARIO
                $citas = DB::select("SELECT u.id
                                    FROM citas c, users u
                                    WHERE c.id_horario='$horario->id' AND c.id_usuario=u.id");

                if(count($citas) == 0) {
                    //FORMATEAR FECHA
                    $horario->fecha = date_create($horario->fecha);
                    $horario->fecha = date_format($horario->fecha, "d/m/Y");
        
                    //PUSHEAR AL NUEVO ARRAY DE HORARIOS
                    $newHorarios[] = $horario;
                }
            }

            // CITAS PARA DOCENTES
            $appointments = DB::select("SELECT DISTINCT on (h.id) c.id, c.aceptado, c.id_usuario, h.fecha , h.id as id_horario, h.hora_inicio, h.hora_final, h.disponible, 
                                        h.id_docente, u.email, u.nombre
                                        from citas c, horarios h, users u where h.id_docente=$id and c.id_horario=h.id and c.id_usuario = u.id");
            foreach($appointments as $appointment) {
                $appointment->fecha = date_create($appointment->fecha);
                $appointment->fecha = date_format($appointment->fecha, "d/m/Y");
            }
            
            $tasks = array(
                "horarios" => $newHorarios,
                "citas" => $appointments
            );
            return $tasks;
        }
    }
}
