<?php

namespace App\Http\Controllers;

use App\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientesController extends Controller
{
    public function guardar(Request $r)
    {

        $nombres = ucwords($r->nombres);
        $apellidos = ucwords($r->apellidos);
        $rut_limpio = $this->limpiar($r->rut);

        if($this->validadorRut($rut_limpio)){
            $rut = (string)$rut_limpio;
            $verify = Cliente::whereRaw("upper(rut) = upper('$rut')")->first();
            if($verify){
                return ['estado'=>'failed','mensaje'=>'El rut ya estan en uso'];
            }else{
                $c = new Cliente;
                $c->rut = strtolower($rut);
                $c->nombres = $nombres;
                $c->apellidos = $apellidos;
                $c->contacto = $r->contacto;
                $c->email = $r->email;
                $c->direccion = $r->direccion;
                $c->activo = 'S';
                if ($c->save()) {
                    return ['estado'=>'success','mensaje'=>'Cliente guardado!'];
                }else{
                    return ['estado'=>'failed','mensaje'=>'No fue posible guardar el cliente'];
                }
            }
        }else{
            return ['estado'=>'failed','mensaje'=>'El rut no es valido'];
        }


    }

    public function listar_clientes()
    {
        $cuerpo = Cliente::where('activo','S')->get();
        if (count($cuerpo)>0) {
            return[
                'estado'=>'success',
                'cuerpo'=>$cuerpo
            ];
        }
        return[
            'estado'=>'failed',
            'cuerpo'=>null
        ];
    }
    public function select_clientes()
    {
        $cuerpo = Cliente::select([
            'id',
            DB::raw("concat(nombres,' ',apellidos,' - ',rut) texto")
        ])
                         ->where('activo','S')->get();
        if (count($cuerpo)>0) {
            return[
                'estado'=>'success',
                'cuerpo'=>$cuerpo
            ];
        }
        return[
            'estado'=>'failed',
            'cuerpo'=>null
        ];
    }

    public function actualizar_cliente(Request $r)
    {

        $c = Cliente::find($r->cliente_id);

        if ($c) {
            if ($c->activo=='N') {
                return ['estado'=>'failed', 'mensaje'=>'No se pudo actualizar la información'];
            }
            $c->nombres = ucwords($r->nombres);
            $c->apellidos = ucwords($r->apellidos);
            $c->contacto = ($r->contacto=='null')?null:$r->contacto;
            $c->email = ($r->email=='null')?null:$r->email;
            $c->direccion = ($r->direccion=='null')?null:$r->direccion;
            if ($c->save()) {
                return ['estado'=>'success', 'mensaje'=>'Información del cliente actualizada'];
            }else{
                return ['estado'=>'failed', 'mensaje'=>'No se pudo actualizar la información'];
            }
        }

    }

    public function inhabilitar_cliente($id)
    {
        $c = Cliente::find($id);
        if ($c) {
            $c->activo='N';
            if ($c->save()) {
                return ['estado'=>'success','mensaje'=>'Cliente inhabilitado'];
            }
            return ['estado'=>'failed','mensaje'=>'Eror en la inhabilidad del cliente'];
        }
    }


    public function cliente_deuda(){

        $listar = DB::select("SELECT
        cr.id credito_deuda_id,
        cr.detalle_credito,
        cr.monto_credito,
        concat(c.nombres,' ', c.apellidos) cliente,
        c.contacto,
        case
            when cr.activo = 'S' then 'PENDIENTE'
            when cr.activo = 'N' then 'CANCELADO'
            END AS activo,
        cr.venta_id,
        to_char(v.created_at, 'DD/MM/YYYY HH24:MI')
        from credito_deuda cr
        inner join cliente c on c.id = cr.cliente_id
        inner join ventas v on v.id = cr.venta_id");

        if(count($listar) > 0){
            return [ 'estado' => 'success', 'listar' => $listar ];
        }
        return [ 'estado' => 'failed', 'listar' => null ];


    }


    function validadorRut($trut)
    {
        $dvt = substr($trut, strlen($trut) - 1, strlen($trut));
        $rutt = substr($trut, 0, strlen($trut) - 1);
        $rut = (($rutt) + 0);
        $pa = $rut;
        $c = 2;
        $sum = 0;
        while ($rut > 0)
        {
            $a1 = $rut % 10;
            $rut = floor($rut / 10);
            $sum = $sum + ($a1 * $c);
            $c = $c + 1;
            if ($c == 8)
            {
                $c = 2;
            }
        }
        $di = $sum % 11;
        $digi = 11 - $di;
        $digi1 = ((string )($digi));
        if (($digi1 == '10'))
        {
            $digi1 = 'K';
        }
        if (($digi1 == '11'))
        {
            $digi1 = '0';
        }
        if (($dvt == $digi1))
        {
            return true;
        } else
        {
            return false;
        }
    }

    function limpiar($s)
    {
        $s = str_replace('á', 'a', $s);
        $s = str_replace('Á', 'A', $s);
        $s = str_replace('é', 'e', $s);
        $s = str_replace('É', 'E', $s);
        $s = str_replace('í', 'i', $s);
        $s = str_replace('Í', 'I', $s);
        $s = str_replace('ó', 'o', $s);
        $s = str_replace('Ó', 'O', $s);
        $s = str_replace('Ú', 'U', $s);
        $s= str_replace('ú', 'u', $s);

        //Quitando Caracteres Especiales
        $s= str_replace('"', '', $s);
        $s= str_replace(':', '', $s);
        $s= str_replace('.', '', $s);
        $s= str_replace(',', '', $s);
        $s= str_replace(';', '', $s);
        $s= str_replace('-', '', $s);
        return $s;
    }
}
