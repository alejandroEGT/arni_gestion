<template>
  <div class="mt-4">
    <!-- <div class="col-12 col-lg-12">
        <b-card class="text-center transparencia mb-4">
          <b-alert show variant="warning">
            <b>NOTA:</b>
            <em>
              Al cambiar algun dato de estos formularios por seguridad la
              sesión se cerrará automaticamente para que efectue un nuevo
              inicio de sesión con los nuevos datos.
            </em>
    </b-alert>-->

    <!-- ALERT SUCCESS-->
    <!-- <div class="col-12 text-center mx-auto my-4">
            <ul>
              <b-alert
                :show="dismissCountDown2"
                variant="success"
                @dismissed="dismissCountDown2=0"
                @dismiss-count-down="countDownChanged2"
              >
                <b>{{correcto2}} {{ dismissCountDown2 }} segundos...</b>
              </b-alert>
            </ul>
    </div>-->
    <!-- ALERT SUCCESS -->
    <!-- </b-card>
    </div>-->

    <div class="col-12 col-lg-12">
      <b-card class="text-center tituloTabla transparencia mb-4">
        <b-card-header class="fondoCategoria mb-4">REPORTE DE VENTAS</b-card-header>

        <div class="row">
          <div class="col-12 col-md-3 col-lg-3 text-left mb-4">
            <b>DESDE:</b>
            <b-form-input type="date" v-model="desde" v-on:keyup.enter="traer_ventas()"></b-form-input>
          </div>

          <div class="col-12 col-md-3 col-lg-3 text-left mb-4">
            <b>HASTA:</b>
            <b-form-input type="date" v-model="hasta" v-on:keyup.enter="traer_ventas()"></b-form-input>
          </div>

          <div class="col-12 col-md-2 col-lg-2 mt-4">
            <b-button block variant="success" @click="traer_ventas()">Filtrar</b-button>
          </div>

          <div class="col-12 col-md-2 col-lg-2 mt-4">
            <b-button block variant="success" @click="limpiar()">Reiniciar</b-button>
          </div>

          <div class="col-12 col-md-2 col-lg-2 mt-4">
            <b-button block variant="success" v-print="printVenta">Imprimir Ventas</b-button>
          </div>
        </div>
          <br>
         <div v-if="filtro">
          <div class="row justify-content-center">
            <div class="col-md-5">
              <table class="table table-bordered">
                <tr>
                  <th style="background:#343a40; color:white">Resumen venta total</th>
                  <td><span class="green">$</span> {{ formatPrice(suma_ventas) }}</td>
                </tr>
              </table>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <div>
                <b-table
                  id="printVenta"
                  show-empty
                  emptyText="Seleccione un rango de fechas."
                  small
                  striped
                  hover
                  bordered
                  stacked="lg"
                  head-variant="dark"
                  :fields="reporteVentasFieldsAdm"
                  :items="listarReporteVentas"
                >
                  <template v-slot:cell(index)="data">{{ data.item.idVenta }}</template>

                  <template v-slot:cell(venta)="data">
                    <span class="green">$</span>
                    {{ formatPrice(data.item.venta_total) }}
                  </template>

                  <template v-slot:cell(fecha)="data">{{data.item.creado}}</template>
                  <template v-slot:cell(creado)="data">{{data.item.nombreUsuarioVenta}}</template>
                  <template v-slot:cell(cliente)="data">{{data.item.nombres+' '+data.item.apellidos}}</template>
                   <template v-slot:cell(tipo_pago)="data"><small>{{ 'Efectivo: '+formatPrice((data.item.pago_efectivo)?data.item.pago_efectivo : 0) }} <br>
                                                       {{ 'Debito: '+formatPrice((data.item.pago_debito)?data.item.pago_debito : 0)}}   </small>       
                   </template>
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
                            :ref="'reporte'+data.item.idVenta"
                          >
                            <template v-slot:modal-title>
                              <h5 class="text-center">Resumen de la venta</h5>
                            </template>
                            <div id="printDetalle">
                              <h5 class="text-center">Detalle de la venta: {{data.item.idVenta}}</h5>
                              <b-table
                                show-empty
                                emptyText="No existen productos aun."
                                small
                                striped
                                hover
                                bordered
                                stacked="lg"
                                head-variant="dark"
                                :fields="reporteDetalleVentaFieldsAdm"
                                :items="listarReporteDetalleVentas"
                              >
                              <template v-slot:cell(imagen)="data"><b-img class="tamanio" thumbnail v-if="data.item.imagen"  :src="data.item.imagen" alt="Image 1"></b-img></template>
                                <template v-slot:cell(nombre)="data">{{ data.item.nombre }}</template>
                                <template v-slot:cell(descripcion)="data">{{ data.item.proDesc }}</template>
                                <template v-slot:cell(categoria)="data">{{ data.item.catDesc }}</template>
                                <template
                                  v-slot:cell(precio)="data"
                                >{{ formatPrice(data.item.precio) }}</template>
                                <template v-slot:cell(cantidad)="data">{{ data.item.cantidadDetalle }}</template>
                                <template v-slot:cell(cliente)="data">{{ data.item.nombres }} {{data.item.apellidos}}</template>
                              </b-table>
                            </div>

                            <div class="row justify-content-center bordeFooter">
                              <div class="col-4">
                                <b-button
                                  class="my-2"
                                  block
                                  pill
                                  variant="success"
                                  v-print="printDetalle"
                                >Imprimir Detalle</b-button>
                              </div>
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
            </div>
          </div>
        </div>
      </b-card>
    </div>
  </div>
</template>

<script src="../reportesVentas/reportesVentas.js"></script>
<style scoped src="../reportesVentas/reportesVentas.css"></style>
