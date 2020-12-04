<?php

namespace App\Http\Controllers;

use App\Cliente;
use App\CreditoDeuda;
use App\Ventas;
use App\Producto;
use Carbon\Carbon;
use App\DetalleVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\AssignOp\Concat;
use App\User;


use BigFish\PDF417\PDF417;
use BigFish\PDF417\Renderers\ImageRenderer;
use BigFish\PDF417\Renderers\SvgRenderer;

class VentasController extends Controller
{
    protected function registro_venta(Request $datos)
    {

        DB::beginTransaction();
        $venta = new Ventas();
        $venta->user_id = Auth::user()->id;

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
        if ($datos->cliente_id == []) {
            return ['estado'=>'failed', 'mensaje'=>'seleccione un cliente.'];
        } else {
            $venta->cliente_id = $datos->cliente_id;
        }

        if ($venta->save()) {
            $ingresarDetalle = $this->registro_detalle_venta($datos->carro, $venta->id);

            $cliente=Cliente::find($datos->cliente_id);

            if ($ingresarDetalle == true) {

                $ticketDetalle = $this->ticketDetalle($venta->id);
                $ticket = $this->ticket($venta->id);
                if ($ticketDetalle['estado'] == 'success' && $ticket['estado'] == 'success') {
                    $datos_finales = ['estado'=>'success',
                            'mensaje'=>'Venta realizada con exito, actualizando nuevo stock.',
                            'ticketDetalle'=>$ticketDetalle['ticketDetalle'],
                            'ticket'=>$ticket['ticket'],
                            'cliente'=>$cliente->nombres.' '.$cliente->apellidos.' - '.$cliente->rut,
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

    protected function traer_ventas()
    {
        $listar = Ventas::select([
                                            'ventas.id as idVenta',
                                            'ventas.created_at as creado',
                                            'ventas.venta_total',
                                            'users.name as nombreUsuarioVenta',
                                        ])
                                            ->join('users', 'users.id', 'ventas.user_id')
                                            ->orderby('ventas.id', 'desc')
                                            ->get();
        if (count($listar) > 0) {
            foreach ($listar as $key) {
                setlocale(LC_TIME, 'es_CL.UTF-8');
                $key->creado = Carbon::parse($key->creado)->formatLocalized('%d de %B del %Y %H:%M:%S');
            }
        }
        if (!$listar->isEmpty()) {
            return ['estado'=>'success' , 'ventas' => $listar];
        } else {
            return ['estado'=>'failed', 'mensaje'=>'No existen ventas.'];
        }
    }

    protected function buscar_venta_producto($producto)
    {
        $listar = Ventas::select([
                                    'ventas.id as idVenta',
                                    'ventas.created_at as creado',
                                    'ventas.venta_total',
                                    'users.name as nombreUsuarioVenta',
                                ])
                                    ->join('users', 'users.id', 'ventas.user_id')
                                    ->where('ventas.id', 'LIKE', '%' . $producto . '%')
                                    ->orWhere('ventas.created_at', 'LIKE', '%' . $producto . '%')
                                    ->orWhere('users.name', 'LIKE', '%' . $producto . '%')
                                        ->orderby('ventas.id', 'desc')
                                    ->get();

        if (count($listar) > 0) {
            foreach ($listar as $key) {
                setlocale(LC_TIME, 'es_CL.UTF-8');
                $key->creado = Carbon::parse($key->creado)->formatLocalized('%d de %B del %Y %H:%M:%S');
            }
        }

        if (!$listar->isEmpty()) {
            return ['estado'=>'success' , 'producto' => $listar];
        } else {
            return ['estado'=>'failed', 'mensaje'=>'El producto no existe en nuestros registros.'];
        }
    }

    protected function total_ventas()
    {
        $listar = DB::table('ventas')->sum('venta_total');
        if ($listar > 0) {
            return $listar;
        } else {
            return 0;
        }
    }

    protected function ultimas_ventas()
    {
        $listar = Ventas::select([
                                    'ventas.id as idVenta',
                                    'ventas.created_at as creado',
                                    'ventas.venta_total',
                                    'users.name as nombreUsuarioVenta',
                                ])
                                    ->join('users', 'users.id', 'ventas.user_id')
                                    ->orderby('ventas.id', 'desc')
                                    ->take(5)
                                    ->get();
        if (count($listar) > 0) {
            foreach ($listar as $key) {
                setlocale(LC_TIME, 'es_CL.UTF-8');
                $key->creado = Carbon::parse($key->creado)->formatLocalized('%d de %B del %Y %H:%M:%S');
            }
        }
        if (!$listar->isEmpty()) {
            return ['estado'=>'success' , 'ventas' => $listar];
        } else {
            return ['estado'=>'failed', 'mensaje'=>'No existen ventas.'];
        }
    }

    protected function mas_vendidos()
    {
        $listar = DB::select(
            "SELECT * from
            (select
             producto_id,
             producto.nombre
             from detalle_venta
             inner join ventas on ventas.id = detalle_venta.venta_id
             inner join  producto on producto.id = detalle_venta.producto_id
             group by producto_id, producto.nombre) producto
             inner join
             (select producto_id,
             sum(cantidad) as cantidad_total,
             sum(precio) as venta_total ,
			 sum(cantidad * precio) precio
             from detalle_venta
             inner join ventas on ventas.id = detalle_venta.venta_id
			 group by producto_id) venta

             on producto.producto_id = venta.producto_id
             order by venta.venta_total desc
             limit 5"
        );

        if (count($listar) > 0) {
            return ['estado'=>'success' , 'vendidos' => $listar];
        } else {
            return ['estado'=>'failed', 'mensaje'=>'No existen ventas.'];
        }
    }

    protected function buscar_producto_carro($producto)
    {
        $precio = empty(Auth::user()->tipo_precio)? 1 : Auth::user()->tipo_precio;

        if($precio == 1){
            $tipo_precio = 'precio_1';
        }
        if($precio == 2){
            $tipo_precio = 'precio_2';
        }

        // $listar = Producto::select([
        //                             'producto.id',
        //                             'producto.sku',
        //                             'producto.nombre',
        //                             'producto.cantidad',
        //                             'producto.'.$tipo_precio.' as precio',
        //                             ])
        //                             ->where('producto.activo','S')
        //                             ->whereRaw(
        //                                 "lower(producto.nombre) like lower('%$producto%') or
        //                                  lower(producto.sku) like lower('%$producto%')"
        //                             )


        //                             ->toSql();

        $listar = DB::select("SELECT producto.id,
                producto.sku,
                producto.nombre,
                producto.cantidad,
                producto.$tipo_precio as precio
        from producto where producto.activo = 'S' and (
        lower(producto.nombre) like lower('$producto') or
        lower(producto.sku) like lower('$producto') and producto.deleted_at is null)");


        if (count($listar) > 0) {
            foreach ($listar as $key) {
                $key->cantidad_ls = 1;
            }
            return ['estado'=>'success' , 'producto' => $listar];
        } else {
            return ['estado'=>'failed', 'mensaje'=>'El producto no existe o esta inhabilitado'];
        }
    }

    protected function traer_detalle_venta($idVenta)
    {
        $listar = DetalleVenta::select([
                                        'detalle_venta.cantidad as cantidadDetalle',
                                        'detalle_venta.precio',
                                        'producto.nombre',
                                        'producto.descripcion as proDesc',
                                        'categoria.descripcion as catDesc',
                                        'categoria.id as catId',
                                        'producto.imagen',
                                        'cliente.nombres',
                                        'cliente.apellidos'
                                        ])
                                        ->join('ventas', 'ventas.id', 'detalle_venta.venta_id')
                                        ->join('producto', 'producto.id', 'detalle_venta.producto_id')
                                        ->join('categoria', 'categoria.id', 'producto.categoria_id')
                                        ->join('users', 'users.id', 'ventas.user_id')
                                        ->join('cliente','cliente.id','ventas.cliente_id')
                                        ->orderby('ventas.id', 'desc')
                                        ->where('venta_id', $idVenta)
                                        ->get();

        if (!$listar->isEmpty()) {
            return ['estado'=>'success' , 'detalleVenta' => $listar];
        } else {
            return ['estado'=>'failed', 'mensaje'=>'No existen ventas.'];
        }
    }

    protected function mas_vendidos_grafico()
    {
        $listar = DB::select(
            " SELECT * from
            (select
             producto_id,
             producto.nombre
             from detalle_venta
             inner join ventas on ventas.id = detalle_venta.venta_id
             inner join  producto on producto.id = detalle_venta.producto_id
             group by producto_id, producto.nombre) producto
             inner join
             (select producto_id,
             sum(cantidad) as cantidad_total,
             sum(venta_total) as venta_total
             from detalle_venta
             inner join ventas on ventas.id = detalle_venta.venta_id
             group by producto_id) venta

             on producto.producto_id = venta.producto_id
             order by venta.venta_total desc
             limit 5"
        );

        $json_producto = [];
        $json_cantidad = [];
        $json_total = [];

        foreach ($listar as $key) {
            $json_producto[] = $key->nombre;
            $json_cantidad[] = $key->cantidad_total;
            $json_total[] = $key->venta_total;
        }

        return [
            'labels' => $json_producto,
            'datasets' =>[
                [
                    'label' => 'CANTIDAD',
                    'data' => $json_cantidad,
                    'backgroundColor' => [
                        '#D35400',
                        '#F1C40F',
                        '#28B463',
                        '#2980B9',
                        '#7D3C98'
                    ],
                    'hoverBackgroundColor' => [
                        '#E59866',
                        '#F9E79F',
                        '#A9DFBF',
                        '#7FB3D5',
                        '#C39BD3'
                    ],
                ],
            ]
        ];
    }

    protected function ultimas_ventas_grafico()
    {
        $listar = Ventas::select([
                                    'ventas.id as idVenta',
                                    'ventas.created_at as creado',
                                    'ventas.venta_total',
                                    'users.name as nombreUsuarioVenta',
                                ])
                                    ->join('users', 'users.id', 'ventas.user_id')
                                    ->orderby('ventas.id', 'desc')
                                    ->take(5)
                                    ->get();

        $json_idVenta = [];
        $json_total = [];

        foreach ($listar as $key) {
            $json_idVenta[] = $key->idVenta;
            $json_total[] = $key->venta_total;
        }

        return [
            'labels' => $json_idVenta,
            'datasets' =>[
                [
                    'label' => 'VENTAS',
                    'data' => $json_total,
                    'backgroundColor' => [
                        '#D35400',
                        '#F1C40F',
                        '#28B463',
                        '#2980B9',
                        '#7D3C98'
                    ],
                    'hoverBackgroundColor' => [
                        '#E59866',
                        '#F9E79F',
                        '#A9DFBF',
                        '#7FB3D5',
                        '#C39BD3'
                    ],
                ],
            ]
        ];
    }

    protected function menos_vendidos_grafico()
    {
        $listar = DB::select(
            " SELECT * from
            (select
             producto_id,
             producto.nombre
             from detalle_venta
             inner join ventas on ventas.id = detalle_venta.venta_id
             inner join  producto on producto.id = detalle_venta.producto_id
             group by producto_id, producto.nombre) producto
             inner join
             (select producto_id,
             sum(cantidad) as cantidad_total,
             sum(venta_total) as venta_total
             from detalle_venta
             inner join ventas on ventas.id = detalle_venta.venta_id
             group by producto_id) venta

             on producto.producto_id = venta.producto_id
             order by venta.venta_total asc
             limit 5"
        );

        $json_producto = [];
        $json_cantidad = [];
        $json_total = [];

        foreach ($listar as $key) {
            $json_producto[] = $key->nombre;
            $json_cantidad[] = $key->cantidad_total;
            $json_total[] = $key->venta_total;
        }

        return [
            'labels' => $json_producto,
            'datasets' =>[
                [
                    'label' => 'CANTIDAD',
                    'data' => $json_cantidad,
                    'backgroundColor' => [
                        '#D35400',
                        '#F1C40F',
                        '#28B463',
                        '#2980B9',
                        '#7D3C98'
                    ],
                    'hoverBackgroundColor' => [
                        '#E59866',
                        '#F9E79F',
                        '#A9DFBF',
                        '#7FB3D5',
                        '#C39BD3'
                    ],
                ],
            ]
        ];
    }

    protected function reporte_ventas($desde = '', $hasta = '')
    {

        if (isset($desde) && isset($hasta)) {
            $listar = Ventas::select([
                'ventas.id as idVenta',
                'ventas.created_at as creado',
                'ventas.venta_total',
                'users.name as nombreUsuarioVenta',
                'cliente.nombres',
                'cliente.apellidos',
                'ventas.pago_efectivo',
                'ventas.pago_debito',
                'ventas.vuelto',
                DB::raw('coalesce(credito_deuda.monto_credito, 0) as monto_credito')
            ])
                ->join('users', 'users.id', 'ventas.user_id')
                ->join('cliente', 'cliente.id', 'ventas.cliente_id')
                ->leftJoin('credito_deuda', 'credito_deuda.venta_id','ventas.id')
                ->whereBetween('ventas.created_at', [$desde, $hasta])
                ->orderby('ventas.id', 'desc')
                ->get();



            $suma_ventas = 0;
            $suma_credito = 0;
            $suma_vuelto = 0;
            $efectivo_real = 0; //efectivo - vuelto
            $debito = 0; //aqui no vamos a permitir vueltos cuando se pague unicamente con debito
            if (count($listar) > 0) {
                foreach ($listar as $key) {
                    setlocale(LC_TIME, 'es_CL.UTF-8');
                    $key->creado = Carbon::parse($key->creado)->formatLocalized('%d de %B del %Y %H:%M:%S');
                    $suma_ventas += $key->venta_total;
                    $suma_credito += $key->monto_credito;
                    $suma_vuelto += $key->vuelto;
                    $efectivo_real += ($key->pago_efectivo - $key->vuelto);
                    $debito += $key->pago_debito;
                }
            }
            if (!$listar->isEmpty()) {
                $dd = date('d/m/Y H:i', strtotime($desde));
                $hh = date('d/m/Y H:i', strtotime($hasta));
                return [
                        'estado'=>'success' ,
                        'ventas' => $listar,
                        'total'=>$suma_ventas,
                        'deuda'=>$suma_credito,
                        'vuelto'=>$suma_vuelto,
                        'efectivo_real' => $efectivo_real,
                        'debito' => $debito,
                        'fecha'=> 'Resumen desde '.$dd.' hrs -  hasta '.$hh.' hrs'

                    ];
            } else {
                return ['estado'=>'failed', 'mensaje'=>'No existen ventas en el rango de fecha seleccionado.'];
            }
        }
    }

    protected function reporte_ventas_excel($desde = '', $hasta = ''){
        if (isset($desde) && isset($hasta)) {
            $listar = Ventas::select([
                'ventas.id as idVenta',
                'ventas.created_at as creado',
                'ventas.venta_total',
                'users.name as nombreUsuarioVenta',
                'cliente.nombres',
                'cliente.apellidos',
                'ventas.pago_efectivo',
                'ventas.pago_debito',
                'ventas.vuelto',
                DB::raw('coalesce(credito_deuda.monto_credito, 0) as monto_credito')
            ])
                ->join('users', 'users.id', 'ventas.user_id')
                ->join('cliente', 'cliente.id', 'ventas.cliente_id')
                ->leftJoin('credito_deuda', 'credito_deuda.venta_id','ventas.id')
                ->whereBetween('ventas.created_at', [$desde, $hasta])
                ->orderby('ventas.id', 'desc')
                ->get();



            $suma_ventas = 0;
            $suma_credito = 0;
            $suma_vuelto = 0;
            $efectivo_real = 0; //efectivo - vuelto
            $debito = 0; //aqui no vamos a permitir vueltos cuando se pague unicamente con debito
            if (count($listar) > 0) {
                foreach ($listar as $key) {
                    setlocale(LC_TIME, 'es_CL.UTF-8');
                    $key->creado = Carbon::parse($key->creado)->formatLocalized('%d de %B del %Y %H:%M:%S');
                    $suma_ventas += $key->venta_total;
                    $suma_credito += $key->monto_credito;
                    $suma_vuelto += $key->vuelto;
                    $efectivo_real += ($key->pago_efectivo - $key->vuelto);
                    $debito += $key->pago_debito;
                }
            }
            if (!$listar->isEmpty()) {
                $dd = date('d/m/Y H:i', strtotime($desde));
                $hh = date('d/m/Y H:i', strtotime($hasta));
                // return [
                //         'estado'=>'success' ,
                //         'ventas' => $listar,
                //         'VENTA TOTAL'=>$suma_ventas,
                //         'EFECTIVO REAL' => $efectivo_real,
                //         'DEBITO' => $debito,
                //         'CREDITO'=>$suma_credito,
                //         'VUELTO'=>$suma_vuelto,


                //         'FECHA'=> 'Resumen desde '.$dd.' hrs -  hasta '.$hh.' hrs'

                //     ];


                return [
                    'respuesta' =>['estado' => 'success'],

                    'excel' => [
                        [],
                        ['titulo' =>'Resumen desde '.$dd.' hrs -  hasta '.$hh.' hrs'],
                        [],
                        [
                            'VENTA TOTAL'=>$suma_ventas,
                            'EFECTIVO REAL' => $efectivo_real,
                            'DEBITO' => $debito,
                            'CREDITO'=>$suma_credito,
                            'VUELTO'=>$suma_vuelto,


                        ],
                        $listar

                    ]
                ];


            } else {
                return ['estado'=>'failed', 'mensaje'=>'No existen ventas en el rango de fecha seleccionado.'];
            }
        }
    }

    protected function ticketDetalle($idVenta)
    {
        $listar = DetalleVenta::select([
            'detalle_venta.cantidad as cantidadDetalle',
            'detalle_venta.precio',
            'producto.nombre',
            ])
            ->join('ventas', 'ventas.id', 'detalle_venta.venta_id')
            ->join('producto', 'producto.id', 'detalle_venta.producto_id')
            ->join('categoria', 'categoria.id', 'producto.categoria_id')
            ->join('users', 'users.id', 'ventas.user_id')
            ->where('venta_id', $idVenta)
            ->get();

        if (!$listar->isEmpty()) {
            return ['estado'=>'success' , 'ticketDetalle' => $listar];
        } else {
            return ['estado'=>'failed', 'mensaje'=>'No existen ventas.'];
        }
    }
    protected function ticket($idVenta)
    {
        $listar = Ventas::select([
            'ventas.id as idVenta',
            'ventas.created_at as fechaVenta',
            'ventas.venta_total as totalVenta'
            ])
            ->where('ventas.id', $idVenta)
            ->get();

        if (!$listar->isEmpty()) {
            return ['estado'=>'success' , 'ticket' => $listar];
        } else {
            return ['estado'=>'failed', 'mensaje'=>'No existen ventas.'];
        }
    }

    protected function periodico_ventas_grafico($anio){
        $meses = [1,2,3,4,5,6,7,8,9,10,11,12];
        $obj = [];
        $val = [];

        foreach ($meses as $key) {
            $moment = DB::select("SELECT sum(venta_total) venta_total from ventas where
            EXTRACT(MONTH FROM created_at) = $key
            and EXTRACT(year FROM created_at) = $anio");

            $obj[$key] = $moment[0];

        }
        foreach ($obj as $key) {
            $val[] = !empty($key->venta_total)?$key->venta_total:0;
        }

        return [
            'labels' => ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
            'datasets' =>[
                [
                    'label' => 'Venta',
                    'data' => $val,
                    'backgroundColor' => [
                        '#D35400',
                        '#F1C40F',
                        '#28B463',
                        '#2980B9',
                        '#7D3C98',
                        '#D35400',
                        '#F1C40F',
                        '#28B463',
                        '#2980B9',
                        '#7D3C98',
                        '#D35400',
                        '#F1C40F',
                        '#28B463',
                        '#2980B9',
                        '#7D3C98'
                    ],
                    'hoverBackgroundColor' => [
                        '#E59866',
                        '#F9E79F',
                        '#A9DFBF',
                        '#7FB3D5',
                        '#C39BD3',
                        '#D35400',
                        '#F1C40F',
                        '#28B463',
                        '#2980B9',
                        '#7D3C98',
                        '#D35400',
                        '#F1C40F',
                        '#28B463',
                        '#2980B9',
                        '#7D3C98'
                    ],
                ],
            ]
        ];

        return response()->json($obj);
    }





    public function ambiente(Array $datos_venta){
        // activar todos los errores
        // ini_set('display_errors', true);
        // error_reporting(E_ALL);

        // // zona horaria
        // date_default_timezone_set('America/Santiago');


        // $config = [
        //     'firma' => [
        //         'file' => '/Users/alejandroesteban/Downloads/firma_digital_certificado.pfx',
        //         //'data' => '', // contenido del archivo certificado.p12
        //         'pass' => '1028',
        //     ],
        // ];

        // // trabajar en ambiente de certificación
        // \sasco\LibreDTE\Sii::setAmbiente(\sasco\LibreDTE\Sii::CERTIFICACION);

        // // solicitar token
         $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($config['firma']);
        // var_dump($token);

        // si hubo errores se muestran
        foreach (\sasco\LibreDTE\Log::readAll() as $error) {
            echo $error,"\n";
        }

        $obtener_rut_cliente = explode(' - ',$datos_venta['cliente']);


        $rut_cliente = (string) $this->div_rut($datos_venta['cliente']);


        $detalle = [];
        $recorre_detalle = 0;
        foreach ($datos_venta['ticketDetalle'] as $key) {

            $detalle[$recorre_detalle]['NmbItem'] = $key['nombre'];
            $detalle[$recorre_detalle]['QtyItem'] = $key['cantidadDetalle'];
            $detalle[$recorre_detalle]['PrcItem'] = $key['precio'];

            $recorre_detalle++;
        }

        // datos
         $factura = [
             'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => 33,
                    'Folio' => 1,
                ],
                'Emisor' => [
                    'RUTEmisor' => '77106553-8',
                    'RznSoc' => 'NEOFOX INFORMATICA LIMITADA',
                    'GiroEmis' => 'Informatico',
                    'Acteco' => 500000,
                    'DirOrigen' => 'RIO YAQUI 1971 ',
                    'CmnaOrigen' => 'LOS ANGELES',
                ],
                'Receptor' => [
                    'RUTRecep' => $rut_cliente,
                    'RznSocRecep' => $obtener_rut_cliente[0],
                    'GiroRecep' => 'tareas de Gobierno',
                    'DirRecep' => 'Villo pto alegre, los angeles',
                    'CmnaRecep' => 'Los Angeles, chile',
                ],
             ],
            'Detalle' => $detalle,
         ];


         return $factura;



    //     $caratula = [
    //         //'RutEnvia' => '11222333-4', // se obtiene de la firma
    //         'RutReceptor' => '60803000-K',
    //         'FchResol' => '2014-12-05',
    //         'NroResol' => 0,
    //     ];

        $xml = file_get_contents(storage_path('xml/folios/33.xml'));
        // Objetos de Firma y Folios
        $Firma = new \sasco\LibreDTE\FirmaElectronica($config['firma']);
        $Folios = new \sasco\LibreDTE\Sii\Folios($xml);
        return response()->json($Folios);

        // generar XML del DTE timbrado y firmado
        $DTE = new \sasco\LibreDTE\Sii\Dte($factura);
        $DTE->timbrar($Folios);
        $DTE->firmar($Firma);


        // generar sobre con el envío del DTE y enviar al SII
        $EnvioDTE = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDTE->agregar($DTE);
        $EnvioDTE->setFirma($Firma);
        $EnvioDTE->setCaratula($caratula);
        $EnvioDTE->generar();
       //Folio del DTE LibreDTE_T33F1 está fuera de rango
        if ($EnvioDTE->schemaValidate()) {
            echo $EnvioDTE->generar();
            //$track_id = $EnvioDTE->enviar();
            //var_dump($track_id);
        }

        // si hubo algún error se muestra
        foreach (\sasco\LibreDTE\Log::readAll() as $log)
            echo $log,"\n";











    }

    public function cambiar_tipo_precio($precio){

        $u = User::find(Auth::user()->id);
        $u->tipo_precio = $precio;

        if($u->save()){
            return [
                'user' => $u,
                'estado' => 'success'
            ];
        }

    }

    public function generar_un_xml(){
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="ISO-8859-1"?>
        <EnvioDTE xmlns="http://www.sii.cl/SiiDte" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sii.cl/SiiDte EnvioDTE_v10.xsd" version="1.0">
        <SetDTE ID="SetDoc">
         <Caratula version="1.0">
          <RutEmisor>97975000-5</RutEmisor>
          <RutEnvia>7880442-4</RutEnvia>
          <RutReceptor>60803000-K</RutReceptor>
          <FchResol>2003-09-02</FchResol>
          <NroResol>0</NroResol>
          <TmstFirmaEnv>2003-10-13T09:33:22</TmstFirmaEnv>
          <SubTotDTE>
           <TpoDTE>33</TpoDTE>
           <NroDTE>1</NroDTE>
          </SubTotDTE>
         </Caratula>
        <DTE version="1.0">
            <Documento ID="F60T33">
                <Encabezado>
                    <IdDoc>
                        <TipoDTE>33</TipoDTE>
                        <Folio>60</Folio>
                        <FchEmis>2003-10-13</FchEmis>
                    </IdDoc>
                    <Emisor>
                        <RUTEmisor>97975000-5</RUTEmisor>
                        <RznSoc>RUT DE PRUEBA</RznSoc>
                        <GiroEmis>Insumos de Computacion</GiroEmis>
                        <Acteco>31341</Acteco>
                        <CdgSIISucur>1234</CdgSIISucur>
                        <DirOrigen>Teatinos 120, Piso 4</DirOrigen>
                        <CmnaOrigen>Santiago</CmnaOrigen>
                        <CiudadOrigen>Santiago</CiudadOrigen>
                    </Emisor>
                    <Receptor>
                        <RUTRecep>77777777-7</RUTRecep>
                        <RznSocRecep>EMPRESA  LTDA</RznSocRecep>
                        <GiroRecep>COMPUTACION</GiroRecep>
                        <DirRecep>SAN DIEGO 2222</DirRecep>
                        <CmnaRecep>LA FLORIDA</CmnaRecep>
                        <CiudadRecep>SANTIAGO</CiudadRecep>
                    </Receptor>
                    <Totales>
                        <MntNeto>100000</MntNeto>
                        <TasaIVA>19</TasaIVA>
                        <IVA>19000</IVA>
                        <MntTotal>119000</MntTotal>
                    </Totales>
                </Encabezado>
                <Detalle>
                    <NroLinDet>1</NroLinDet>
                    <CdgItem>
                        <TpoCodigo>INT1</TpoCodigo>
                        <VlrCodigo>011</VlrCodigo>
                    </CdgItem>
                    <NmbItem>Parlantes Multimedia 180W.</NmbItem>
                    <DscItem/>
                    <QtyItem>20</QtyItem>
                    <PrcItem>4500</PrcItem>
                    <MontoItem>90000</MontoItem>
                </Detalle>
                <Detalle>
                    <NroLinDet>2</NroLinDet>
                    <CdgItem>
                        <TpoCodigo>INT1</TpoCodigo>
                        <VlrCodigo>0231</VlrCodigo>
                    </CdgItem>
                    <NmbItem>Mouse Inalambrico PS/2</NmbItem>
                    <DscItem/>
                    <QtyItem>1</QtyItem>
                    <PrcItem>5000</PrcItem>
                    <MontoItem>5000</MontoItem>
                </Detalle>
                <Detalle>
                    <NroLinDet>3</NroLinDet>
                    <CdgItem>
                        <TpoCodigo>INT1</TpoCodigo>
                        <VlrCodigo>1515</VlrCodigo>
                    </CdgItem>
                    <NmbItem>Caja de Diskettes 10 Unidades</NmbItem>
                    <DscItem/>
                    <QtyItem>5</QtyItem>
                    <PrcItem>1000</PrcItem>
                    <MontoItem>5000</MontoItem>
                </Detalle>
                <TED version="1.0">
                    <DD>
                        <RE>97975000-5</RE>
                        <TD>33</TD>
                        <F>60</F>
                        <FE>2003-10-13</FE>
                        <RR>77777777-7</RR>
                        <RSR>EMPRESA  LTDA</RSR>
                        <MNT>119000</MNT>
                        <IT1>Parlantes Multimedia 180W.</IT1>
                        <CAF version="1.0">
                            <DA>
                                <RE>97975000-5</RE>
                                <RS>RUT DE PRUEBA</RS>
                                <TD>33</TD>
                                <RNG>
                                    <D>1</D>
                                    <H>200</H>
                                </RNG>
                                <FA>2003-09-04</FA>
                                <RSAPK>
                                    <M>0a4O6Kbx8Qj3K4iWSP4w7KneZYeJ+g/prihYtIEolKt3cykSxl1zO8vSXu397QhTmsX7SBEudTUx++2zDXBhZw==</M>
                                    <E>Aw==</E>
                                </RSAPK>
                                <IDK>100</IDK>
                            </DA>
                            <FRMA algoritmo="SHA1withRSA">g1AQX0sy8NJugX52k2hTJEZAE9Cuul6pqYBdFxj1N17umW7zG/hAavCALKByHzdYAfZ3LhGTXCai5zNxOo4lDQ==</FRMA>
                        </CAF>
                        <TSTED>2003-10-13T09:33:20</TSTED>
                    </DD>
                    <FRMT algoritmo="SHA1withRSA">GbmDcS9e/jVC2LsLIe1iRV12Bf6lxsILtbQiCkh6mbjckFCJ7fj/kakFTS06Jo8i
        S4HXvJj3oYZuey53Krniew==</FRMT>
                </TED>
                <TmstFirma>2003-10-13T09:33:20</TmstFirma>
            </Documento>
        <Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
        <SignedInfo>
        <CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
        <SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
        <Reference URI="#F60T33">
        <Transforms>
        <Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
        </Transforms>
        <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
        <DigestValue>hlmQtu/AyjUjTDhM3852wvRCr8w=</DigestValue>
        </Reference>
        </SignedInfo>
        <SignatureValue>JG1Ig0pvSIH85kIKGRZUjkyX6CNaY08Y94j4UegTgDe8+wl61GzqjdR1rfOK9BGn93AMOo6aiAgolW0k/XklNVtM/ZzpNNS3d/fYVa1q509mAMSXbelxSM3bjoa7H6Wzd/mV1PpQ8zK5gw7mgMMP4IKxHyS92G81GEguSmzcQmA=</SignatureValue>
        <KeyInfo>
        <KeyValue>
        <RSAKeyValue>
        <Modulus>
        tNEknkb1kHiD1OOAWlLKkcH/UP5UGa6V6MYso++JB+vYMg2OXFROAF7G8BNFFPQx
        iuS/7y1azZljN2xq+bW3bAou1bW2ij7fxSXWTJYFZMAyndbLyGHM1e3nVmwpgEpx
        BHhZzPvwLb55st1wceuKjs2Ontb13J33sUb7bbJMWh0=
        </Modulus>
        <Exponent>
        AQAB
        </Exponent>
        </RSAKeyValue>
        </KeyValue>
        <X509Data>
        <X509Certificate>MIIEgjCCA+ugAwIBAgIEAQAApzANBgkqhkiG9w0BAQUFADCBtTELMAkGA1UEBhMC
        Q0wxHTAbBgNVBAgUFFJlZ2lvbiBNZXRyb3BvbGl0YW5hMREwDwYDVQQHFAhTYW50
        aWFnbzEUMBIGA1UEChQLRS1DRVJUQ0hJTEUxIDAeBgNVBAsUF0F1dG9yaWRhZCBD
        ZXJ0aWZpY2Fkb3JhMRcwFQYDVQQDFA5FLUNFUlRDSElMRSBDQTEjMCEGCSqGSIb3
        DQEJARYUZW1haWxAZS1jZXJ0Y2hpbGUuY2wwHhcNMDMxMDAxMTg1ODE1WhcNMDQw
        OTMwMDAwMDAwWjCBuDELMAkGA1UEBhMCQ0wxFjAUBgNVBAgUDU1ldHJvcG9saXRh
        bmExETAPBgNVBAcUCFNhbnRpYWdvMScwJQYDVQQKFB5TZXJ2aWNpbyBkZSBJbXB1
        ZXN0b3MgSW50ZXJub3MxDzANBgNVBAsUBlBpc28gNDEjMCEGA1UEAxQaV2lsaWJh
        bGRvIEdvbnphbGV6IENhYnJlcmExHzAdBgkqhkiG9w0BCQEWEHdnb256YWxlekBz
        aWkuY2wwgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGBALxZlVh1xr9sKQIBDF/6
        Va+lsHQSG5AAmCWvtNTIOXN3E9EQCy7pOPHrDg6EusvoHyesZSKJbc0TnIFXZp78
        q7mbdHijzKqvMmyvwbdP7KK8LQfwf84W4v9O8MJeUHlbJGlo5nFACrPAeTtONbHa
        ReyzeMDv2EganNEDJc9c+UNfAgMBAAGjggGYMIIBlDAjBgNVHREEHDAaoBgGCCsG
        AQQBwQEBoAwWCjA3ODgwNDQyLTQwCQYDVR0TBAIwADA8BgNVHR8ENTAzMDGgL6At
        hitodHRwOi8vY3JsLmUtY2VydGNoaWxlLmNsL2UtY2VydGNoaWxlY2EuY3JsMCMG
        A1UdEgQcMBqgGAYIKwYBBAHBAQKgDBYKOTY5MjgxODAtNTAfBgNVHSMEGDAWgBTg
        KP3S4GBPs0brGsz1CJEHcjodCDCB0AYDVR0gBIHIMIHFMIHCBggrBgEEAcNSBTCB
        tTAvBggrBgEFBQcCARYjaHR0cDovL3d3dy5lLWNlcnRjaGlsZS5jbC8yMDAwL0NQ
        Uy8wgYEGCCsGAQUFBwICMHUac0VsIHRpdHVsYXIgaGEgc2lkbyB2YWxpZG8gZW4g
        Zm9ybWEgcHJlc2VuY2lhbCwgcXVlZGFuZG8gZWwgQ2VydGlmaWNhZG8gcGFyYSB1
        c28gdHJpYnV0YXJpbywgcGFnb3MsIGNvbWVyY2lvIHkgb3Ryb3MwCwYDVR0PBAQD
        AgTwMA0GCSqGSIb3DQEBBQUAA4GBABMfCyJF0mNXcov8iEWvjGFyyPTsXwvsYbbk
        OJ41wjaGOFMCInb4WY0ngM8BsDV22bGMs8oLyX7rVy16bGA8Z7WDUtYhoOM7mqXw
        /Hrpqjh3JgAf8zqdzBdH/q6mAbdvq/yb04JHKWPC7fMFuBoeyVWAnhmuMZfReWQi
        MUEHGGIW</X509Certificate>
        </X509Data>
        </KeyInfo>
        </Signature></DTE>
        </SetDTE><Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
        <SignedInfo>
        <CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
        <SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
        <Reference URI="#SetDoc">
        <Transforms>
        <Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
        </Transforms>
        <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
        <DigestValue>4OTWXyRl5fw3htjTyZXQtYEsC3E=</DigestValue>
        </Reference>
        </SignedInfo>
        <SignatureValue>sBnr8Yq14vVAcrN/pKLD/BrqUFczKMW3y1t3JOrdsxhhq6IxvS13SgyMXbIN/T9ciRaFgNabs3pi732XhcpeiSmD1ktzbRctEbSIszYkFJY49k0eB+TVzq3eVaQr4INrymfuOnWj78BZcwKuXvDy4iAcx6/TBbAAkPFwMP9ql2s=</SignatureValue>
        <KeyInfo>
        <KeyValue>
        <RSAKeyValue>
        <Modulus>
        tNEknkb1kHiD1OOAWlLKkcH/UP5UGa6V6MYso++JB+vYMg2OXFROAF7G8BNFFPQx
        iuS/7y1azZljN2xq+bW3bAou1bW2ij7fxSXWTJYFZMAyndbLyGHM1e3nVmwpgEpx
        BHhZzPvwLb55st1wceuKjs2Ontb13J33sUb7bbJMWh0=
        </Modulus>
        <Exponent>
        AQAB
        </Exponent>
        </RSAKeyValue>
        </KeyValue>
        <X509Data>
        <X509Certificate>MIIEgjCCA+ugAwIBAgIEAQAApzANBgkqhkiG9w0BAQUFADCBtTELMAkGA1UEBhMC
        Q0wxHTAbBgNVBAgUFFJlZ2lvbiBNZXRyb3BvbGl0YW5hMREwDwYDVQQHFAhTYW50
        aWFnbzEUMBIGA1UEChQLRS1DRVJUQ0hJTEUxIDAeBgNVBAsUF0F1dG9yaWRhZCBD
        ZXJ0aWZpY2Fkb3JhMRcwFQYDVQQDFA5FLUNFUlRDSElMRSBDQTEjMCEGCSqGSIb3
        DQEJARYUZW1haWxAZS1jZXJ0Y2hpbGUuY2wwHhcNMDMxMDAxMTg1ODE1WhcNMDQw
        OTMwMDAwMDAwWjCBuDELMAkGA1UEBhMCQ0wxFjAUBgNVBAgUDU1ldHJvcG9saXRh
        bmExETAPBgNVBAcUCFNhbnRpYWdvMScwJQYDVQQKFB5TZXJ2aWNpbyBkZSBJbXB1
        ZXN0b3MgSW50ZXJub3MxDzANBgNVBAsUBlBpc28gNDEjMCEGA1UEAxQaV2lsaWJh
        bGRvIEdvbnphbGV6IENhYnJlcmExHzAdBgkqhkiG9w0BCQEWEHdnb256YWxlekBz
        aWkuY2wwgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGBALxZlVh1xr9sKQIBDF/6
        Va+lsHQSG5AAmCWvtNTIOXN3E9EQCy7pOPHrDg6EusvoHyesZSKJbc0TnIFXZp78
        q7mbdHijzKqvMmyvwbdP7KK8LQfwf84W4v9O8MJeUHlbJGlo5nFACrPAeTtONbHa
        ReyzeMDv2EganNEDJc9c+UNfAgMBAAGjggGYMIIBlDAjBgNVHREEHDAaoBgGCCsG
        AQQBwQEBoAwWCjA3ODgwNDQyLTQwCQYDVR0TBAIwADA8BgNVHR8ENTAzMDGgL6At
        hitodHRwOi8vY3JsLmUtY2VydGNoaWxlLmNsL2UtY2VydGNoaWxlY2EuY3JsMCMG
        A1UdEgQcMBqgGAYIKwYBBAHBAQKgDBYKOTY5MjgxODAtNTAfBgNVHSMEGDAWgBTg
        KP3S4GBPs0brGsz1CJEHcjodCDCB0AYDVR0gBIHIMIHFMIHCBggrBgEEAcNSBTCB
        tTAvBggrBgEFBQcCARYjaHR0cDovL3d3dy5lLWNlcnRjaGlsZS5jbC8yMDAwL0NQ
        Uy8wgYEGCCsGAQUFBwICMHUac0VsIHRpdHVsYXIgaGEgc2lkbyB2YWxpZG8gZW4g
        Zm9ybWEgcHJlc2VuY2lhbCwgcXVlZGFuZG8gZWwgQ2VydGlmaWNhZG8gcGFyYSB1
        c28gdHJpYnV0YXJpbywgcGFnb3MsIGNvbWVyY2lvIHkgb3Ryb3MwCwYDVR0PBAQD
        AgTwMA0GCSqGSIb3DQEBBQUAA4GBABMfCyJF0mNXcov8iEWvjGFyyPTsXwvsYbbk
        OJ41wjaGOFMCInb4WY0ngM8BsDV22bGMs8oLyX7rVy16bGA8Z7WDUtYhoOM7mqXw
        /Hrpqjh3JgAf8zqdzBdH/q6mAbdvq/yb04JHKWPC7fMFuBoeyVWAnhmuMZfReWQi
        MUEHGGIW</X509Certificate>
        </X509Data>
        </KeyInfo>
        </Signature></EnvioDTE>
        ');


        $timbre = $xml->SetDTE->DTE->Documento->TED;
        $timbre->DD->RE[0];
        // return response()->json(
        // DD ---------
        $RE = $timbre->DD->RE[0]; //Rut emisor
        $TD = $timbre->DD->TD[0]; // tipo documento
        $F = $timbre->DD->F[0]; // Folio
        $FE = $timbre->DD->FE[0]; // Fecha emision
        $RR = $timbre->DD->RR[0]; // Rut reseptor
        $RSR = $timbre->DD->RSR[0]; // Run Seror
        $MNT = $timbre->DD->MNT[0]; // monto
        $IT1 = $timbre->DD->IT1[0]; // DESCONOCIDO

        // DD->CAF->da
        $C_RE = preg_replace('/\s+/', '',$timbre->DD->CAF->DA->RE[0]); // Rur emisor
        $C_RS = preg_replace('/\s+/', '',$timbre->DD->CAF->DA->RS[0]); // Rason social
        $C_TD = $timbre->DD->CAF->DA->TD[0]; // TIPO DOCUMENTO
        $C_RNG = $timbre->DD->CAF->DA->RNG; //RANFGO DESDE HASTA (D Y H);
        $C_FA = preg_replace('/\s+/', '',$timbre->DD->CAF->DA->FA[0]); // FECHA A
        $C_RSAPK = $timbre->DD->CAF->DA->RSAPK; // E Y M
        $IDK = preg_replace('/\s+/', '',$timbre->DD->CAF->DA->IDK[0]); // DESCONOCIDO
        $FRMA = preg_replace('/\s+/', '',$timbre->DD->CAF->FRMA[0]); // FIRMA

        $TSTED = $timbre->DD->TSTED[0]; // desconocido
        $FRMT = preg_replace('/\s+/', '',$timbre->FRMT[0]); // desconocido



         $string = '<TED version="1.0"><DD><RE>'.$RE.'</RE><TD>'.$TD.'</TD><F>'.$F.'</F><FE>'.$FE.'</FE><RR>'.$RR.'</RR><RSR>'.$RSR.'</RSR><MNT>'.$MNT.'</MNT><IT1>'.$MNT.'</IT1><CAF version="1.0"><DA><RE>'.$C_RE.'</RE><RS>'.$C_RS.'</RS><TD>39</TD><RNG><D>'.$C_RNG->D[0].'</D><H>'.$C_RNG->H[0].'</H></RNG><FA>'.$C_FA.'</FA><RSAPK><M>'.preg_replace('/\s+/', '',$C_RSAPK->M[0]).'</M><E>'.preg_replace('/\s+/', '',$C_RSAPK->E[0]).'</E></RSAPK><IDK>'.$IDK.'</IDK></DA><FRMA algoritmo="SHA1withRSA">'.trim($FRMA).'</FRMA></CAF><TSTED>'.trim($TSTED).'</TSTED></DD><FRMT algoritmo="SHA1withRSA">'.trim($FRMT).'</FRMT></TED>';











         // $string = '<TED version="1.0"><DD><RE>97975000-5</RE><TD>33</TD><F>60</F><FE>2003-10-13</FE><RR>77777777-7</RR><RSR>EMPRESA LTDA</RSR><MNT>119000</MNT><IT1>119000</IT1><CAF version="1.0"><DA><RE>97975000-5</RE><RS>RUT DE PRUEBA</RS><TD>39</TD><RNG><D>1</D><H>200</H></RNG><FA>2003-09-04</FA><RSAPK><M>0a4O6Kbx8Qj3K4iWSP4w7KneZYeJ+g/prihYtIEolKt3cykSxl1zO8vSXu397QhTmsX7SBEudTUx++2zDXBhZw==</M><E>Aw==</E></RSAPK><IDK>100</IDK></DA><FRMA algoritmo="SHA1withRSA">g1AQX0sy8NJugX52k2hTJEZAE9Cuul6pqYBdFxj1N17umW7zG/hAavCALKByHzdYAfZ3LhGTXCai5zNxOo4lDQ==</FRMA></CAF><TSTED>2003-10-13T09:33:20</TSTED></DD><FRMT algoritmo="SHA1withRSA">GbmDcS9e/jVC2LsLIe1iRV12Bf6lxsILtbQiCkh6mbjckFCJ7fj/kakFTS06Jo8iS4HXvJj3oYZuey53Krniew==</FRMT></TED>';
        // return $string;
            // $xml = simplexml_load_string($string);


            // <TED version="1.0">
            // <DD>
            // <RE>97975000-5</RE>
            // <TD>33</TD>
            // <F>60</F>
            // <FE>2003-10-13</FE>
            // <RR>77777777-7</RR>
            // <RSR>EMPRESA LTDA</RSR>
            // <MNT>119000</MNT>
            // <IT1>Parlantes Multimedia 180W.</IT1>
            // <CAF version="1.0">
            // <DA>
            // <RE>97975000-5</RE>
            // <RS>RUT DE PRUEBA</RS>
            // <TD>33</TD>
            // <RNG>
            // <D>1</D>
            // <H>200</H>
            // </RNG>
            // <FA>2003-09-04</FA>
            // <RSAPK>
            // </DA>
            // <FRMA
            // algoritmo="SHA1withRSA">g1AQX0sy8NJugX52k2hTJEZAE9Cuul6pqYBdFxj1N17umW7zG/hAavCA
            // LKByHzdYAfZ3LhGTXCai5zNxOo4lDQ==</FRMA>
            // </CAF>
            // </DD>

            // </TED>




        //Encode the data, returns a BarcodeData object
        // $pdf417 = new PDF417();
        // $data = $pdf417->encode($string);
        // Create a PNG image
        // $renderer = new SvgRenderer([
        //     'format' => 'jpg',
        //     'scale' => 20,
        //     'ratio' => 10
        // ]);

        // $img = $renderer->render($data);

        return response()->json([

            'caratula' => $xml->SetDTE->Caratula,
            'dte' => $xml->SetDTE->DTE,
            // 'img' => $img->encoded,
            'xml' => $string

        ]);

        // foreach ($xml->obra as $obra) {
        //     $elementoObra = array(); // Elemento que contendrá datos de cada nodo.
        //     $elementoObra["obra"] = trim((string)$obra); // Se agrega el contenido del nodo como cadena.
        //     $matrizDeObras[] = $elementoObra; // El elemento de cada nod es agregado a la matriz general.
        //     unset($elementoObra); // El contenido de un elemento nodo se recreará en cada iteración.
        // }
        // var_dump ($matrizDeObras);
    }


    function div_rut($rut){
        $obtener_rut_cliente = explode(" - ", $rut);
        $revers_rut_client = str_split(strrev($obtener_rut_cliente[1]));
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
