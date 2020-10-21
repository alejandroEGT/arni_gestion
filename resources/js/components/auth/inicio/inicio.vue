<template>
  <div>
    <div class="row my-4 mx-4">
      <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <div class="transparencia">
          <b-card no-body align="center" class="my-2">
            <b-row no-gutters>
              <div class="col-md-6 fondoProductos">
                <img src="images/user.png" class="my-4" width="50px" height="50px" />
              </div>
              <div class="col-md-6 text-center">
                <b-card-text class="my-4">
                  <h4>
                    Usuarios
                    <p class="mt-2">
                      <b>{{totalUsuarios}}</b>
                    </p>
                  </h4>
                </b-card-text>
              </div>
            </b-row>
          </b-card>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <div class="transparencia">
          <b-card
            no-body
            align="center"
            class="my-2"
            @click="url('categorias')"
            style="cursor: pointer"
          >
            <b-row no-gutters>
              <div class="col-md-6 fondoProductos">
                <img src="images/list.png" class="my-4" width="50px" height="50px" />
              </div>
              <div class="col-md-6 text-center">
                <b-card-text class="my-4">
                  <h4>
                    Categorias
                    <p class="mt-2">
                      <b>{{cantidadCategorias}}</b>
                    </p>
                  </h4>
                </b-card-text>
              </div>
            </b-row>
          </b-card>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <div class="transparencia">
          <b-card
            no-body
            align="center"
            class="my-2"
            @click="url('administrarProducto')"
            style="cursor: pointer"
          >
            <b-row no-gutters>
              <div class="col-md-6 fondoProductos">
                <img src="images/shopping-cart.png" class="my-4" width="50px" height="50px" />
              </div>
              <div class="col-md-6 text-center">
                <b-card-text class="my-4">
                  <h4>
                    Productos
                    <p class="mt-2">
                      <b>{{cantidadProductos}}</b>
                    </p>
                  </h4>
                </b-card-text>
              </div>
            </b-row>
          </b-card>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <div class="transparencia">
          <b-card
            no-body
            align="center"
            class="my-2"
            @click="url('reportesVentas')"
            style="cursor: pointer"
          >
            <b-row no-gutters>
              <div class="col-md-6 fondoProductos">
                <img src="images/dollar-symbol.png" class="my-4" width="50px" height="50px" />
              </div>
              <div class="col-md-6 text-center">
                <b-card-text class="my-4">
                  <h4>
                    Ventas
                    <p class="mt-2">
                      <b>
                        <span class="green">$</span>
                        {{ formatPrice(totalVentas) }}
                      </b>
                    </p>
                  </h4>
                </b-card-text>
              </div>
            </b-row>
          </b-card>
        </div>
      </div>
    </div>

    <div class="row m-4">
      <div class="col-12 col-md-12 col-lg-6">
        <b-card class="text-center tituloTabla mt-2 mb-4 col-12 transparencia">
          <b-card-header class="fondoProductos mb-4">PRODUCTOS MAS VENDIDOS</b-card-header>
          <div>
            <b-table
              show-empty
              emptyText="No existen productos aun."
              small
              striped
              hover
              bordered
              stacked="lg"
              head-variant="dark"
              :fields="productosFields"
              :items="productosItems"
            >
              <template v-slot:cell(index)="data">{{ data.item.producto_id}}</template>
              <template v-slot:cell(producto)="data">
                <b>{{ data.item.nombre.toUpperCase() }}</b>
              </template>
              <template v-slot:cell(cantidad)="data">{{ formatPrice(data.item.cantidad_total) }}</template>
              <template v-slot:cell(totalVendido)="data">
                <span class="green">$</span>
                {{ formatPrice(data.item.venta_total) }}
              </template>
            </b-table>
          </div>
        </b-card>

        <!--GRAFICO  -->
        <div class="row">
          <div class="col-12 col-lg-6 mb-4">
            <b-card class="text-center tituloTabla transparencia">
              <b-card-header class="fondoCategoria mb-4">TOP 5 PRODUCTOS MAS VENDIDOS</b-card-header>
              <template>
                <div class="small">
                  <ChartProductos :chart-data="datacollection" :options="optionsGrafico"></ChartProductos>
                </div>
              </template>
            </b-card>
          </div>
          <!--GRAFICO  -->

          <!--GRAFICO  -->
          <div class="col-12 col-lg-6 mb-4">
            <b-card class="text-center tituloTabla transparencia">
              <b-card-header class="fondoCategoria mb-4">TOP 5 PRODUCTOS MENOS VENDIDOS</b-card-header>
              <template>
                <div class="small">
                  <ChartProductos :chart-data="datacollection3" :options="optionsGrafico"></ChartProductos>
                </div>
              </template>
            </b-card>
          </div>
        </div>
        <!--GRAFICO  -->
      </div>

      <div class="col-12 col-md-12 col-lg-6">
        <b-card class="text-center tituloTabla mt-2 mb-4 col-12 transparencia">
          <b-card-header class="fondoProductos mb-4">ULTIMAS VENTAS</b-card-header>
          <div>
            <b-table
              show-empty
              emptyText="No existen ventas aun."
              small
              striped
              hover
              bordered
              stacked="lg"
              head-variant="dark"
              :fields="ventasFields"
              :items="ventasItems"
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
                      
                      size="sm"
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
                          <template v-slot:cell(nombre)="data">{{ data.item.nombre }}</template>
                          <template v-slot:cell(descripcion)="data">{{ data.item.proDesc }}</template>
                          <template v-slot:cell(categoria)="data">{{ data.item.catDesc }}</template>
                          <template v-slot:cell(precio)="data">{{ formatPrice(data.item.precio) }}</template>
                          <template v-slot:cell(cantidad)="data">{{ data.item.cantidadDetalle }}</template>
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

        <!--GRAFICO  -->
        <b-card class="text-center tituloTabla transparencia">
          <b-card-header class="fondoCategoria mb-4">TOP 5 ULTIMAS VENTAS</b-card-header>
          <template>
            <div class="smallVentas">
              <ChartVentas :chart-data="datacollection2" :options="optionsGrafico"></ChartVentas>
            </div>
          </template>
        </b-card>
        <!--GRAFICO  -->
      </div>
    </div>
  </div>
</template>

<script src="../inicio/inicio.js"></script>
<style src="../inicio/inicio.css"></style>

