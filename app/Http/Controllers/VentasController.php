<?php

namespace App\Http\Controllers;

use App\Cliente;
use App\Ventas;
use App\Producto;
use Carbon\Carbon;
use App\DetalleVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\AssignOp\Concat;

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
            $vuelto = (int)$datos->pago_efectivo - (int)$datos->venta_total;
        } elseif ($datos->forma_pago_id == '2,undefined') {
            $venta->forma_pago_id = '2';
            $vuelto = (int)$datos->pago_debito - (int)$datos->venta_total;
            
        }
        elseif ($datos->forma_pago_id == '1,2'){
            $venta->forma_pago_id = '1,2';
            $vuelto = ((int)$datos->pago_efectivo + (int)$datos->pago_debito) - (int)$datos->venta_total;
        }
        elseif ($datos->forma_pago_id == '2,1'){
            $venta->forma_pago_id = '2,1';
            $vuelto = ((int)$datos->pago_efectivo + (int)$datos->pago_debito) - (int)$datos->venta_total;
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
                DB::commit();
                $ticketDetalle = $this->ticketDetalle($venta->id);
                $ticket = $this->ticket($venta->id);
                if ($ticketDetalle['estado'] == 'success' && $ticket['estado'] == 'success') {
                    return ['estado'=>'success',
                            'mensaje'=>'Venta realizada con exito, actualizando nuevo stock.',
                            'ticketDetalle'=>$ticketDetalle['ticketDetalle'],
                            'ticket'=>$ticket['ticket'],
                            'cliente'=>$cliente->nombres.' '.$cliente->apellidos.' - '.$cliente->rut,
                            'vuelto'=>$vuelto
                            ];
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
            $venta->precio = $carro[$i]['precio_venta'];

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

        if (count($listar) > 0) {
            return ['estado'=>'success' , 'vendidos' => $listar];
        } else {
            return ['estado'=>'failed', 'mensaje'=>'No existen ventas.'];
        }
    }

    protected function buscar_producto_carro($producto)
    {
        $listar = Producto::select([
                                    'producto.id',
                                    'producto.sku',
                                    'producto.nombre',
                                    'producto.cantidad',
                                    'producto.precio_venta',
                                    ])
                                    ->where('producto.activo','S')
                                    ->whereRaw(
                                        "lower(producto.nombre) like lower('%$producto%') or
                                         lower(producto.sku) like lower('%$producto%')"
                                    )
                                    
                                    
                                    ->get();

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
                'ventas.pago_debito'
            ])
                ->join('users', 'users.id', 'ventas.user_id')
                ->join('cliente', 'cliente.id', 'ventas.cliente_id')
                ->whereBetween('ventas.created_at', [$desde.' 00:00:00', $hasta.' 23:59:59'])
                ->orderby('ventas.id', 'desc')
                ->get();

            $suma_ventas = 0;
            if (count($listar) > 0) {
                foreach ($listar as $key) {
                    setlocale(LC_TIME, 'es_CL.UTF-8');
                    $key->creado = Carbon::parse($key->creado)->formatLocalized('%d de %B del %Y %H:%M:%S');
                    $suma_ventas += $key->venta_total;
                }
            }
            if (!$listar->isEmpty()) {
                return ['estado'=>'success' , 'ventas' => $listar, 'total'=>$suma_ventas];
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
}
