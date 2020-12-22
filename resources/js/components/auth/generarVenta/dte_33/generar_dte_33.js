import Multiselect from 'vue-multiselect';

export default {
  components: {
    Multiselect
  },
    data() {
        return {

            // BUSCADOR
            view_buscando:false,
            lista_buscando:[],
            buscando_txt:'',

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

            chk_credito: false,
            detalle_credito:'',
            monto_credito:0,

            confirm_compra:false,


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
            dismissSecs3: 5,
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
            get_vuelto:0,

            //datos para la factura
            ted:'',
            traer_ul_venta:false,
            local_storage_venta:(localStorage.getItem('venta_id')) ? localStorage.getItem('venta_id') :'',


            listarConf:{},
            ticketPrint:{},
            ticketPrintDetalle:{},


            mostrar_cliente:[],
            ver_cliente:false,
            btn_buscar_rut:false,
        }


    },

    created() {

        console.log(document);
        setInterval(this.getNow, 1000);
        console.log(document.getElementsByName('vuelto'));
    },



    methods: {

        buscando_personalizado(){

                        this.view_buscando = false;

                        this.lista_buscando = [];

                        if(this.buscando_txt.trim() == ''){

                            this.view_buscando = false;

                            this.lista_buscando = [];

                            this.axios.get('api/users/autocomplete/none').then((response) => {

                                this.view_buscando = false;

                                console.log(response)

                                 this.lista_buscando = response.data;

                            });

                        }else{

                            this.axios.get('api/users/autocomplete/'+this.buscando_txt).then((response) => {

                                this.view_buscando = true;

                                console.log(response)

                                 this.lista_buscando = response.data;

                            });

                        }





                    },


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
                        this.agregar(this.buscadorProducto);
                        this.buscadorProducto = '';
                        this.buscando_txt = '';
                        this.lista_buscando = [];
                        this.view_buscando = false;
                        console.log(this.view_buscando);
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

        agregar(sku) {
            let existe = false;
            for (let i = 0; i < this.arregloCarro.length; i++) {
                if (this.listarCarro.sku == this.arregloCarro[i].sku) {
                    existe = true;
                    break;
                }
            }
            if (existe == true) {
                this.listarCarro = [];
               console.log("inout");

                this.arregloCarro.map(function(item, index) {

                    if(item.sku == sku){
                        const input = document.getElementsByName("input_cantidad");
                        const input_posicion = input[index];
                        item.cantidad_ls = Number(item.cantidad_ls) + 1;
                        input_posicion.value = item.cantidad_ls;
                        console.log('item: ',index, item.cantidad_ls)

                           input_posicion.click();
                        //    console.log(this.ingresar_cantidad_carro(index, item.cantidad_ls));
                    }
                });


                this.showAlert5();
                // console.table(this.arregloCarro)
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
                    this.totalTemporal = parseInt(this.arregloCarro[i].precio) * (this.arregloCarro[i].cantidad_ls);
                    this.total = parseInt(this.total + this.totalTemporal);
                }
            }

        },

        ingresar_cantidad_carro(index, valor) {

            //  console.log($event.target.value);
            this.arregloCarro[index].cantidad_ls = valor;
            console.log('carga:',this.arregloCarro[index].cantidad_ls)
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
                this.totalTemporal = parseInt(this.arregloCarro[i].precio) * (this.arregloCarro[i].cantidad_ls);
                this.total = parseInt(this.total + this.totalTemporal);
            }

        },

        registrar_venta(cliente) {
            this.confirm_compra = true;
            if (document.getElementById('rut').value.trim() == ''){

                alert("Falta el cliente");
                this.confirm_compra = false;
                 return false;
            }

            if(this.chk_credito == true){
                if(this.monto_credito <= 0 || this.monto_credito == '' || this.monto_credito == null){
                    alert("El monto del credito debe ser mayor a 0")
                    this.confirm_compra = false;
                    return false;
                }
                if( this.detalle_credito == '' || this.detalle_credito == null){
                    alert("El detalle del credito no debe estar vacio")
                    this.confirm_compra = false;
                    return false;
                }
            }

            const data = {
                'carro': this.arregloCarro,
                'venta_total': this.total,
                'forma_pago_id': this.formaPago[0] + ',' + this.formaPago[1],
                'tipo_entrega_id': this.entrega,
                'cliente': cliente,
                'pago_efectivo': this.montoEfectivo,
                'pago_debito': this.montoDebito,
                'chk_credito': this.chk_credito,
                'detalle_credito': this.detalle_credito,
                'monto_credito': this.monto_credito,
                //este campo es nuevo
                'tipo_venta_id' : 33 //FACTURACION ELECTRONICA
                // 'vuelto': (Number(this.montoEfectivo)+ Number(this.montoDebito)) - Number(this.total)
            }



            this.axios.post('api/emitir_dte_33', data).then((response) => {

                if (response.data.estado == 'success') {
                    //limpiamos cada vez que se genera una venta el ID venta
                    localStorage.removeItem('venta_id');
                    this.producto_id = '';
                    this.errores3 = [];
                    // this.limpiarCarro();
                    this.correcto3 = response.data.mensaje;
                    this.chk_credito = false;
                    this.monto_credito = 0;
                    this.detalle_credito = '';
                    this.cliente_id = null;
                    this.montoEfectivo = 0;
                    this.montoDebito = 0;
                    this.buscando_txt = '';
                    this.showAlert3();
                    this.showModal();
                    this.ticketPrintDetalle = response.data.ticketDetalle;
                    this.cliente = response.data.cliente;
                    this.ticketPrint = response.data.ticket;
                    this.get_vuelto = response.data.vuelto;

                    this.confirm_compra = false;

                    //guardamos la ID de la venta en local Storage, por cada venta se ira actualizando
                    localStorage.setItem('venta_id', this.ticketPrint[0].idVenta);
                    this.local_storage_venta = localStorage.getItem('venta_id');
                }

                if (response.data.estado == 'failed') {
                    this.showAlert6();
                    this.confirm_compra = false;
                    this.errores6 = response.data.mensaje;
                }
                if (response.data.estado == 'failed_v') {
                    this.errores3 = response.data.mensaje;
                    this.confirm_compra = false;
                    return false;
                }

            });
        },

        //ultima venta del comprobante :1
        abrir_ultima_venta(component, venta_id){
            this.traer_ul_venta = true;
            this.axios.get('api/comprobante/'+venta_id).then((res)=>{
                if(res.data.estado == 'success'){
                    this.listarConf = res.data.configuraciones;
                    this.ticketPrint = res.data.venta;
                    this.ticketPrintDetalle = res.data.venta_detalle;
                    this.traer_ul_venta = false;
                    this.$refs[""+component+""].show();
                }else{
                    this.traer_ul_venta = false;
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


        renderChild(data) {
            return `
            <div style="border: 1px solid black;">
                <div class="row">
                    <div class="col-md-2">
                    <img class="avatar_indexx" src="${data.imagen}" />
                    </div>
                    <div class="col-md-6">
                    <span>${data.nombre}</span>
                    </div>
                </div>
             </div>
            `
        },

        getData(sku) {

             this.buscadorProducto = sku;
             this.traer_producto();


        },

        redirect_user($id){
            this.exist = false;
            this.$router.push({name: 'User', params: {id:$id }});
        },

        generar_un_xml(){
            const esto = this;
            alert("entrando..");
            this.axios.get('api/generar_un_xml').then((res)=>{


                this.ted = res.data.xml;
                console.log(this.ted);
            });
        },

        formatear_rut(){
            const $rut = document.getElementById('rut').value
            switch ($rut.length) {
                case 9: //xx.xxx.xxx-x
                    console.log("estan xx.xxx.xxx-x")

                    document.getElementById('rut').value = $rut.replace( /^(\d{2})(\d{3})(\d{3})(\w{1})$/, '$1.$2.$3-$4');

                break;
                case 8: //x.xxx.xxx-x
                    console.log("estan x.xxx.xxx-x");

                    document.getElementById('rut').value = $rut.replace( /^(\d{1})(\d{3})(\d{3})(\w{1})$/, '$1.$2.$3-$4');

                break;

                default:
                    break;
            }
            // console.log(this.rut.length == 8);
            // if(this.rut.length == 8){
            //     console.log("cantidad 9")
            //     this.rut = this.rut.replace( /^(\d{2})(\d{3})(\d{3})(\w{1})$/, '$1.$2.$3-$4')
            // }
            // if(this.rut.length == 7){
            //     console.log("cantidad 8")
            //     this.rut = this.rut.replace( /^(\d{1})(\d{3})(\d{3})(\w{1})$/, '$1.$2.$3-$4')
            // }


        },

        traer_rut_empresa(){
            this.ver_cliente =false;
            this.btn_buscar_rut = true;
            var rut = document.getElementById('rut').value;
            this.axios.get('api/traer_empresa/'+rut).then((res)=>{
                if(res.data.estado == 'success'){
                    this.mostrar_cliente = res.data.cliente;
                    this.ver_cliente = true;
                    this.btn_buscar_rut = false;

                }else{
                    this.mostrar_cliente = res.data.cliente;
                    this.ver_cliente = false;
                    this.btn_buscar_rut = false;
                }
            });
        },


        printDiv(contenido) {

            // html2canvas(document.querySelector("#pdfFactura")).then(canvas => {
            //     document.body.appendChild(canvas)
            // });
            // var ficha = document.getElementById(contenido);
            // var ventanaImpresion = window.open(' ', 'popUp');
            // ventanaImpresion.document.write(ficha.innerHTML);
            // ventanaImpresion.document.close();
            // ventanaImpresion.print();
            // ventanaImpresion.close();
        }

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

        document.getElementById("inputBuscar").focus();

    },
}
