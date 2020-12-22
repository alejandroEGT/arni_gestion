<?php

namespace App\Http\Controllers;

use App\Configuraciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ConfiguracionesController extends Controller
{
    public function validar_configuraciones($datos)
    {
        $validator = Validator::make(
            $datos->all(),
            [
                // 'logo' => 'file|mimes:png,jpg',
            ],
            [
                // 'logo.file' => 'Lo seleccionado debe ser un archivo',
                // 'logo.mimes' => 'El formato del archivo debe ser PNG o JPG',
            ]
        );


        if ($validator->fails()) {
            return ['estado' => 'failed_v', 'mensaje' => $validator->errors()];
        }
        return ['estado' => 'success', 'mensaje' => 'success'];
    }

    public function registro_configuraciones(Request $datos)
    {


        $validarDatos = $this->validar_configuraciones($datos);
        if ($validarDatos['estado'] == 'success') {

            if($this->valida_rut($datos->rut)){
                $traer = $this->traer_configuraciones();
                if ($traer['estado'] == 'success') {

                    $ruta = substr($traer['configuraciones']->logo, 8);
                    $borrar = Storage::delete($ruta);

                    $update = Configuraciones::find($traer['configuraciones']->id);

                    $update->empresa = $datos->empresa;
                    $update->direccion = $datos->direccion;
                    $update->rut = $datos->rut;


                    $update->rut = $datos->rut;

                    if ($datos->logo != 'undefined') {
                        $guardarArchivo = $this->guardarArchivo($datos->logo, 'ArchivosConfiguracion/');
                        if ($guardarArchivo['estado'] == "success") {
                            $update->logo = $guardarArchivo['archivo'];
                        } else {
                            return $guardarArchivo;
                        }
                    }


                    if ($update->save()) {
                        return ['estado'=>'success', 'mensaje'=>'Información guardada con éxito, por seguridad la sesión cerrará automaticamente.'];
                    } else {
                        return ['estado'=>'failed', 'mensaje'=>'A ocurrido un error, verifique esten correcto los campos.'];
                    }
                } else {
                    $conf = new Configuraciones();
                    $conf->empresa = $datos->empresa;
                    $conf->direccion = $datos->direccion;
                    $conf->rut = $datos->rut;
                    if ($datos->logo != 'undefined') {
                        $guardarArchivo = $this->guardarArchivo($datos->logo, 'ArchivosConfiguracion/');
                        if ($guardarArchivo['estado'] == "success") {
                            $conf->logo = $guardarArchivo['archivo'];
                        } else {
                            return $guardarArchivo;
                        }
                    }

                    if ($conf->save()) {
                        return ['estado'=>'success', 'mensaje'=>'Información guardada con éxito, por seguridad la sesión cerrará automaticamente.'];
                    } else {
                        return ['estado'=>'failed', 'mensaje'=>'A ocurrido un error, verifique esten correcto los campos.'];
                    }
                }
            }else{
                return ['estado'=>'failed', 'mensaje'=>'rut no valido'];
            }


        }
        return $validarDatos;
    }

    function valida_rut($rut)
    {
        try{
            $rut = preg_replace('/[^k0-9]/i', '', $rut);
            $dv  = substr($rut, -1);
            $numero = substr($rut, 0, strlen($rut)-1);
            $i = 2;
            $suma = 0;
            foreach(array_reverse(str_split($numero)) as $v)
            {
                if($i==8)
                    $i = 2;
                $suma += $v * $i;
                ++$i;
            }
            $dvr = 11 - ($suma % 11);

            if($dvr == 11)
                $dvr = 0;
            if($dvr == 10)
                $dvr = 'K';
            if($dvr == strtoupper($dv))
                return true;
            else
                return false;
        }
        catch(\Exception $e){
            return false;
        }
    }

    public function traer_configuraciones()
    {
        $listar = Configuraciones::select([
                                    'id',
                                    'logo',
                                    'empresa',
                                    'direccion',
                                    'rut'
                                ])
                                    ->first();

        if (!is_null($listar)) {
            return ['estado'=>'success' , 'configuraciones' => $listar];
        } else {
            return ['estado'=>'failed', 'mensaje'=>'No existe informacion.'];
        }
    }

    public function guardarArchivo($archivo, $ruta)
    {
        $filenameext = $archivo->getClientOriginalName();
        $filename = pathinfo($filenameext, PATHINFO_FILENAME);
        $extension = $archivo->getClientOriginalExtension();
        $nombreArchivo = $filename . '_' . time() . '.' . $extension;
        $rutaDB = 'storage/' . $ruta . $nombreArchivo;
        $guardar = Storage::put($ruta . $nombreArchivo, (string) file_get_contents($archivo), 'public');
        if ($guardar) {
            return ['estado' =>  'success', 'archivo' => $rutaDB];
        } else {
            return ['estado' =>  'failed', 'mensaje' => 'Error al intentar guardar el archivo.'];
        }
    }


    function div_rut($rut){
        $obtener_rut_cliente = explode(" - ", $rut);

        $revers_rut_client = str_split(strrev($obtener_rut_cliente[0]));
        $i=1;
        $rut='';
        $dv='';
        foreach ($revers_rut_client as $key) {
            if($i == 1){

                $dv = $key;
            }else{

                $rut= $key.$rut;
            }

            $i++;
        }
        return $rut.'-'.$dv;
        // 188056520
    }
}
