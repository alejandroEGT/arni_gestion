import Multiselect from 'vue-multiselect';

export default {
  components: {
    Multiselect
  },
    data() {
        return {

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

            // FORMA DE PAGO
            cliente_id: null,
            efectivo: false,
            debito: false,
            credito: false,
            montoEfectivo: 0,
            montoDebito: 0,
            montoCredito: 0,
            montoEfectivoDebito: 0,
            total: 0,
            totalTemporal: 0,

            arregloCarro: [],
            cantidadStock: '',

            listar_clientes: [],
            cliente:'',

            // CABEZERA DE LA TABLA AGREGAR
            carroFieldsAdm: [
                { key: 'sku', label: 'SKU' },
                { key: 'cantidad', label: 'Cantidad' },
                { key: 'producto', label: 'Producto' },
                { key: 'precioProd', label: 'Precio Producto' },
                { key: 'subtotal', label: 'Sub Total' },
                { key: 'opc', label: 'Eliminar' },

            ],

            // LLENAR TABLA
            listarCarro: [],

            // CABEZERA DE LA TABLA AGREGADOS
            productosFieldsAdm: [
                { key: 'cantidad', label: 'Cantidad', thStyle: { color: 'white' } },
                { key: 'detalle', label: 'Detalle' },
                { key: 'precioProd', label: 'Precio Producto' },
                { key: 'subtotal', label: 'Sub Total' },
                { key: 'opc', label: 'Eliminar' },

            ],

            // LLENAR TABLA
            listarProductos: [
                { cantidad: '1', detalle: 'iphone de klida paquepo', precioProd: '600', subtotal: '600' }
            ],

            formaPago: [],
            formaPagoOpcion: [
                { text: 'Efectivo', value: '1' },
                { text: 'T.Debito', value: '2' },
                // { text: 'T.Credito', value: '3' },
            ],

            entrega: [],
            entregaOpcion: [
                { text: 'Entrega Inmediata', value: '1' },
                { text: 'Por Despachar', value: '2' },
            ],

            // ALERTS BUSCADOR PRODUCTOS
            errores4: [],
            correcto4: '',
            dismissSecs4: 3,
            dismissCountDown4: 0,
            showAlertBuscar: false,
            errorBuscar: '',

            // ALERTS PRODUCTO REPETIDO
            dismissSecs5: 5,
            dismissCountDown5: 0,

            // CAMPOS VACIOS O STOCK MAYOR
            dismissSecs6: 5,
            dismissCountDown6: 0,
            errores6: [],

            // ALERTS INGRESO VENTA
            errores3: [],
            correcto3: '',
            dismissSecs3: 10,
            dismissCountDown3: 0,

            fechaLocal: '',
            horaLocal: '',
            usuario: this.$auth.user(),

            printVenta: {
                id: "printVenta",
                popTitle: 'good print',
                extraCss: 'https://www.google.com,https://www.google.com',
                extraHead: '<meta http-equiv="Content-Language"content="zh-cn"/>'
            },

            ticketPrint: [],
            ticketPrintDetalle: [],
            listarConf: [],
            logoNull: false,
        }


    },

    created() {
        setInterval(this.getNow, 1000);
    },

    methods: {

        traer_clientes() {
            this.axios.get('api/listar_clientes').then((response) => {
              this.listar_clientes = response.data.cuerpo;
                    })
      
          },

        buscadorClientes({ nombres,apellidos,rut }) {
            return `${nombres} ${apellidos} - ${rut}`
          },

        // MODAL VENTAS
        showModal() {
            this.$refs['ventasModal'].show();
        },
        hideModal() {
            this.$refs['ventasModal'].hide();
            // this.limpiarCarro();
        },

        formatPrice(value) {
            let val = (value / 1).toFixed(0).replace('.', ',')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        },

        // SUCCESS VENTA CONTADOR
        countDownChanged3(dismissCountDown3) {
            this.dismissCountDown3 = dismissCountDown3;
            if (this.dismissCountDown3 == 0) {
                this.limpiarCarro();
            }
        },
        showAlert3() {
            this.dismissCountDown3 = this.dismissSecs3;
        },

        // SUCCESS CARRO
        countDownChanged4(dismissCountDown4) {
            this.dismissCountDown4 = dismissCountDown4;
        },
        showAlert4() {
            this.dismissCountDown4 = this.dismissSecs4;
        },
        // PRODUCTO REPETIDO
        countDownChanged5(dismissCountDown5) {
            this.dismissCountDown5 = dismissCountDown5;
        },
        showAlert5() {
            this.dismissCountDown5 = this.dismissSecs5;
        },

        // CAMPOS VACIOS O STOCK MAYOR
        countDownChanged6(dismissCountDown6) {
            this.dismissCountDown6 = dismissCountDown6;
        },
        showAlert6() {
            this.dismissCountDown6 = this.dismissSecs6;
        },

        traer_producto() {
            this.listarCarro = [];
            this.axios.get('api/buscar_producto_carro/' + this.buscadorProducto).then((response) => {

                if (this.buscadorProducto == '') {
                    this.showAlertBuscar = true;
                    this.errorBuscar = ("El campo no puede quedar vacio, ingrese un producto porfavor.");
                } else {

                    if (response.data.estado == 'success') {
                        this.listarCarro = response.data.producto[0];
                        this.agregar();
                        this.buscadorProducto = '';
                        this.btn_buscar_producto = true;
                    } else {

                        if (response.data.estado == 'failed') {
                            this.showAlert4();
                            this.errores4 = response.data.mensaje;
                            console.log(this.errores4);
                            this.buscadorProducto = '';
                            this.btn_buscar_producto = true;
                        }
                    }
                }

            })
                .catch(error => {
                    alert(error);
                })
        },

        agregar() {
            let existe = false;
            for (let i = 0; i < this.arregloCarro.length; i++) {
                if (this.listarCarro.sku == this.arregloCarro[i].sku) {
                    existe = true;
                    break;
                }
            }
            if (existe == true) {
                this.listarCarro = [];
                this.showAlert5();
            } else {
                this.arregloCarro.push(this.listarCarro);
                localStorage.setItem('Carro', JSON.stringify(this.arregloCarro));
                this.total_temporal();
            }
        },

        cargarCarro() {
            if (localStorage.getItem("Carro") != null) {
                let carroGuardado = JSON.parse(localStorage.getItem("Carro"));
                this.total = 0;
                this.totalTemporal = 0;
                for (let i = 0; i < carroGuardado.length; i++) {
                    this.arregloCarro.push(carroGuardado[i]);
                    this.totalTemporal = parseInt(this.arregloCarro[i].precio_venta) * (this.arregloCarro[i].cantidad_ls);
                    this.total = parseInt(this.total + this.totalTemporal);
                }
            }

        },

        ingresar_cantidad_carro(index, $event) {
            // console.log($event.target.value);
            this.arregloCarro[index].cantidad_ls = $event.target.value;
            this.total_temporal();
            localStorage.removeItem('Carro');
            localStorage.setItem("Carro", JSON.stringify(this.arregloCarro));

        },

        eliminarItem(indice) {
            this.arregloCarro.splice(indice, 1);
            this.total_temporal();
            localStorage.removeItem('Carro');
            localStorage.setItem("Carro", JSON.stringify(this.arregloCarro));

        },

        limpiarCarro() {
            localStorage.removeItem('Carro');
            this.arregloCarro = [];
            this.total = 0;
            this.montoDebito = 0;
            this.montoCredito = 0;
            this.montoEfectivoDebito = 0;
            this.efectivo = false;
            this.debito = false;
            this.credito = false;
            this.formaPago = [];
            this.entrega = [];
        },

        total_temporal() {
            this.total = 0;
            this.totalTemporal = 0;
            for (let i = 0; i < this.arregloCarro.length; i++) {
                this.totalTemporal = parseInt(this.arregloCarro[i].precio_venta) * (this.arregloCarro[i].cantidad_ls);
                this.total = parseInt(this.total + this.totalTemporal);
            }

        },

        registrar_venta() {
            const data = {
                'carro': this.arregloCarro,
                'venta_total': this.total,
                'forma_pago_id': this.formaPago[0] + ',' + this.formaPago[1],
                'tipo_entrega_id': this.entrega,
                'cliente_id': this.cliente_id.id,
            }

            this.axios.post('api/registro_venta', data).then((response) => {
                if (response.data.estado == 'success') {
                    this.producto_id = '';
                    this.errores3 = [];
                    // this.limpiarCarro();
                    this.correcto3 = response.data.mensaje;
                    this.showAlert3();
                    this.showModal();
                    this.ticketPrintDetalle = response.data.ticketDetalle;
                    this.cliente = response.data.cliente;
                    this.ticketPrint = response.data.ticket;

                }

                if (response.data.estado == 'failed') {
                    this.showAlert6();
                    this.errores6 = response.data.mensaje;
                }
                if (response.data.estado == 'failed_v') {
                    this.errores3 = response.data.mensaje;
                    return false;
                }

            });
        },

        escribiendoProducto() {
            if (this.buscadorProducto.toLowerCase().trim() == '') {
                this.btn_buscar_producto = true;
            } else {

                this.btn_buscar_producto = false;
            }
        },

        traer_configuraciones() {
            this.axios.get('api/traer_configuraciones').then((response) => {
                if (response.data.estado == 'success') {
                    this.logoNull = true;
                    this.listarConf = response.data.configuraciones;

                }
            })
        },

        // getNow: function () {
        //     const today = new Date();
        //     const date = ('0' + today.getDate()).slice(-2) + '/' + ('0' + (today.getMonth() + 1)).slice(-2) + '/' + today.getFullYear();
        //     const time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        //     this.fechaLocal = date;
        //     this.horaLocal = time;
        // },

    },

    mounted() {
        this.traer_clientes();
        this.cargarCarro();
        this.traer_configuraciones();
    },
}
