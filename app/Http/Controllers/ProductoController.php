<?php

namespace App\Http\Controllers;

use App\Producto;
use App\Categoria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    public function validar_producto($datos)
    {
        $validator = Validator::make(
            $datos->all(),
            [
                'categoria_id' => 'required',
                'nombre' => 'required',
                'sku' => 'required',
                'descripcion' => 'required',
                // 'cantidad' => 'required',
                'precio_1' => 'required',
                'precio_2' => 'required',
            ],
            [
                'categoria_id.required' => 'La categoria a ingresar es necesaria',
                'nombre.required' => 'El nombre a ingresar es necesario',
                'sku.required' => 'El sku a ingresar es necesario',
                'descripcion.required' => 'La descripcion a ingresar es necesaria',
                // 'cantidad.required' => 'La cantidad a ingresar es necesaria',
                'precio_compra.required' => 'El precio 1 es necesario',
                'precio_venta.required' => 'el precio 2 es necesario',
            ]
        );

 
        if ($validator->fails()) {
            return ['estado' => 'failed_v', 'mensaje' => $validator->errors()];
        }
        return ['estado' => 'success', 'mensaje' => 'success'];
    }

    protected function registro_producto(Request $datos)
    {
        $validarDatos = $this->validar_producto($datos);

        if ($validarDatos['estado'] == 'success') {
            $producto = new Producto();
            $producto->user_id = Auth::user()->id;
            $producto->categoria_id = $datos->categoria_id;
            $producto->sku =  strtolower($datos->sku);
            $producto->nombre =  strtolower($datos->nombre);
            $producto->descripcion = ($datos->descripcion);


          
            $producto->cantidad = $datos->cantidad;


            $producto->precio_1 = $datos->precio_1;
            $producto->precio_2 = $datos->precio_2;
            $producto->stock = $datos->stock;
            $producto->activo = 'S';

            if ($producto->save()) {
                return ['estado'=>'success', 'mensaje'=>'Producto guardado con exito.'];
            } else {
                return ['estado'=>'failed', 'mensaje'=>'A ocurrido un error, verifique esten correctos los campo.'];
            }
        }
        return $validarDatos;
    }

    protected function traer_productos()
    {
        $listar = Producto::select([
                                    'producto.id',
                                    'producto.sku',
                                    'producto.nombre',
                                    'producto.descripcion as proDesc',
                                    'producto.cantidad',
                                    'producto.precio_1',
                                    'producto.precio_2',
                                    'categoria.descripcion as catDesc',
                                    'categoria.id as catId',
                                    'producto.created_at as creado',
                                    'u.name as nombreUsuario',
                                    'producto.imagen'
                                ])
                                    ->join('categoria', 'categoria.id', 'producto.categoria_id')
                                    ->join('users as u', 'u.id', 'producto.user_id')
                                    ->where('producto.activo','S')
                                    ->orderby('categoria.descripcion', 'asc')
                                    ->get();
        if (count($listar) > 0) {
            foreach ($listar as $key) {
                setlocale(LC_TIME, 'es_CL.UTF-8');
                $key->creado = Carbon::parse($key->creado)->formatLocalized('%d de %B del %Y %H:%M:%S');
            }
        }
        if (!$listar->isEmpty()) {
            return ['estado'=>'success' , 'productos' => $listar];
        } else {
            return ['estado'=>'failed', 'mensaje'=>'No existen productos.'];
        }
    }

    public function filter(Request $request)
    {
        if(trim($request->q)=='none'){
          return [];
        }
        
        return DB::select("SELECT id, concat(nombre,' - ',descripcion) as nombre, imagen, sku from producto
        where concat(nombre,' ',descripcion) like '%$request->q%'");
        //return $id;
    }

    protected function validar_modificar_producto($request)
    {
        switch ($request->campo) {
        case 'categoria_id':
          $validator = Validator::make(
              $request->all(),
              [
              'input' => 'required',
            ],
              [
              'input.required' => 'Debe ingresar una categoria.',
            ]
          );
          break;

        case 'nombre':
          $validator = Validator::make(
              $request->all(),
              [
              'input' => 'required',
            ],
              [
              'input.required' => 'Debe ingresar un nombre.',
            ]
          );
          break;

        case 'sku':
          $validator = Validator::make(
              $request->all(),
              [
              'input' => 'required',
            ],
              [
              'input.required' => 'Debe ingresar un sku.',
            ]
          );
          break;

        case 'descripcion':
          $validator = Validator::make(
              $request->all(),
              [
              'input' => 'required',
            ],
              [
              'input.required' => 'Debe ingresar una descripcion.',
            ]
          );
          break;

        case 'cantidad':
          $validator = Validator::make(
              $request->all(),
              [
              'input' => 'required',
            ],
              [
              'input.required' => 'Debe ingresar una cantidad.',
            ]
          );
          break;

        case 'precio_1':
          $validator = Validator::make(
              $request->all(),
              [
              'input' => 'required',
            ],
              [
              'input.required' => 'Debe ingresar precio 1.',
            ]
          );
          break;
        case 'precio_2':
          $validator = Validator::make(
              $request->all(),
              [
              'input' => 'required',
            ],
              [
              'input.required' => 'Debe ingresar precio 2.',
            ]
          );
          break;

        default:
        return null;
          break;
      }
        if ($validator->fails()) {
            return ['estado' => 'failed_v', 'mensaje' => $validator->errors()];
        }
        return ['estado' => 'success', 'mensaje' => 'success'];
    }

    protected function modificar_campo_producto(Request $request)
    {
        $validarDatos = $this->validar_modificar_producto($request);
        
        if ($validarDatos['estado'] == 'success') {
            $modificar = Producto::find($request->id);

            if (!is_null($modificar)) {
                switch ($request->campo) {

                case 'categoria_id':
   
                        $modificar->categoria_id = $request->input;

                        if ($modificar->save()) {
                            return ['estado'=>'success', 'mensaje'=>'Categoria actualizada.'];
                        } else {
                            return ['estado'=>'failed', 'mensaje'=>'A ocurrido un error al igreso de datos.'];
                        }
                    
                  break;

                case 'nombre':

                  $validar = strtolower($request->input);
                  $verificarCategoria = Producto::select([
                    'nombre',
                ])
                                        ->where('nombre', $validar)
                                        ->get();
                    if (count($verificarCategoria) > 0) {
                        return ['estado'=>'failed', 'mensaje'=>'El producto ya existe en nuestros registros.'];
                    }

                        $modificar->nombre = $validar;

                        if ($modificar->save()) {
                            return ['estado'=>'success', 'mensaje'=>'Producto actualizado.'];
                        } else {
                            return ['estado'=>'failed', 'mensaje'=>'A ocurrido un error al igreso de datos.'];
                        }
                    

                  break;

                case 'sku':

                  $validarSku = strtolower($request->input);
                  $verificarSku = Producto::select([
                    'sku',
                ])
                                        ->where('sku', $validarSku)
                                        ->get();
                    if (count($verificarSku) > 0) {
                        return ['estado'=>'failed', 'mensaje'=>'El sku ya existe en nuestros registros.'];
                    }

                        $modificar->sku = $validarSku;

                        if ($modificar->save()) {
                            return ['estado'=>'success', 'mensaje'=>'Sku actualizado.'];
                        } else {
                            return ['estado'=>'failed', 'mensaje'=>'A ocurrido un error al igreso de datos.'];
                        }
                    
                  break;

                  
                case 'descripcion':
   
                    $modificar->descripcion = $request->input;

                    if ($modificar->save()) {
                        return ['estado'=>'success', 'mensaje'=>'Descripcion actualizada.'];
                    } else {
                        return ['estado'=>'failed', 'mensaje'=>'A ocurrido un error al igreso de datos.'];
                    }
                
              break;

                case 'cantidad':
              
                      $modificar->cantidad = $request->input;
                      if ($modificar->save()) {
                          return ['estado'=>'success', 'mensaje'=>'Cantidad actualizada.'];
                      } else {
                          return ['estado'=>'failed', 'mensaje'=>'A ocurrido un error al igreso de datos.'];
                      }
              break;

                case 'precio_1':
   
                    $modificar->precio_1 = $request->input;

                    if ($modificar->save()) {
                        return ['estado'=>'success', 'mensaje'=>'Precio 1 actualizado.'];
                    } else {
                        return ['estado'=>'failed', 'mensaje'=>'A ocurrido un error al igreso de datos.'];
                    }
                
              break;

                case 'precio_2':
   
                    $modificar->precio_2 = $request->input;

                    if ($modificar->save()) {
                        return ['estado'=>'success', 'mensaje'=>'Precio 2 actualizado.'];
                    } else {
                        return ['estado'=>'failed', 'mensaje'=>'A ocurrido un error al igreso de datos.'];
                    }
                
              break;
     
                default:
                  return null;
                  break;
              }
            }
            return ['estado'=>'failed', 'mensaje'=>'El item que intentas modificar no existe.'];
        }
        return $validarDatos;
    }
    protected function inhabilitar_producto($id)
    {
      $producto = Producto::find($id);
      if ($producto) {
        $producto->activo = 'N';
        if($producto->save()){
          return [
            'estado'=>'success',
            'mensaje'=>'Producto inhabilitado'
          ];
        }
      }
    }

    protected function cantidad_productos()//productos activos
    {
        $listar = DB::table('producto')->where('activo','S')->count();
        if ($listar > 0) {
            return $listar;
        } else {
            return 0;
        }
    }

    protected function buscar_producto($producto)
    {
        $listar = Producto::select([
                                    'producto.id',
                                    'producto.sku',
                                    'producto.nombre',
                                    'producto.descripcion as proDesc',
                                    'producto.cantidad',
                                    'producto.precio_1',
                                    'producto.precio_2',
                                    'producto.created_at as creado',
                                    'categoria.descripcion as catDesc',
                                    'categoria.id as catId',
                                    'u.name as nombreUsuario',
                                    'producto.imagen'
                                    ])
                                    ->join('categoria', 'categoria.id', 'producto.categoria_id')
                                    ->join('users as u', 'u.id', 'producto.user_id')
                                    ->where('activo','S')
                                    ->whereRaw(
                                      "lower(producto.nombre) like lower('%$producto%') or 
                                      lower(categoria.descripcion) like lower('%$producto%') or
                                      lower(producto.descripcion) like lower('%$producto%')"
                                    )
                                    
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
            return ['estado'=>'failed', 'mensaje'=>'El producto no existe en nuestros registros,refrescando la tabla.'];
        }
    }


    //aqui toco alejandro
    public function subir_imagen(Request $r)
    {
   
       $validar_img=$this->validar_archivo($r->imagen,'imagen', 'image/jpeg','image/png');
     
       if ($validar_img == false) {
                return [
                    'estado' => 'failed',
                    'mensaje' => 'El archivo no es una imagen o logo'
                ];  
        }else{
          
                if ($validar_img==="nofile") {
                  return ['estado'=>'failed', 'mensaje'=>'No hay una imagen'];
                }
                if ($validar_img==true) {
                 
                  $producto = Producto::find($r->id);
                      if ($producto) {
                       
                        $file = $this->guardarArchivo($r->imagen,'foto_productos/');


                        if($file['estado'] == "success"){
                           $ruta = substr($producto->imagen, 8);
                            $borrar = Storage::delete($ruta);

                            $producto->imagen = 'storage/'.$file['archivo'];
                        }else{

                            // $u->avatar = '--';
                            //return ['estado'=>'failed','mensaje'=>'el archivo no se subio correctamente'];
                        } 

                        if ($producto->save()) {
                            return ['estado'=>'success','mensaje'=>'La imagen se ha subido correctamente'];
                        }
                      }
                }
                
                
                         
                    
            }
    }

    public function validar_archivo($archivo, $campo_name, $formato1, $formato2)
    {
       
        if($archivo == "null" || $archivo == "undefined"){
            return "nofile";
        }else{
            
            if($_FILES[$campo_name]['type']==$formato1 ){
                return true;
                //  dd("true");
                
            }
            if ($_FILES[$campo_name]['type']==$formato2) {
                return true;
            }

            else{
                // dd("false");
                return false;
            }
        }
    }

    protected function guardarArchivo($archivo, $ruta)
    {
    	try{
	        $filenameext = $archivo->getClientOriginalName();
	        $filename = pathinfo($filenameext, PATHINFO_FILENAME);
	        $extension = $archivo->getClientOriginalExtension();
	        $nombreArchivo = $filename . '_' . time() . '.' . $extension;
	        $rutaDB = $ruta . $nombreArchivo;

	        $guardar = Storage::put($ruta . $nombreArchivo, (string) file_get_contents($archivo), 'public');
          
             if ($guardar) {
                
	            return ['estado' =>  'success', 'archivo' => $rutaDB];
	        } else {
	            return ['estado' =>  'failed', 'mensaje' => 'error al guardar el archivo.'];
	        }
	    }catch (\Throwable $t) {
    			return ['estado' =>  'failed', 'mensaje' => 'error al guardar el archivo, posiblemente este dañado o no exista.'];
		}
    }
}
