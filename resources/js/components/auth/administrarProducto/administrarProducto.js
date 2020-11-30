import Multiselect from 'vue-multiselect';

export default {

    components: {
        Multiselect
    },

    data() {

        return {
            usuario: this.$auth.user(),	
            admin:1,
            // CABEZERA DE LA TABLA
            productosFieldsAdm: [
                { key: 'index', label: 'SKU', variant: 'dark', class: 'text-center' },
                { key: 'imagen', label: 'Imagen', class: 'text-center' },
                { key: 'prod', label: 'Producto', class: 'text-center' },
                { key: 'cat', label: 'Categoria', class: 'text-center' },
                { key: 'desc', label: 'Descripcion', class: 'text-center' },
                { key: 'stock', label: 'Stock', class: 'text-center' },
                { key: 'detalle', label: '' },
                { key: 'editar', label: '' },
                // { key: 'ventaModal', label: '' },
                { key: 'eliminarProd', label: '' },
            ],

            // LLENAR TABLA
            listarProductos: [],
            listarCategorias: [],

            //local storage
            itemArreglo1:[],
            itemArreglo2:[],

            // ALERTS MODIFICAR
            errores2: [],
            correcto2: '',
            dismissSecs2: 5,
            dismissCountDown2: 0,

            //ACTUALIZAR
            skuUpd:'',
            nombreUpd: '',
            descripcionUpd: '',
            categoria_id: null,
            cantidadUpd: '',
            precio_1: '',
            precio_2: '',

            // REGISTRO DE VENTA
            producto_id: '',
            cantidad: '',
            ventas: '',

            // ALERTS INGRESO VENTA
            errores3: [],
            correcto3: '',
            dismissSecs3: 5,
            dismissCountDown3: 0,

            // ALERT STOCK PRODUCTO
            errorStock: '',
            showAlertStock: false,

            // BUSCADOR
            buscadorProducto: '',
            productoSearch: '',
            idProducto: '0',
            btn_buscar_producto: true,

            // ALERTS BUSCADOR PRODUCTOS
            errores4: [],
            correcto4: '',
            dismissSecs4: 5,
            dismissCountDown4: 0,
            showAlertBuscar: false,
            errorBuscar: '',

            imagen:null,
            load_img:false,
        }
    },
    created(){
       
    },

    methods: {

        formatPrice(value) {
            let val = (value / 1).toFixed(0).replace('.', ',')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        },
        // BUSCADOR SELECT CATEGORIA
        buscadorCategorias({ descripcion }) {
            return `${descripcion}`
        },
        // ABRIR RUTAS POR CLICK
        url(ruta) {
            this.$router.push({ path: ruta }).catch(error => {
                if (error.name != "NavigationDuplicated") {
                    throw error;
                }
            });
        },

        preview_image(event) 
        {
            
            var reader = new FileReader();
            reader.onload = function () {
                var output = document.getElementById('output_image');
                output.src = reader.result;
                console.log(output.src)
            }
            reader.readAsDataURL(event.target.files[0]);
            this.imagen = event.target.files[0];
        },
        // MODAL VENTAS
        showModalVentas(id) {
            this.producto_id = id;
            this.$refs['ventasModal' + id].show();
        },
        hideModalVentas(id) {
            this.$refs['ventasModal' + id].hide();
        },
        // MODAL EDITAR
        showModalEditarProducto(id) {
            this.limpiar_inputs();
            this.$refs['editarModalProducto' + id].show();
        },
        hideModalEditarProducto(id) {
            this.$refs['editarModalProducto' + id].hide();
        },
        // SUCCESS EDITAR CONTADOR
        countDownChanged2(dismissCountDown2) {
            this.dismissCountDown2 = dismissCountDown2;
        },
        showAlert2() {
            this.dismissCountDown2 = this.dismissSecs2;
        },
        // SUCCESS VENTA CONTADOR
        countDownChanged3(dismissCountDown3) {
            this.dismissCountDown3 = dismissCountDown3;
        },
        showAlert3() {
            this.dismissCountDown3 = this.dismissSecs3;
        },
        // SUCCESS VENTA CONTADOR
        countDownChanged4(dismissCountDown4) {
            this.dismissCountDown4 = dismissCountDown4;
        },
        showAlert4() {
            this.dismissCountDown4 = this.dismissSecs4;
        },

        traer_productos() {
            this.listarProductos = [];
            this.axios.get('api/traer_productos').then((response) => {
                this.listarProductos = response.data.productos;
            })

        },

        traer_categorias() {
            this.axios.get('api/traer_categorias').then((response) => {
                this.listarCategorias = response.data.cat;
            })

        },
        
        actualizar_dato(id, campo, input) {
            const data = {
                'id': id,
                'campo': campo,
                'input': input,
            }
            console.log(data);
            this.axios.post('api/modificar_campo_producto', data).then((response) => {
                if (response.data.estado == 'success') {
                    this.correcto2 = response.data.mensaje;
                    this.showAlert2();
                    this.traer_productos();
                    this.hideModalEditarProducto(id);
                    this.skuUpd = '';
                    this.nombreUpd = '';
                    this.descripcionUpd = '';
                    this.categoria_id = null;
                    this.cantidadUpd = '';
                    this.precio_1 = '';
                    this.precio_2 = '';
                    this.errores2 = [];
                }
                if (response.data.estado == 'failed_v') {
                    this.errores2 = response.data.mensaje;
                }

                if (response.data.estado == 'failed') {
                    alert(response.data.mensaje);
                    this.campoUpd = '';
                }
            })
                .catch(error => {
                    alert(error);
                    this.loading = false;
                })
        },
        limpiar_inputs(){
            this.skuUpd= '';
            this.nombreUpd= '';
            this.descripcionUpd= '';
            this.categoria_id= null;
            this.cantidadUpd= '';
            this.precio_1= '';
            this.precio_2= '';

            // REGISTRO DE VENTA
            this.producto_id= '';
            this.cantidad= '';
        },

        actualizar_imagen(id){
            this.load_img = true;
            const data = new FormData();
            data.append('id', id);
            data.append('imagen', this.imagen);

            this.axios.post('api/subir_imagen', data).then((res) => {
                if (res.data.estado == 'success') {
                    this.load_img = false;
                    this.traer_productos();
                    var file = document.getElementsByClassName('imagen');
                    file[0].value ='';
                    this.imagen = null;
                    document.getElementById('output_image').src='';
                    alert(res.data.mensaje)
                } else {
                    this.load_img = false;
                    alert(res.data.mensaje)
                    console.log(res.data);
                }
            });
        },
        inhabilitar(id){

            
            var r = confirm("¿Quiere inhabilitar el producto con id "+id+'?');
            if (r == true) {
                this.axios.get('api/inhabilitar_producto/' + id).then((res) => {
                    if (res.data.estado == 'success') {
                        this.traer_productos();

                        alert(res.data.mensaje)
                    } else {
                        alert(res.data.mensaje)
                        console.log(res.data);
                    }
                });
            } else {
                alert("Proceso cancelado!")
            }

            
        },

        registrar_venta() {
            const data = {
                'producto_id': this.producto_id,
                'cantidad': this.cantidad,
                'venta': this.ventas,
            }
            this.axios.post('api/registro_venta', data).then((response) => {
                if (response.data.estado == 'success') {
                    this.correcto3 = response.data.mensaje;
                    this.showAlert3();
                    this.hideModalVentas(this.producto_id);
                    this.traer_productos();
                    this.producto_id = '';
                    this.cantidad = '';
                    this.ventas = '';
                    this.showAlertStock = false;
                    this.errores3 = [];
                }

                if (response.data.estado == 'failed') {
                    this.showAlertStock = true;
                    this.errorStock = response.data.mensaje;
                }
                if (response.data.estado == 'failed_v') {
                    this.errores3 = response.data.mensaje;
                    return false;
                }

            });
        },

        traer_producto() {
            this.listarProductos = [];
            this.axios.get('api/buscar_producto/' + this.buscadorProducto).then((response) => {

                if (this.buscadorProducto == '') {
                    this.showAlertBuscar = true;
                    this.errorBuscar = ("El campo no puede quedar vacio, ingrese un producto porfavor.");
                    this.traer_productos();
                } else {

                    if (response.data.estado == 'success') {

                        this.listarProductos = response.data.producto;
                        this.buscadorProducto = '';
                        this.btn_buscar_producto = true;
                    } else {

                        if (response.data.estado == 'failed') {
                            this.showAlert4();
                            this.errores4 = response.data.mensaje;
                            console.log(this.errores4);
                            this.buscadorProducto = '';
                            this.btn_buscar_producto = true;
                            this.traer_productos();
                        }
                    }
                }

            })
                .catch(error => {
                    alert(error);
                })
        },

        escribiendoProducto() {
            if (this.buscadorProducto.toLowerCase().trim() == '') {
                this.btn_buscar_producto = true;
            } else {

                this.btn_buscar_producto = false;
            }
        },

    },

    mounted() {
        this.traer_productos();
        this.traer_categorias();
    },

}