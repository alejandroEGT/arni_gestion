<?php
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

use Illuminate\Http\Request;

Route::post('auth/login', 'APIController@login');
Route::post("json", function(){
    return "kalida";
});
Route::post('register', 'APIController@register');
//Rutas para el reinicio de password
Route::post('sendEmail','ResetPasswordController@sendEmail');
Route::post('resetPassword','ResetPasswordController@process');


Route::get('users/autocomplete/{q}','ProductoController@filter');

Route::group(['middleware' => 'auth.jwt'], function () {

    //login y user
    Route::get('auth/user', 'APIController@user');
    Route::post('auth/logout', 'APIController@logout');
    Route::get('allUser', 'APIController@allUser');
    Route::post('modificar_perfil', 'APIController@modificar_perfil');
    Route::post('cambiar_password', 'APIController@cambiar_password');
    Route::post('crear_usuario', 'APIController@crear_usuario');
    Route::get('traer_usuarios', 'APIController@traer_usuarios');
    Route::post('delete_usuario', 'APIController@delete_usuario');

    // rutas categoria
    Route::post('registro_categoria', 'CategoriaController@registro_categoria');
    Route::get('traer_categorias', 'CategoriaController@traer_categorias');
    Route::post('modificar_campo_categoria', 'CategoriaController@modificar_campo_categoria');
    Route::get('buscar_categoria/{categoria}', 'CategoriaController@buscar_categoria');
    Route::get('cantidad_categoria', 'CategoriaController@cantidad_categoria');
    Route::get('cantidad_productos_categoria', 'CategoriaController@cantidad_productos_categoria');
    Route::get('productos_menos_categoria', 'CategoriaController@productos_menos_categoria');

    // rutas producto
    Route::post('registro_producto', 'ProductoController@registro_producto');
    Route::get('traer_productos', 'ProductoController@traer_productos');
    Route::post('modificar_campo_producto', 'ProductoController@modificar_campo_producto');
    Route::get('cantidad_productos', 'ProductoController@cantidad_productos');
    Route::get('buscar_producto/{producto}', 'ProductoController@buscar_producto');
    Route::get('inhabilitar_producto/{producto}', 'ProductoController@inhabilitar_producto');

    Route::post('subir_imagen','ProductoController@subir_imagen');

    // rutas ventas
    Route::post('registro_venta', 'VentasController@registro_venta');
    Route::get('traer_ventas', 'VentasController@traer_ventas');
    Route::get('buscar_venta_producto/{producto}', 'VentasController@buscar_venta_producto');
    Route::get('total_ventas', 'VentasController@total_ventas');
    Route::get('ultimas_ventas', 'VentasController@ultimas_ventas');
    Route::get('mas_vendidos', 'VentasController@mas_vendidos');
    Route::get('buscar_producto_carro/{producto}', 'VentasController@buscar_producto_carro');
    Route::get('traer_detalle_venta/{idVenta}', 'VentasController@traer_detalle_venta');
    Route::get('mas_vendidos_grafico', 'VentasController@mas_vendidos_grafico');
    Route::get('ultimas_ventas_grafico', 'VentasController@ultimas_ventas_grafico');
    Route::get('menos_vendidos_grafico', 'VentasController@menos_vendidos_grafico');
    Route::get('reporte_ventas/{desde?}/{hasta?}', 'VentasController@reporte_ventas');
    Route::get('reporte_ventas_excel/{desde?}/{hasta?}', 'VentasController@reporte_ventas_excel');
    Route::get('periodico_ventas_grafico/{anio}', 'VentasController@periodico_ventas_grafico');

    Route::get('cambiar_tipo_precio/{tipo_precio}', 'VentasController@cambiar_tipo_precio');
    Route::get('generar_un_xml','VentasController@generar_un_xml');

    //rutas cliente
    Route::post('guardar_cliente','ClientesController@guardar');
    Route::get('listar_clientes','ClientesController@listar_clientes');
    Route::post('actualizar_cliente','ClientesController@actualizar_cliente');
    Route::get('inhabilitar_cliente/{cliente}','ClientesController@inhabilitar_cliente');
    Route::get('select_clientes','ClientesController@select_clientes');
    Route::get('cliente_deuda','ClientesController@cliente_deuda');


    // rutas configuraciones
    Route::post('registro_configuraciones', 'ConfiguracionesController@registro_configuraciones');
    Route::post('modificar_campo_configuraciones', 'ConfiguracionesController@modificar_campo_configuraciones');
    Route::get('traer_configuraciones', 'ConfiguracionesController@traer_configuraciones');

    //comprobante

    Route::get('comprobante/{venta_id}','VentasController@comprobante');

});
