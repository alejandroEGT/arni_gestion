<template>
  <div>
    <b-navbar toggleable="lg" type="dark" variant="dark">
      <a class="navbar-brand" @click="url('index')" style="cursor:pointer;">
        <img :src="listarConf.logo" v-show="logoNull" width="50px" height="30px" />
        {{listarConf.empresa}}
      </a>
      <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

      <b-collapse id="nav-collapse" is-nav>
        <b-navbar-nav>
          <b-nav-item @click="url('index')">
            <i class="fas fa-home"></i> Panel de Control
          </b-nav-item>
          <b-nav-item @click="url('clientes')">
            <i class="fas fa-user-friends"></i> Clientes
          </b-nav-item>
          <b-nav-item @click="url('categorias')">
            <i class="fas fa-indent"></i> Categorias
          </b-nav-item>
          <b-nav-item-dropdown text="Productos">
            <b-dropdown-item @click="url('administrarProducto')">
              <i class="fas fa-edit"></i> Administrar Productos
            </b-dropdown-item>
            <b-dropdown-item @click="url('agregarProducto')">
              <i class="fas fa-plus"></i> Agregar Productos
            </b-dropdown-item>
          </b-nav-item-dropdown>

          <b-nav-item-dropdown text="Ventas">
            <!-- <b-dropdown-item @click="url('ventas')">
              <i class="fas fa-search-dollar"></i> Visualizar Ventas
            </b-dropdown-item> -->
            <b-dropdown-item @click="url('reportesVentas')">
              <i class="fas fa-paste"></i> Reporte de Ventas
            </b-dropdown-item>
          </b-nav-item-dropdown>
          <b-nav-item @click="url('generarVenta')">
            <i class="fas fa-cart-plus"></i> Generar Venta POS
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
    }
  },

  mounted() {
    this.traer_configuraciones();
  }
};
</script>
