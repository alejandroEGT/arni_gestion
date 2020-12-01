
export default {
    data() {


        return {
            usuario: this.$auth.user(),
            admin:1,
            printVenta: {
                id: "printVenta",
                popTitle: 'good print',
                extraCss: 'https://www.google.com,https://www.google.com',
                extraHead: '<meta http-equiv="Content-Language"content="zh-cn"/>'
            },
            printDetalle: {
                id: "printDetalle",
                popTitle: 'good print',
                extraCss: 'https://www.google.com,https://www.google.com',
                extraHead: '<meta http-equiv="Content-Language"content="zh-cn"/>'
            },


            desde: '',
            hasta: '',
            hora_h:'23:59',
            hora_d:'00:00',
            suma_ventas:0,
            vuelto:0,
            credito:0,
            filtro:false,
            resumen_titulo:'',
            efectivo_real:0,
            debito:0,

            // CABEZERA DE LA TABLA VENTAS
            reporteVentasFieldsAdm: [
                { key: 'index', label: 'ID', variant: 'dark' },
                { key: 'venta', label: 'Venta Total' },
                { key: 'fecha', label: 'Fecha Venta' },
                { key: 'creado', label: 'Creado Por' },
                { key: 'cliente', label: 'Cliente' },
                { key: 'tipo_pago', label:'Tipo de pago' },
                { key: 'deuda_credito', label:'Credito' },
                { key: 'vuelto', label:'Vuelto' },
                { key: 'detalle', label: '' },


            ],
            // LLENAR TABLA VENTAS
            listarReporteVentas: [],

            // CABEZERA DE LA TABLA DETALLE VENTA
            reporteDetalleVentaFieldsAdm: [
                { key: 'imagen', label: 'Imagen' },
                { key: 'nombre', label: 'Nombre producto' },
                { key: 'descripcion', label: 'Descripcion' },
                { key: 'categoria', label: 'Categoria' },
                { key: 'precio', label: 'Precio' },
                { key: 'cantidad', label: 'Cantidad Vendida' },
                { key: 'cliente', label: 'Cliente' },



            ],
            // LLENAR TABLA DETALLE VENTAS
            listarReporteDetalleVentas: [],
        }
    },
    methods: {

        formatPrice(value) {
            let val = (value / 1).toFixed(0).replace('.', ',')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        },

        // MODAL EDITAR
        showModalDetalleVenta(id, idVenta) {
            this.$refs['reporte' + id].show();
            this.traer_detalle_ventas(idVenta);
        },
        hideModalDetalleVenta(id, idVenta) {
            this.$refs['reporte' + id].hide();
            this.traer_detalle_ventas(idVenta);
        },

        traer_ventas() {
            if(this.usuario.rol != this.admin){
                return false;
            }
            this.filtro = false;
            if (this.desde == '' && this.hasta == '' && this.hora_d =='' && this.hora_h=='') {
                this.filtro = false;
                alert("seleccione un rango de fechas.");
                return false;
            } else {
                this.listarReporteVentas = [];
                this.axios.get('api/reporte_ventas/' + this.desde+' '+this.hora_d + '/' + this.hasta+' '+this.hora_h).then((response) => {
                    if (response.data.estado == 'success') {
                        this.listarReporteVentas = response.data.ventas;
                        this.suma_ventas = response.data.total;
                        this.credito = response.data.deuda;
                        this.vuelto = response.data.vuelto;
                        this.resumen_titulo = response.data.fecha;
                        this.filtro = true;
                        this.efectivo_real = response.data.efectivo_real;
                        this.debito = response.data.debito;
                    }
                    if (response.data.estado == 'failed') {
                        this.filtro = false;
                        alert(response.data.mensaje);
                    }
                })
            }

        },

        traer_detalle_ventas(idVenta) {
            if(this.usuario.rol != this.admin){
                return false;
            }
            this.listarReporteDetalleVentas = [];
            this.axios.get('api/traer_detalle_venta/' + idVenta).then((response) => {
                this.listarReporteDetalleVentas = response.data.detalleVenta;

            })

        },

        limpiar() {
            this.desde = '';
            this.hasta = '';
            this.listarReporteVentas = [];
            this.listarReporteDetalleVentas = [];
            this.suma_ventas = 0;
            this.credito =0;
            this.vuelto = 0;
            this.filtro = false;
        }

    },
    mounted() {
    },
}
