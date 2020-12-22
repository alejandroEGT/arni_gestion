<?php

namespace App\Http\Controllers;

use App\Empresas;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{


    public function guardar_empresa(Request $r){

        if(!$this->valida_rut($r->rut)){
            return ['estado' => 'failed', 'mensaje' => 'Rut no valido.'];
        }


        $verify = Empresas::where(['activo' => 'S','rut' => $r->rut])->first();

        if($verify){
            return ['estado' => 'failed', 'mensaje' => 'Empresa ya existe en sus registros'];
        }else{
            $em = new Empresas;
            $em->rut = strtolower($r->rut);
            $em->razon_social = $r->razon_social;
            $em->contacto = $r->contacto;
            $em->email = $r->email;
            $em->direccion = $r->direccion;
            $em->comuna = $r->comuna;
            $em->ciudad = $r->ciudad;
            $em->giro = $r->giro;
            $em->activo = 'S';

            if($em->save()){
                return ['estado' => 'success', 'mensaje' => 'Empresa registrada con exito'];
            }
        }

    }

    public function listar_empresas(){

        $list = Empresas::all();

        if($list){
            return ['estado'=>'success', 'lista'=>$list];
        }

        return ['estado'=>'failed', 'lista'=>[]];
    }

    public function traer_empresa($rut){

        if(!$this->valida_rut($rut)){
            return ['estado' => 'failed', 'mensaje' => 'Rut no valido.'];
        }

        $empresa = Empresas::where([
            'activo' => 'S',
            'rut' => $rut
        ])->first();

        $retVal = ($empresa) ? ['estado'=>'success', 'cliente' => $empresa] : ['estado'=>'failed', 'cliente'=>[]] ;
        return $retVal;
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
}
