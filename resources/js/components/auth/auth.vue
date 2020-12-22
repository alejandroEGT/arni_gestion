<template>
  <div>
    <b-navbar toggleable="lg" type="dark"  style="background:#34495E;">
      <a class="navbar-brand" @click="url('index')" style="cursor:pointer;">
        <img :src="listarConf.logo" v-show="logoNull" width="50px" height="30px" />
        {{listarConf.empresa}}
      </a>
      <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

      <b-collapse id="nav-collapse" is-nav>
        <b-navbar-nav>
          <b-nav-item v-if="usuario.rol==admin" @click="url('index')">
            <i class="fas fa-home"></i> Panel de Control
          </b-nav-item>

          <b-nav-item-dropdown  text="Clientes">
            <b-dropdown-item @click="url('clientes')">
              <i class="fas fa-search-dollar"></i> Clientes
            </b-dropdown-item>
             <b-dropdown-item @click="url('empresas')">
              <i class="fas fa-search-dollar"></i> Empresas
            </b-dropdown-item>
            <b-dropdown-item @click="url('pagos_pendientes')">
              <i class="fas fa-paste"></i> Pagos pendientes
            </b-dropdown-item>
          </b-nav-item-dropdown>
          <b-nav-item @click="url('categorias')">
            <i class="fas fa-indent"></i> Categorias
          </b-nav-item>
          <b-nav-item-dropdown text="Productos">
            <b-dropdown-item @click="url('administrarProducto')">
              <i class="fas fa-edit"></i> Administrar Productos
            </b-dropdown-item>
            <b-dropdown-item v-if="usuario.rol==admin" @click="url('agregarProducto')">
              <i class="fas fa-plus"></i> Agregar Productos
            </b-dropdown-item>
          </b-nav-item-dropdown>

          <b-nav-item-dropdown v-if="usuario.rol==admin" text="Ventas">
            <b-dropdown-item @click="url('ventas')">
              <i class="fas fa-search-dollar"></i> Visualizar Ventas
            </b-dropdown-item>
            <b-dropdown-item @click="url('reportesVentas')">
              <i class="fas fa-paste"></i> Reporte de Ventas
            </b-dropdown-item>
          </b-nav-item-dropdown>
          <b-nav-item-dropdown  text="Generar venta">

                <b-dropdown-item @click="url('generarVenta')">
                <i class="fas fa-cart-plus"></i> Voucher
                </b-dropdown-item>

                <b-dropdown-item @click="url('facturacion_electronica')">
                <i class="fas fa-cart-plus"></i> Facturación electrónica
                </b-dropdown-item>
          </b-nav-item-dropdown>

          <b-nav-item >
            <button type="button" @click="cambiar_tipo_precio(usuario.tipo_precio)" class="btn btn-secondary btn-sm">Usando precio {{ usuario.tipo_precio }}  de venta</button>
          </b-nav-item>
        </b-navbar-nav>

        <!-- Right aligned nav items -->
        <b-navbar-nav class="ml-auto">
          <b-nav-item-dropdown right>
            <!-- Using 'button-content' slot -->
            <template v-slot:button-content>
              <em>{{usuario.name}}</em>
            </template>
            <b-dropdown-item @click="url('perfil')">
              <i class="fas fa-user"></i> Perfil
            </b-dropdown-item>
            <b-dropdown-item @click="url('configuraciones')">
              <i class="fas fa-cog"></i> Configuracion
            </b-dropdown-item>
            <b-dropdown-item @click="logout()">
              <i class="fas fa-sign-in-alt"></i> Cerrar Sesion
            </b-dropdown-item>
          </b-nav-item-dropdown>
        </b-navbar-nav>
      </b-collapse>
    </b-navbar>

    <transition name>
      <router-view></router-view>
    </transition>
  </div>
</template>

<script>
export default {
  data() {
    return {
      usuario: this.$auth.user(),
      admin:1,
      listarConf: [],
      logoNull: false
    };
  },

  methods: {
    url(ruta) {
      this.$router.push({ path: ruta }).catch(error => {
        if (error.name != "NavigationDuplicated") {
          throw error;
        }
      });
    },

    logout: function() {
      this.$auth.logout({
        makeRequest: true,
        redirect: "/"
      });
    },
    traer_configuraciones() {
      this.axios.get("api/traer_configuraciones").then(response => {
        if (response.data.estado == "success") {
          this.logoNull = true;
          this.listarConf = response.data.configuraciones;
        }
      });
    },

    cambiar_tipo_precio(tipo_precio){
        var numero_send = 0;
        if(tipo_precio == 1){
          numero_send = 2;
        }
        if(tipo_precio == 2){
          numero_send = 1;
        }
        this.axios.get("api/cambiar_tipo_precio/"+numero_send).then((res)=>{

          if(res.data.estado == 'success'){
            alert("Usando ahora precio "+numero_send);
            this.usuario = res.data.user;
          }

        });

    }
  },

  mounted() {
    this.traer_configuraciones();
  }
};
</script>
