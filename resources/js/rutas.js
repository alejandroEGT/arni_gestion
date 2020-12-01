import Outer from './components/outer.vue';
import HomeComponent from './components/Home.vue';
import NotFound from './components/404.vue';
import Auth from './components/auth/auth.vue';
import IndexComponent from './components/auth/inicio/inicio.vue';
import CategoriasComponent from './components/auth/categorias/categorias.vue';
import AgregarProductoComponent from './components/auth/agregarProducto/agregarProducto.vue';
import AdministrarProductoComponent from './components/auth/administrarProducto/administrarProducto.vue';
import VentasComponent from './components/auth/ventas/ventas.vue';
import generarVentaComponent from './components/auth/generarVenta/generarVenta.vue';
import reportesVentasComponent from './components/auth/reportesVentas/reportesVentas.vue';
import perfilComponent from './components/auth/perfil/perfil.vue';
import configuracionesComponent from './components/auth/configuraciones/configuraciones.vue';
import recuperarPasswordComponent from './components/recuperarPassword.vue';
import resetearPasswordComponent from './components/resetearPassword.vue';
import pagos_pendientes from './components/auth/clientes/pagos_pendientes.vue'



import ClientesComponent from './components/auth/clientes/clientes.vue';

const routes = [
  {
    path: '/',
    component: Outer,
    name: 'Admin',
    redirect: 'home',
    iconCls: 'el-icon-message',
    meta: { auth: false },

    children: [
      {
        name: 'home',
        path: '/',
        component: HomeComponent
      },

      {
        name: 'recuperarPassword',
        path: '/recuperarPassword',
        component: recuperarPasswordComponent
      },

      {
        name: 'resetearPassword',
        path: '/resetearPassword/:token',
        component: resetearPasswordComponent
      },

      // {
      //   path: '/404',

      //   name: '',
      //   redirect: { path: '/home' },
      //   hidden: true
      // },

      // {
      //   path: '*',
      //   hidden: true,
      //   redirect: { name: 'home' }
      // }

    ]
  },

  {
    path: '/',
    component: Auth,
    name: 'Auth',
    redirect: 'index',
    iconCls: 'el-icon-message',
    meta: { auth: true },

    children: [
      {
        name: 'index',
        path: '/index',
        component: IndexComponent
      },
      {
        name: 'categorias',
        path: '/categorias',
        component: CategoriasComponent
      },
      {
        name: 'clientes',
        path: '/clientes',
        component: ClientesComponent
      },
      {
        name: 'pagos_pendientes',
        path: '/pagos_pendientes',
        component: pagos_pendientes
      },
      {
        name: 'agregarProducto',
        path: '/agregarProducto',
        component: AgregarProductoComponent
      },
      {
        name: 'administrarProducto',
        path: '/administrarProducto',
        component: AdministrarProductoComponent
      },
      {
        name: 'ventas',
        path: '/ventas',
        component: VentasComponent
      },
      {
        name: 'generarVenta',
        path: '/generarVenta',
        component: generarVentaComponent
      },
      {
        name: 'reportesVentas',
        path: '/reportesVentas',
        component: reportesVentasComponent
      },
      {
        name: 'perfil',
        path: '/perfil',
        component: perfilComponent
      },
      {
        name: 'configuraciones',
        path: '/configuraciones',
        component: configuracionesComponent
      },
      // {
      //   path: '/404',
      //   // component: NotFoundAuth,
      //   name: '',
      //   redirect: { path: '/index' },
      //   hidden: true
      // },

      // {
      //   path: '*',
      //   hidden: true,
      //   redirect: { path: '/index' }
      // }

    ]
  },

  {
    path: '/404',
    component: NotFound,
    name: '',
    hidden: true
  },

  {
    path: '*',
    hidden: true,
    redirect: { path: '/' }
}

];

export default routes;
