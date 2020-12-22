<?php

namespace App\Http\Controllers;

use App\Cliente;
use App\CreditoDeuda;
use DetalleVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Producto;
use Ventas;

class DteController extends Controller
{


    public function emitir_dte_33(Request $r){

        return $this->registro_venta($r);
    }

    protected function registro_venta(Request $datos)
    {

        dd($datos);

        DB::beginTransaction();
        $venta = new Ventas();
        $venta->user_id = Auth::user()->id;
        $venta->tipo_venta_id = $datos->tipo_venta_id;

        if ($datos->venta_total == '0') {
            return ['estado'=>'failed', 'mensaje'=>'ingrese minimo un producto al carro.'];
        } else {
            $venta->venta_total = $datos->venta_total;
            $venta->pago_efectivo = !empty($datos->pago_efectivo) ?  $datos->pago_efectivo : '0';
            $venta->pago_debito = !empty($datos->pago_debito) ? $datos->pago_debito : '0';

        }

        if ($datos->forma_pago_id == '1,undefined') {
            $venta->forma_pago_id = '1';
            if($datos->pago_efectivo == 0 || trim($datos->pago_efectivo)==''){
                return ['estado'=>'failed', 'mensaje'=>'ingrese el monto en efectivo.'];
            }
            $vuelto = (int)$datos->pago_efectivo - (int)$datos->venta_total;
            $venta->vuelto = ($vuelto < 0)? 0: $vuelto ;
        } elseif ($datos->forma_pago_id == '2,undefined') {
            $venta->forma_pago_id = '2';
            if($datos->pago_debito == 0 || trim($datos->pago_debito)==''){
                return ['estado'=>'failed', 'mensaje'=>'ingrese el monto en efectivo.'];
            }
            $vuelto = (int)$datos->pago_debito - (int)$datos->venta_total;
            $venta->vuelto = ($vuelto < 0)? 0: $vuelto ;

        }
        elseif ($datos->forma_pago_id == '1,2'){
            $venta->forma_pago_id = '1,2';
            if($datos->pago_debito == 0 || trim($datos->pago_debito)=='' || $datos->pago_efectivo == 0 || trim($datos->pago_efectivo)=='' ){
                return ['estado'=>'failed', 'mensaje'=>'ingrese el monto en efectivo y/o debito.'];
            }
            $vuelto = ((int)$datos->pago_efectivo + (int)$datos->pago_debito) - (int)$datos->venta_total;
            $venta->vuelto = ($vuelto < 0)? 0: $vuelto ;
        }
        elseif ($datos->forma_pago_id == '2,1'){
            $venta->forma_pago_id = '2,1';
            if($datos->pago_debito == 0 || trim($datos->pago_debito)=='' || $datos->pago_efectivo == 0 || trim($datos->pago_efectivo)=='' ){
                return ['estado'=>'failed', 'mensaje'=>'ingrese el monto en efectivo y/o debito.'];
            }
            $vuelto = ((int)$datos->pago_efectivo + (int)$datos->pago_debito) - (int)$datos->venta_total;
            $venta->vuelto = ($vuelto < 0)? 0: $vuelto ;
        }
        elseif ($datos->forma_pago_id == '3,undefined') {
            $venta->forma_pago_id = '3';

        } elseif ($datos->forma_pago_id == 'undefined,undefined') {
            return ['estado'=>'failed', 'mensaje'=>'seleccione una forma de pago.'];
        } else {
            $venta->forma_pago_id = $datos->forma_pago_id;
        }

        if ($datos->tipo_entrega_id == []) {
            return ['estado'=>'failed', 'mensaje'=>'seleccione un tipo de entrega.'];
        } else {
            $venta->tipo_entrega_id = $datos->tipo_entrega_id;
        }
        if ($datos->cliente == [] || $datos->cliente == [] || $datos->cliente == null) {
            return ['estado'=>'failed', 'mensaje'=>'seleccione un cliente.'];
        } else {
            // queda en cero cuando venta tiene hecha una factura
            $venta->cliente_id = 0; // queda en cero cuando venta tiene hecha una factura
        }

        if ($venta->save()) {
            $ingresarDetalle = $this->registro_detalle_venta($datos->carro, $venta->id);

            // se comenta porque el cliente siembre seria cero
            // $cliente=Cliente::find($datos->cliente_id);

            if ($ingresarDetalle == true) {

                $ticketDetalle = $this->ticketDetalle($venta->id);
                $ticket = $this->ticket($venta->id);
                if ($ticketDetalle['estado'] == 'success' && $ticket['estado'] == 'success') {
                    $datos_finales = ['estado'=>'success',
                            'mensaje'=>'Venta realizada con exito, actualizando nuevo stock.',
                            'ticketDetalle'=>$ticketDetalle['ticketDetalle'],
                            'ticket'=>$ticket['ticket'],
                            // 'cliente'=>$cliente->nombres.' '.$cliente->apellidos.' - '.$cliente->rut,
                            'vuelto'=>$vuelto
                            ];
                    // DB::rollBack();

                    if($datos->chk_credito == true){
                        $credito = new CreditoDeuda();
                        $credito->cliente_id = $datos->cliente_id;
                        $credito->detalle_credito = $datos->detalle_credito;
                        $credito->monto_credito = $datos->monto_credito;
                        $credito->activo = 'S'; //credito pendiente
                        $credito->venta_id = $venta->id;
                        if($credito->save()){

                            DB::commit(); /*descomentar aqui despues*/
                            return $datos_finales;

                        }
                    }else{
                            DB::commit(); /*descomentar aqui despues*/
                            return $datos_finales;
                    }
                    // return $datos_finales;
                    //return $this->ambiente($datos_finales);
                }
            } else {
                if ($ingresarDetalle == false) {
                    return ['estado'=>'failed', 'mensaje'=>'Error, la cantidad de venta es mayor al stock.'];
                }
                DB::rollBack();
                return ['estado'=>'failed', 'mensaje'=>'A ocurrido un error, verifique esten correctos los campo.'];
            }
        } else {
            DB::rollBack();
            return ['estado'=>'failed', 'mensaje'=>'A ocurrido un error, verifique esten correctos los campo.'];
        }
    }

    protected function registro_detalle_venta($carro, $venta_id)
    {


        for ($i = 0; $i < count($carro); $i++) {
            $productoCantidad = Producto::select([
            'producto.cantidad',
            'producto.id',
            'producto.nombre',
            'producto.stock',
        ])
            ->where('producto.id', $carro[$i]['id'])
            ->where('producto.stock', 'S')
            ->first();
            if ($productoCantidad) {
                if ($carro[$i]['cantidad_ls'] > $productoCantidad->cantidad) {
                    return false;
                }
            }
        }
        $count = 0;

        DB::beginTransaction();
        for ($i = 0; $i < count($carro); $i++) {
            $venta = new DetalleVenta;
            $venta->user_id = Auth::user()->id;
            $venta->venta_id = $venta_id;
            $venta->producto_id = $carro[$i]['id'];
            $venta->cantidad = $carro[$i]['cantidad_ls'];
            $venta->precio = $carro[$i]['precio'];

            if ($venta->save()) {
                $actualizarCantidad = Producto::find($carro[$i]['id']);

                if ($actualizarCantidad->stock == 'S') {
                    $actualizarCantidad->cantidad = $actualizarCantidad->cantidad - $carro[$i]['cantidad_ls'];
                    if ($actualizarCantidad->save()) {
                        $count++;
                    }
                } else {
                    $actualizarCantidad->cantidad = null;
                    if ($actualizarCantidad->save()) {
                        $count++;
                    }
                }
            }
        }

        if (count($carro) == $count) {
            DB::commit();
            return true;
        } else {
            DB::rollBack();
            return false;
        }
    }
}
