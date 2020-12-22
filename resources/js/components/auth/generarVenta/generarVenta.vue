<template>
  <div>

    <button v-if="false" @click="generar_un_xml">Genera un xml factura</button>

    <button
    v-if="false"
      type="button"
      class="btn btn-primary"
      data-toggle="modal"
      data-target="#modalFactura"
    >
      Ver xml como una factura real
    </button>

    <div v-if="false" class="card">
      aqui se veria la img
      <img width="700" height="400" :src="ted" alt="">
    </div>

    <!-- Modal factura -->
    <div
      v-if="false"
      class="modal fade"
      id="modalFactura"
      tabindex="-1"
      role="dialog"
      aria-labelledby="exampleModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Modal Factura</h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div id="pdfFactura">
              <!-- AQUI EL DISEÑO DE LA FACTURA ELECTRONICA -->
              <div class="factura">
                <center style="font-size: 2rem; border:3px solid red;color:red;font-family:sans-serif;">
                  <b><pre style="color:red">18.8.5.652-0</pre></b>
                  <b><pre style="color:red">FACTURA ELECTRONICA</pre></b>
                  <b><pre style="color:red">Nº 001</pre></b>
                </center>
                <br />
                <center style="font-size: 2rem;font-family:sans-serif;">
                  <pre> S.I.I SANTIAGO, RM</pre>
                </center>
                <br />

                <!-- DATOS DEL EMISOR  -->
                <section
                  class="datos_emisor"
                  style="
                    font-family: sans-serif;
                    font-size: 1.2rem;
                    width: 100%;
                    white-space: pre-wrap;
                    white-space: -moz-pre-wrap;
                    white-space: -pre-wrap;
                    white-space: -o-pre-wrap;
                    word-wrap: break-word;
                  "
                >
                  <pre><b>Razón social del emisor</b></pre>
                  <pre>Venta de productos de alimentacio1n, limpieza</pre>
                  <br />

                  Emisión&nbsp; : 29/12/2020 <br />
                  Señor(a)&nbsp;: Juan Luis Guerra Echeñiquie <br />
                  Rut&nbsp;: 77.106.553-8 <br />
                  Giro&nbsp;: Venta <br />

                  <table style="width:100%; padding-right:2px;">
                    <tr>
                      <td
                        style="
                          border-bottom: 1px solid black;
                          border-top: 1px solid black;
                        "
                      >
                        Item
                      </td>
                      <td
                        style="
                          border-bottom: 1px solid black;
                          border-top: 1px solid black;
                        "
                      >
                        P.unitario
                      </td>
                      <td
                        style="
                          border-bottom: 1px solid black;
                          border-top: 1px solid black;
                        "
                      >
                        Cantidad
                      </td>
                      <td
                        style="
                          border-bottom: 1px solid black;
                          border-top: 1px solid black;
                        "
                      >
                        Total item
                      </td>
                    </tr>

                    <tr>
                      <td>Cerveza pack 6 u</td>
                      <td>3000</td>
                      <td>2</td>
                      <td>6000</td>
                    </tr>

                    <tr>
                      <td>Vino tinto gato EXPORT</td>
                      <td>3.000</td>
                      <td>2</td>
                      <td>6.000</td>
                    </tr>

                    <tr>
                      <td
                        class="fintabla"
                        style="
                          border-bottom: 1px solid black;
                          border-top: 1px solid black;
                        "
                        colspan="3"
                      >
                        <div style="text-align: right">Total&nbsp;:</div>
                      </td>
                      <td
                        class="fintabla"
                        style="
                          border-bottom: 1px solid black;
                          border-top: 1px solid black;
                        "
                      >
                        9.000
                      </td>
                    </tr>
                  </table>

                  <!-- <div style="text-align:right">
        Total: 10.000
      </div> -->
                </section>
              </div>

              <!-- AQUI SE GENERA EL TIMBRE ELECTRONICO -------------------------------------------------------->
              <div style="text-align: center"><br>
                <!-- insert your custom barcode setting your data in the GET parameter "data" -->
                <img
                  width="90%"
                  height="120%"
                  alt="Barcode Generator TEC-IT"
                  :src="'https://barcode.tec-it.com/barcode.ashx?data='+ted+'&code=PDF417&multiplebarcodes=false&translate-esc=false&unit=Fit&dpi=96&imagetype=Gif&rotation=0&color=%23000000&bgcolor=%23ffffff&codepage=Default&qunit=Mm&quiet=0'"
                />
              </div>
              <div style="font-size: 1.2rem; font-family: sans-serif">
                <!-- back-linking to www.tec-it.com is required -->

                  <!-- logos are optional -->
                  <center>
                    Timbre electronico SII <br>
                    Verifique en www.sii.cl
                  </center>

              </div>
              <!-- AQUI SE GENERA EL TIMBRE ELECTRONICO -------------------------------------------------------------->

              <!-- FIN DEL DISEÑO DE LA FACTURACION ELECTRONICA -->
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
            >
              Close
            </button>
            <!-- <button @click="printDiv('pdfFactura')"></button> -->
            <button
              type="button"
              class="btn btn-primary"
              onclick="printJS({
          printable: 'pdfFactura',
          type:'html',
          style: 'pre{font-family:sans-serif;width: 100%;white-space: pre-wrap;white-space: -moz-pre-wrap;white-space: -pre-wrap;white-space: -o-pre-wrap;word-wrap: break-word;}'
         })"
            >
              Imprimir
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="row my-4 mx-1">
      <div class="col-12">
           <h4>Vóucher de venta</h4>
        <b-card class="text-center transparencia">
          <div class="row">
            <!-- buscar por codigo -->
            <div class="col-12 col-md-6 col-lg-8">

              <b-card class="largoCard">
                <div class="row">
                  <div class="col-12">
                    <div class="row">
                      <div class="col-md-12">
                        <input
                          placeholder="Buscar por nombre o descripcion de producto..."
                          class="form-control form-control-sm"
                          v-model="buscando_txt"
                          type="text"
                          v-on:keyup="buscando_personalizado"
                        />

                        <div
                          v-if="view_buscando"
                          style="
                            border: 1px solid #bfc9ca;
                            border-radius: 4px;
                            height: 200px;
                            overflow: scroll;
                          "
                        >
                          <table class="table">
                            <tr
                              v-for="data in lista_buscando"
                              :key="data.id"
                              class="row"
                            >
                              <td>
                                <b-img
                                 @click="getData(data.sku)"
                                  class="tamanio"
                                  thumbnail
                                  v-if="data.imagen"
                                  :src="data.imagen"
                                  alt="Image 1"
                                ></b-img>
                              </td>
                              <td>
                                <button
                                  @click="getData(data.sku)"
                                  type="button"
                                  class="btn btn-link"
                                >
                                  {{ data.nombre }}
                                </button>
                              </td>
                            </tr>
                          </table>
                        </div>
                      </div>
                    </div>
                    <br />
                    <b-input-group>
                      <b-form-input
                        ref="inputBuscar"
                        id="inputBuscar"
                        v-on:keyup="escribiendoProducto"
                        size="sm"
                        placeholder="Escanee el producto o ingrese el código de barras SKU"
                        v-model="buscadorProducto"
                        v-on:keyup.enter="traer_producto()"
                      ></b-form-input>
                      <b-input-group-append>
                        <b-button
                          id="btnBuscar"
                          :disabled="btn_buscar_producto"
                          block
                          variant="success"
                          @click="traer_producto()"
                          size="sm"
                          >Buscar</b-button
                        >
                      </b-input-group-append>
                    </b-input-group>
                  </div>

                  <div class="col-12 my-4">
                    <div>
                      <b-table
                        table
                        hove12
                        hovexl-r
                        bordered
                        small
                        size="sm"
                        :fields="carroFieldsAdm"
                        :items="arregloCarro"
                        sticky-header="400px"
                        head-variant="dark"
                      >
                        <template v-slot:cell(sku)="data">{{
                          data.item.sku
                        }}</template>
                        <template v-slot:cell(cantidad)="data">
                          <input
                            name="input_cantidad"
                            @input="
                              ingresar_cantidad_carro(
                                data.index,
                                $event.target.value
                              )
                            "
                            @click="
                              ingresar_cantidad_carro(
                                data.index,
                                $event.target.value
                              )
                            "
                            class="form-control form-control-sm"
                            :value="data.item.cantidad_ls"
                          />
                        </template>
                        <template v-slot:cell(producto)="data">
                          <b>{{ data.item.nombre }}</b>
                          <br />
                          <em
                            >{{ formatPrice(data.item.cantidad) }} unidades
                            disponibles</em
                          >
                        </template>
                        <template v-slot:cell(precioProd)="data"
                          >$ {{ formatPrice(data.item.precio) }}</template
                        >
                        <template v-slot:cell(subtotal)="data"
                          >$
                          {{
                            formatPrice(
                              data.item.precio * data.item.cantidad_ls
                            )
                          }}</template
                        >
                        <template v-slot:cell(opc)="data">
                          <div class="col-12 col-xl-12">
                            <b-button
                              size="sm"
                              pill
                              variant="danger"
                              @click="eliminarItem(data.index)"
                              >x</b-button
                            >
                          </div>
                        </template>
                      </b-table>
                    </div>
                  </div>
                </div>

                <template v-slot:footer>
                  <!-- Total Temporal -->
                  <div class="row justify-content-end">
                    <div class="col-4">
                      <b-button
                        size="sm"
                        pill
                        variant="danger"
                        id="limpiarTodo"
                        @click="limpiarCarro()"
                        >Quitar todo</b-button
                      >
                    </div>

                    <div class="col-4">
                      <label>
                        Total:
                        <b>$ {{ formatPrice(total) }}</b>
                      </label>
                    </div>
                  </div>
                  <!-- Total Temporal -->
                </template>
              </b-card>
            </div>
            <!-- buscar por codigo -->




            <!-- tipo de venta -->
            <div class="col-12 col-md-4 col-lg-4 text-right">
              <b-card>
                <b-form-group id="cliente" label="Selecione Cliente:">
                  <div class="row">
                    <div class="col-12">
                      <multiselect
                        v-model="cliente_id"
                        :options="listar_clientes"
                        :custom-label="buscadorClientes"
                        open-direction="bottom"
                        placeholder="Seleccione"
                        select-label
                        selectedLabel="Selecionado"
                        deselectLabel="Quitar"
                      >
                        <span slot="noResult"
                          >No se encontraron resultados.</span
                        >
                      </multiselect>
                    </div>
                  </div>
                </b-form-group>

                <div>
                  <b-form-group label="Forma de pago">
                    <b-form-checkbox-group
                      v-model="formaPago"
                      :options="formaPagoOpcion"
                      name="buttons-1"
                      button-variant="outline-success"
                      buttons
                      size="sm"
                    ></b-form-checkbox-group>
                  </b-form-group>

                </div>

                <div
                  v-if="
                    formaPago == '1' || formaPago == '1,2' || formaPago == '2,1'
                  "
                >
                  <b-form-group label="Monto en CLP">
                    <b-input-group>
                      <b-form-input
                        size="sm"
                        type="number"
                        placeholder="Ingrese el monto que cancela el cliente"
                        v-model="montoEfectivo"
                        >{{ formatPrice() }}</b-form-input
                      >
                      <b-input-group-append>
                        <b-button size="sm" text="Button" disabled
                          >Efectivo</b-button
                        >
                      </b-input-group-append>
                    </b-input-group>
                  </b-form-group>
                </div>

                <div
                  v-if="
                    formaPago == '2' || formaPago == '1,2' || formaPago == '2,1'
                  "
                >
                  <b-form-group label="Monto en CLP">
                    <b-input-group>
                      <b-form-input
                        size="sm"
                        type="number"
                        placeholder="Ingrese el monto que cancela el cliente"
                        v-model="montoDebito"
                      ></b-form-input>
                      <b-input-group-append>
                        <b-button size="sm" text="Button" disabled
                          >T.Debito</b-button
                        >
                      </b-input-group-append>
                    </b-input-group>
                  </b-form-group>
                </div>

                <div v-if="formaPago == '3'">
                  <b-form-group label="Monto en CLP">
                    <b-input-group>
                      <b-form-input
                        size="sm"
                        type="number"
                        placeholder="Ingrese el monto que cancela el cliente"
                        v-model="montoCredito"
                      ></b-form-input>
                      <b-input-group-append>
                        <b-button size="sm" text="Button" disabled
                          >T.Credito</b-button
                        >
                      </b-input-group-append>
                    </b-input-group>
                  </b-form-group>
                </div>

                <div>
                  <b-form-group label="Seleccione Entrega">
                    <b-form-radio-group
                      id="btn-radios-1"
                      v-model="entrega"
                      :options="entregaOpcion"
                      button-variant="outline-success"
                      buttons
                      name="radios-btn-default"
                      size="sm"
                    ></b-form-radio-group>
                  </b-form-group>


                  <hr>

                  <label >

                       <input v-model="chk_credito" @click="monto_credito = 0;" type="checkbox"> Credito / Deuda (Pago pendiente)
                 </label>

                  <div v-if="chk_credito">
                    <textarea v-model="detalle_credito" class="form-control" style="resize: none;" placeholder="Detalle de la deuda.." name="" id="" cols="30" rows="2"></textarea>
                    <br>
                    <input v-model="monto_credito" class="form-control form-control-sm" type="numeric" placeholder="Monto de la deuda..">
                  </div>

                  <hr>
                </div>

                <template v-slot:footer>
                  <!-- detalle venta -->
                  <label>
                    <b>Detalle de Venta</b>
                  </label>
                  <div class="row">
                    <div class="col-8">
                      <label>Total a Pagar</label>
                    </div>
                    <div class="col-4">
                      <label>$ {{ formatPrice(total) }}</label>
                    </div>

                    <div class="col-8">
                      <label>Cliente Paga</label>
                    </div>
                    <div class="col-4">
                      <div v-if="formaPago == ''">
                        <label>$ 0</label>
                      </div>
                      <div v-if="formaPago == '1'">
                        <label>$ {{ formatPrice(Number(montoEfectivo) + Number(monto_credito) ) }}</label>
                      </div>
                      <div v-if="formaPago == '2'">
                        <label>$ {{ formatPrice(Number(montoDebito) + Number(monto_credito)) }}</label>
                      </div>

                      <div v-if="formaPago == '1,2'">
                        <label
                          >$
                          {{
                            formatPrice(
                              Number(montoEfectivo) + Number(montoDebito) + Number(monto_credito)
                            )
                          }}</label
                        >
                      </div>
                      <div v-if="formaPago == '2,1'">
                        <label
                          >$
                          {{
                            formatPrice(
                              Number(montoEfectivo) + Number(montoDebito) + Number(monto_credito)
                            )
                          }}</label
                        >
                      </div>
                      <div v-if="formaPago == '3'">
                        <label>$ {{ formatPrice(montoCredito) }}</label>
                      </div>

                      <!-- <div v-if="chk_credito">
                          <label>$ {{ formatPrice(
                            Number(montoEfectivo) + Number(montoDebito) + Number(monto_credito)
                            ) }}</label>
                      </div> -->

                      <!-- <div v-if="formaPago == '1,2' || formaPago =='2,1'">
                        <label>({{montoEfectivo}} + {{montoDebito}})</label>
                      </div>-->
                    </div>

                    <div class="col-8">
                      <label>Vuelto</label>
                    </div>
                    <div class="col-4">
                      <div v-if="formaPago == ''">
                        <label>$ 0</label>
                      </div>
                      <div v-if="formaPago == '1'">
                        <label

                          >$
                          {{
                            formatPrice(Number(montoEfectivo) - Number(total))
                          }}</label> <label style="color:red" v-if="Number(montoEfectivo) - Number(total) < 0"><i class="fas fa-arrow-up"></i> deuda de cliente</label>
                      </div>
                      <div v-if="formaPago == '2'">
                        <label
                          >$
                          {{
                            formatPrice(Number(montoDebito) - Number(total))
                          }}</label>
                          <label style="color:red" v-if="(Number(montoDebito) - Number(total)) < 0">
                            <i class="fas fa-arrow-up"></i> deuda de cliente
                          </label>
                      </div>
                      <div v-if="formaPago == '1,2'">
                        <label
                          >$
                          {{
                            formatPrice(
                              Number(montoEfectivo) +
                                Number(montoDebito) -
                                Number(total)
                            )
                          }}</label> <label style="color:red" v-if="(Number(montoEfectivo)+Number(montoDebito)-Number(total)) < 0">
                            <i class="fas fa-arrow-up"></i> deuda de cliente
                          </label>
                      </div>
                      <div v-if="formaPago == '2,1'">
                        <label
                          >$
                          {{
                            formatPrice(
                              Number(montoEfectivo) +
                                Number(montoDebito) -
                                Number(total)
                            )
                          }}</label>
                          <label style="color:red" v-if="(Number(montoEfectivo)+Number(montoDebito)-Number(total)) < 0">
                            <i class="fas fa-arrow-up"></i> deuda de cliente
                          </label>
                      </div>
                      <!-- <div v-if="formaPago == '3'">
                        <label>$ {{formatPrice(montoCredito - total)}}</label>
                      </div> -->
                    </div>
                  </div>
                  <!-- detalle venta -->

                  <!-- Comprobante -->
                  <div class="row justify-content-end">
                    <div class="col-12">
                      <!-- BOTON VENTAS -->
                      <div>
                        <b-button
                         :disabled="confirm_compra"
                          pill
                          block
                          size="sm"
                          id="show-btn"
                          class="my-2"
                          variant="success"
                          @click="registrar_venta()"
                          >Confirmar Compra</b-button
                        >
                        <!-- @click="showModal();" -->

                        <button :disabled="traer_ul_venta" @click="abrir_ultima_venta('comprobante',local_storage_venta)" v-if="local_storage_venta !=''" class="btn btn-link">Traer mi ultima venta (ID: {{local_storage_venta}})</button>


                        <b-modal
                            no-close-on-esc
                            no-close-on-backdrop
                            class="modal-header-ventas"
                            id="modal-md"
                            size="md"
                            :ref="'comprobante'"
                            hide-footer
                            centered
                          >
                            <div>

                                <!-- VAUCHER PARA MOSTRAR -->
                                <div
                                        class="ticket"
                                        id="printVenta"
                                        style="
                                            font-size: 12px;
                                            font-family: 'Times New Roman';
                                        "
                                        >
                                        <center>
                                            <img
                                            :src="listarConf.logo"

                                            width="177px"
                                            height="86px"
                                            />
                                        </center>
                                        <center>
                                            <p>
                                            TICKET DE VENTA
                                            <br />
                                            {{ listarConf.empresa }}
                                            <br />
                                            {{ listarConf.direccion }}
                                            </p>
                                        </center>
                                        <table align="center">
                                            <thead>
                                            <!--Fecha Emisión-->
                                            <tr>
                                                <th colspan="4">
                                                Fecha: {{ ticketPrint.fecha }}
                                                </th>
                                            </tr>
                                            <tr>
                                                <th colspan="4">
                                                Comprobante de Venta Nº {{ ticketPrint.id }}
                                                </th>
                                            </tr>

                                            <tr
                                                style="
                                                border-top: 1px solid black;
                                                border-collapse: collapse;
                                                "
                                            >
                                                <th
                                                style="
                                                    border-top: 1px solid black;
                                                    border-collapse: collapse;
                                                "
                                                >
                                                PRODUCTO
                                                </th>
                                                <th
                                                style="
                                                    border-top: 1px solid black;
                                                    border-collapse: collapse;
                                                "
                                                >
                                                CANT
                                                </th>
                                                <th
                                                style="
                                                    border-top: 1px solid black;
                                                    border-collapse: collapse;
                                                "
                                                >
                                                PRECIO
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <!--Producto-->
                                            <tr
                                                v-for="t in ticketPrintDetalle"
                                                :key="t.id"
                                            >
                                                <td>
                                                {{ t.nombre }} ($
                                                {{ formatPrice(t.precio) }} C/U)
                                                </td>
                                                <td>{{ t.cantidad }}</td>
                                                <td class="text-right">
                                                {{
                                                    formatPrice(
                                                    t.precio * t.cantidad
                                                    )
                                                }}
                                                </td>
                                            </tr>
                                            <!--Producto-->
                                            <br />
                                            <!--Totales-->
                                            <tr>
                                                <td>
                                                <b>Total:</b>
                                                $ {{ formatPrice(ticketPrint.venta_total) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                <div>
                                                    <label>
                                                    <b>Vuelto:</b>
                                                    $ {{ formatPrice(ticketPrint.vuelto) }} <label v-if="ticketPrint.vuelto < 0" for=""> Deuda de cliente</label>
                                                    </label>
                                                </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <br />
                                        <center>
                                            <p class="centrado">
                                            <b>Cliente: </b>{{ ticketPrint.cliente }}
                                            </p>
                                            <p class="centrado">
                                            ¡GRACIAS POR SU COMPRA!
                                            </p>
                                            <p>NEO-GESTION</p>
                                            <p>.................</p>
                                        </center>
                                        </div>
                                        <!-- MODAL VENTAS  -->

                                    <div class="row justify-content-center bordeFooter">
                                        <div class="col-4">
                                        <b-button
                                            class="my-2"
                                            block
                                            pill
                                            variant="info"
                                            onclick="printJS({
                                                    printable: 'printVenta',
                                                    type:'html', })"

                                            >imprimir ticket</b-button>
                                            <!-- @click="hideModal()" -->
                                        </div>
                                    </div>
                                <!-- <comprobantes
                                    :listarConf="listarConf"
                                    :ticketPrint="ticketPrint"
                                    :ticketPrintDetalle="ticketPrintDetalle"
                                ></comprobantes> -->
                            </div>
                        </b-modal>
                      </div>
                      <!-- MODAL VENTAS  -->
                      <template>
                        <div>
                          <b-modal
                            no-close-on-esc
                            no-close-on-backdrop
                            class="modal-header-ventas"
                            id="modal-md"
                            size="md"
                            :ref="'ventasModal'"
                            hide-footer
                            centered
                          >
                            <section>
                              <div
                                class="ticket"
                                id="printVenta"
                                style="
                                  font-size: 12px;
                                  font-family: 'Times New Roman';
                                "
                              >
                                <center>
                                  <img
                                    :src="listarConf.logo"
                                    v-show="logoNull"
                                    width="177px"
                                    height="86px"
                                  />
                                </center>
                                <center>
                                  <p>
                                    TICKET DE VENTA
                                    <br />
                                    {{ listarConf.empresa }}
                                    <br />
                                    {{ listarConf.direccion }}
                                  </p>
                                </center>
                                <table align="center">
                                  <thead>
                                    <!--Fecha Emisión-->
                                    <tr v-for="t in ticketPrint" :key="t.id">
                                      <th colspan="4">
                                        Fecha: {{ t.fechaVenta }}
                                      </th>
                                    </tr>
                                    <tr v-for="t in ticketPrint" :key="t.id">
                                      <th colspan="4">
                                        Comprobante de Venta Nº {{ t.idVenta }}
                                      </th>
                                    </tr>

                                    <tr
                                      style="
                                        border-top: 1px solid black;
                                        border-collapse: collapse;
                                      "
                                    >
                                      <th
                                        style="
                                          border-top: 1px solid black;
                                          border-collapse: collapse;
                                        "
                                      >
                                        PRODUCTO
                                      </th>
                                      <th
                                        style="
                                          border-top: 1px solid black;
                                          border-collapse: collapse;
                                        "
                                      >
                                        CANT
                                      </th>
                                      <th
                                        style="
                                          border-top: 1px solid black;
                                          border-collapse: collapse;
                                        "
                                      >
                                        PRECIO
                                      </th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <!--Producto-->
                                    <tr
                                      v-for="t in ticketPrintDetalle"
                                      :key="t.id"
                                    >
                                      <td>
                                        {{ t.nombre }} ($
                                        {{ formatPrice(t.precio) }} C/U)
                                      </td>
                                      <td>{{ t.cantidadDetalle }}</td>
                                      <td class="text-right">
                                        {{
                                          formatPrice(
                                            t.precio * t.cantidadDetalle
                                          )
                                        }}
                                      </td>
                                    </tr>
                                    <!--Producto-->
                                    <br />
                                    <!--Totales-->
                                    <tr v-for="t in ticketPrint" :key="t.id">
                                      <td>
                                        <b>Total:</b>
                                        $ {{ formatPrice(t.totalVenta) }}
                                      </td>
                                    </tr>
                                    <tr>
                                      <td>
                                        <div>
                                          <label>
                                            <b>Vuelto:</b>
                                            $ {{ formatPrice(get_vuelto) }} <label v-if="get_vuelto < 0" for=""> Deuda de cliente</label>
                                          </label>
                                        </div>
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>
                                <br />
                                <center>
                                  <p class="centrado">
                                    <b>Cliente: </b>{{ cliente }}
                                  </p>
                                  <p class="centrado">
                                    ¡GRACIAS POR SU COMPRA!
                                  </p>
                                  <p>NEO-GESTION</p>
                                  <p>.................</p>
                                </center>
                              </div>
                              <!-- MODAL VENTAS  -->
                            </section>
                            <div class="row justify-content-center bordeFooter">
                              <div class="col-4">
                                <b-button
                                  class="my-2"
                                  block
                                  pill
                                  variant="info"
                                  onclick="printJS({
                                            printable: 'printVenta',
                                            type:'html', })"

                                  >imprimir ticket</b-button>
                                  <!-- @click="hideModal()" -->
                              </div>
                            </div>
                          </b-modal>
                        </div>
                      </template>
                    </div>
                  </div>
                  <!-- comprobante -->
                </template>
              </b-card>
            </div>
            <!--tipo de venta -->
          </div>

          <!-- SUCCESS VENTA -->
          <div class="col-12 mt-4">
            <ul>
              <b-alert
                variant="success"
                :show="dismissCountDown3"
                @dismissed="dismissCountDown3 = 0"
                @dismiss-count-down="countDownChanged3"
              >
                <p>{{ correcto3 }} {{ dismissCountDown3 }} segundos...</p>
                <b-progress
                  variant="success"
                  height="4px"
                  :max="dismissSecs3"
                  :value="dismissCountDown3"
                ></b-progress>
              </b-alert>
            </ul>
          </div>

          <!-- BUSQUEDA ERRONEA -->
          <div class="col-12">
            <ul>
              <b-alert
                variant="danger"
                :show="dismissCountDown4"
                @dismissed="dismissCountDown4 = 0"
                @dismiss-count-down="countDownChanged4"
              >
                <p>{{ errores4 }} {{ dismissCountDown4 }} segundos...</p>
                <b-progress
                  variant="danger"
                  height="4px"
                  :max="dismissSecs4"
                  :value="dismissCountDown4"
                ></b-progress>
              </b-alert>
            </ul>
          </div>

          <!-- CAMPOS VACIOS O STOCK MAYOR -->
          <div class="col-12">
            <ul>
              <b-alert
                variant="warning"
                :show="dismissCountDown6"
                @dismissed="dismissCountDown6 = 0"
                @dismiss-count-down="countDownChanged6"
              >
                <p>{{ errores6 }} {{ dismissCountDown6 }} segundos...</p>
                <b-progress
                  variant="warning"
                  height="4px"
                  :max="dismissSecs6"
                  :value="dismissCountDown6"
                ></b-progress>
              </b-alert>
            </ul>
          </div>

          <!-- PRODUCTO REPETIDO -->
          <div class="col-12">
            <ul>
              <b-alert v-if="false"
                variant="warning"
                :show="dismissCountDown5"
                @dismissed="dismissCountDown5 = 0"
                @dismiss-count-down="countDownChanged5"
              >
                <p>
                  El producto ya fue agregado al carro, modifique la cantidad en
                  la tabla. {{ dismissCountDown5 }} segundos...
                </p>
                <b-progress
                  variant="warning"
                  height="4px"
                  :max="dismissSecs5"
                  :value="dismissCountDown5"
                ></b-progress>
              </b-alert>
            </ul>
          </div>
        </b-card>
      </div>
    </div>
  </div>
</template>


<script src="../generarVenta/generarVenta.js"></script>
<style scoped src="../generarVenta/generarVenta.css"></style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style>
.avatar_indexx {
  height: 135px;
  width: 135px;
  border-radius: 30px;
}

/* Móviles en horizontal o tablets en vertical */
/* ------------------------------------------------------------------------- / */
@media (min-width: 768px) {
  .tamanio {
    max-width: 30%;
    max-height: 30%;
  }
}

/* / Tablets en horizonal y escritorios normales
   ------------------------------------------------------------------------- / */
/* @media (min-width: 1024px) {
    .tamanio{
        max-width: 50%;
        max-height: 50%;
    }
} */

/* / Escritorios muy anchos
   ------------------------------------------------------------------------- */
/* @media (min-width: 1200px) {
    .tamanio{
        max-width: 150px;
        max-height: 150px;
    }
} */

/*
  ##Device = Desktops
  ##Screen = 1281px to higher resolution desktops
*/

@media (min-width: 1281px) {
  .tamanio {
    max-width: 150px;
    max-height: 150px;
  }
}

/*
  ##Device = Laptops, Desktops
  ##Screen = B/w 1025px to 1280px
*/

@media (min-width: 1025px) and (max-width: 1280px) {
  .tamanio {
    max-width: 150px;
    max-height: 150px;
  }
}
</style>
