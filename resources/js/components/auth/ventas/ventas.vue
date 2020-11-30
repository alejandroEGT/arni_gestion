<template>
  <div v-if="usuario.rol==admin">
    <div class="row justify-content-center m-4">
      <div class="col-12 col-md-12">
        <b-card class="tituloTabla text-center transparencia">
          <b-card-header class="fondoProductoAdm mb-4">LISTA DE PRODUCTOS</b-card-header>

          <div class="row">
            <div class="col-12 col-sm-8 col-md-8 col-lg-8 col-xl-8 my-2">
              <b-input-group>
                <b-form-input
                  v-on:keyup="escribiendoProducto"
                  placeholder="Buscar venta..."
                  v-model="buscadorProducto"
                  v-on:keyup.enter="traer_producto()"
                ></b-form-input>
              </b-input-group>
            </div>
            <div class="col-6 col-sm-2 col-md-2 col-lg-2 col-xl-2 my-2 mx-auto">
              <b-button
                :disabled="btn_buscar_producto"
                block
                variant="success"
                @click="traer_producto()"
              >
                <i class="fas fa-search text-light"></i>
              </b-button>
            </div>
            <div class="col-6 col-sm-2 col-md-2 col-lg-2 col-xl-2 my-2 mx-auto">
              <b-button block variant="success" @click="traer_ventas()">
                <i class="fas fa-sync-alt text-light"></i>
              </b-button>
            </div>
            <!-- BUSQUEDA ERRONEA -->
            <div class="col-12">
              <ul>
                <!-- SUCCESS BUSQUETA -->
                <b-alert
                  variant="danger"
                  :show="dismissCountDown4"
                  @dismissed="dismissCountDown4=0"
                  @dismiss-count-down="countDownChanged4"
                >
                  <p>{{errores4}} {{ dismissCountDown4 }} segundos...</p>
                  <b-progress
                    variant="danger"
                    height="4px"
                    :max="dismissSecs4"
                    :value="dismissCountDown4"
                  ></b-progress>
                </b-alert>
              </ul>
            </div>
            <!-- BUSCADOR VACIO -->
            <div class="col-12">
              <b-alert v-model="showAlertBuscar" variant="danger" dismissible>{{errorBuscar}}</b-alert>
            </div>
          </div>

          <div>
            <b-table
              id="app"
              show-empty
              emptyText="No existen ventas aun."
              small
              striped
              hover
              bordered
              stacked="lg"
              head-variant="dark"
              :fields="ventasFieldsAdm"
              :items="listarVentas"
            >
              <template v-slot:cell(index)="data">{{ data.item.idVenta }}</template>

              <template v-slot:cell(venta)="data">
                <span class="green">$</span>
                {{ formatPrice(data.item.venta_total) }}
              </template>

              <template v-slot:cell(fecha)="data">{{data.item.creado}}</template>
              <template v-slot:cell(creado)="data">{{data.item.nombreUsuarioVenta}}</template>

              <template v-slot:cell(detalle)="data">
                <!-- EDITAR PRODUCTOS -->
                <!-- BOTON EDITAR -->
                <div class="row">
                  <div class="col-12">
                    <b-button
                      block
                      id="show-btn"
                      class="my-2"
                      variant="success"
                      @click="showModalDetalleVenta(data.item.idVenta,data.item.idVenta)"
                    >Ver Detalle</b-button>
                  </div>
                </div>
                <div class="col-12 col-xl-12">
                  <!-- MODAL EDITAR PRODUCTOS -->
                  <template>
                    <div>
                      <b-modal
                        hide-footer
                        centered
                        class="modal-header-editar"
                        id="modal-xl"
                        size="xl"
                        :ref="'detalleVenta'+data.item.idVenta"
                      >
                        <template v-slot:modal-title>
                          <h5 class="text-center">Detalle de la venta: {{data.item.idVenta}}</h5>
                        </template>

                        <b-table
                          id="app"
                          show-empty
                          emptyText="Cargando productos..."
                          small
                          striped
                          hover
                          bordered
                          stacked="lg"
                          head-variant="dark"
                          :fields="detalleVentaFieldsAdm"
                          :items="listarDetalleVentas"
                        >
                          <template v-slot:cell(imagen)="data"><b-img class="tamanio" thumbnail v-if="data.item.imagen"  :src="data.item.imagen" alt="Image 1"></b-img></template>
                          <template v-slot:cell(nombre)="data">{{ data.item.nombre }}</template>
                          <template v-slot:cell(descripcion)="data">{{ data.item.proDesc }}</template>
                          <template v-slot:cell(categoria)="data">{{ data.item.catDesc }}</template>
                          <template v-slot:cell(precio)="data">{{ formatPrice(data.item.precio) }}</template>
                          <template v-slot:cell(cantidad)="data">{{ data.item.cantidadDetalle }}</template>
                          <template v-slot:cell(cliente)="data">{{ data.item.nombres }} {{data.item.apellidos}}</template>
                        </b-table>

                        <div class="row justify-content-center bordeFooter">
                          <div class="col-4">
                            <b-button
                              class="my-2"
                              block
                              pill
                              variant="info"
                              @click="hideModalDetalleVenta(data.item.idVenta,data.item.idVenta)"
                            >Volver</b-button>
                          </div>
                        </div>
                      </b-modal>
                    </div>
                  </template>
                </div>
              </template>
            </b-table>
          </div>
        </b-card>
      </div>
    </div>

    <!-- <button @click="download">Download</button> -->
  </div>
</template>

<script src="../ventas/ventas.js"></script>
<style scoped src="../ventas/ventas.css"></style>
