<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecuperarMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = "Recuperar contraseña de usuario";
    public $codigo = "";
    public $nombre = "";

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($codigo, $nombre)
    {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails/recuperar')->with('codigo', $this->codigo)->with('nombre', $this->nombre);
    }
}
