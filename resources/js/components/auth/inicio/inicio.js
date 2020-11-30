import ChartProductos from './Chart.js'
import ChartVentas from './ChartVentas.js'
// import Line from './Line.js'

export default {
  components: {
    ChartProductos,
    ChartVentas,
    // Line
  },
  data() {

    return {
      usuario: this.$auth.user(),
      admin:1,
      productosFields: [
        { key: 'index', label: 'ID', variant: 'dark' },
        { key: 'producto', label: 'Productos' },
        { key: 'cantidad', label: 'Cantidad' },
        { key: 'totalVendido', label: 'Total' },

      ],
      productosItems: [],

      ventasFields: [
        { key: 'index', label: 'ID', variant: 'dark' },
        { key: 'venta', label: 'Total' },
        { key: 'fecha', label: 'Fecha Venta' },
        { key: 'creado', label: 'Venta por' },
        { key: 'detalle', label: '' },

      ],
      ventasItems: [],

      // CABEZERA DE LA TABLA DETALLE VENTA
      detalleVentaFieldsAdm: [
        { key: 'nombre', label: 'Nombre producto' },
        { key: 'descripcion', label: 'Descripcion' },
        { key: 'categoria', label: 'Categoria' },
        { key: 'precio', label: 'Precio' },
        { key: 'cantidad', label: 'Cantidad Vendida' },


      ],
      // LLENAR TABLA DETALLE VENTAS
      listarDetalleVentas: [],

      cantidadCategorias: '0',
      cantidadProductos: '0',
      totalVentas: '0',
      totalUsuarios: '0',

      //GRAFICO
      datacollection: null,
      datacollection2: null,
      datacollection3: null,
      optionsGrafico: {
        legend: {
          position: 'bottom',
          labels: {
            fontSize: 16,
            fontColor: 'black',
            fontStyle: 'bold',
          },
          title: {
            display: true,
            text: 'Custom Chart Title'
          },
        },
      },


    }
  },

  methods: {

    formatPrice(value) {
      let val = (value / 1).toFixed(0).replace('.', ',')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
    },

    url(ruta) {
      this.$router.push({ path: ruta }).catch(error => {
        if (error.name != "NavigationDuplicated") {
          throw error;
        }
      });
    },

    cantidad_categorias() {
      this.axios.get('api/cantidad_categoria').then((response) => {
        this.cantidadCategorias = response.data;
      })
    },

    cantidad_productos() {
      this.axios.get('api/cantidad_productos').then((response) => {
        this.cantidadProductos = response.data;
      })
    },

    total_ventas() {
        if(this.usuario.rol != this.admin){
            return false;
        }
        this.axios.get('api/total_ventas').then((response) => {
            this.totalVentas = response.data;
        })
    },

    ultimas_ventas() {
        console.log(this.usuario.rol +'!='+ this.admin, this.usuario != this.admin)
        if(this.usuario.rol != this.admin){
            return false;
        }
      this.axios.get('api/ultimas_ventas').then((response) => {
        this.ventasItems = response.data.ventas;
      })
    },

    mas_vendidos() {
        if(this.usuario.rol != this.admin){
            return false;
        }
      this.axios.get('api/mas_vendidos').then((response) => {
        this.productosItems = response.data.vendidos;
      })
    },

    total_usuarios() {
        if(this.usuario.rol != this.admin){
            return false;
        }
      this.axios.get('api/allUser').then((response) => {
        this.totalUsuarios = response.data;
      })
    },

    mas_vendidos_grafico() {
        if(this.usuario.rol != this.admin){
            return false;
        }
      this.axios.get('api/mas_vendidos_grafico').then((response) => {
        this.datacollection = response.data;
      })
    },

    ultimas_ventas_grafico() {
        if(this.usuario.rol != this.admin){
            return false;
        }
      this.axios.get('api/ultimas_ventas_grafico').then((response) => {
        this.datacollection2 = response.data;
      })
    },

    menos_vendidos_grafico() {
        if(this.usuario.rol != this.admin){
            return false;
        }
      this.axios.get('api/menos_vendidos_grafico').then((response) => {
        this.datacollection3 = response.data;
      })
    },

    traer_detalle_ventas(idVenta) {
        if(this.usuario.rol != this.admin){
            return false;
        }
      this.listarDetalleVentas = [];
      this.axios.get('api/traer_detalle_venta/' + idVenta).then((response) => {
        this.listarDetalleVentas = response.data.detalleVenta;

      })

    },

    // MODAL EDITAR
    showModalDetalleVenta(id, idVenta) {
      this.$refs['detalleVenta' + id].show();
      this.traer_detalle_ventas(idVenta);
    },
    hideModalDetalleVenta(id, idVenta) {
      this.$refs['detalleVenta' + id].hide();
      this.traer_detalle_ventas(idVenta);
    },

  },

  mounted() {
    this.cantidad_categorias();
    this.cantidad_productos();
    this.total_ventas();
    this.ultimas_ventas();
    this.mas_vendidos();
    this.total_usuarios();
    this.mas_vendidos_grafico();
    this.ultimas_ventas_grafico();
    this.menos_vendidos_grafico();
  },

}
