<template>
  <div>
    <div class="row m-4">
      <div class="col-12 col-md-12 col-lg-12">
        <!-- FORMULARIO -->
        <b-card class="text-center tituloTabla transparencia">
          <b-card-header class="fondoCategoria mb-4">Pagos por pagar</b-card-header>


          <b-card-body>
              <b-table
              show-empty
              emptyText="Buscando clientes..."
              small
              striped
              hover
              bordered
              stacked="lg"
              head-variant="dark"
              :fields="cabeza"
              :items="get_tabla"
            >
                <template v-slot:cell(venta_id)="data">
                    <div class="col-12">{{ data.item.venta_id }}</div>
                </template>
                <template v-slot:cell(cliente)="data">
                    <div class="col-12">{{ data.item.cliente }}</div>
                </template>
                <template v-slot:cell(contacto)="data">
                    <div class="col-12">{{ data.item.contacto }}</div>
                </template>
                <template v-slot:cell(detalle_credito)="data">
                    <div class="col-12">{{ data.item.detalle_credito }}</div>
                </template>
                <template v-slot:cell(monto_credito)="data">
                    <div class="col-12">$ {{ formatPrice(data.item.monto_credito) }}</div>
                </template>
                <template v-slot:cell(fecha)="data">
                    <div class="col-12">{{ data.item.to_char }}</div>
                </template>
                <template v-slot:cell(activo)="data">
                    <div class="col-12">{{ data.item.activo }}</div>
                </template>
                <template v-slot:cell(opciones)="data">
                    <div class="col-12">
                        <b-button :disabled="true" @click="
                                    a_cliente_id=data.item.id;
                                    a_nombres=data.item.nombres;
                                    a_apellidos=data.item.apellidos;
                                    a_contacto=data.item.contacto;
                                    a_email=data.item.email;
                                    a_direccion=data.item.direccion;
                                    btn_actualizar=false;
                        " v-b-modal="'modal'+data.item.id">Opciones</b-button>

                        <b-modal hide-footer="" :id="'modal'+data.item.id" :title="'Opciones para '+data.item.nombres+' '+data.item.apellidos">
                            <fieldset class="scheduler-border">
                                <legend class="scheduler-border">Actualizar información</legend>
                                <div class="control-group">
                                    <label class="control-label input-label">Nombres:</label>
                                    <b-input v-model="a_nombres" placeholder="Nombre.."></b-input>
                                    <br>
                                    <label class="control-label input-label">Apellidos:</label>
                                    <b-input v-model="a_apellidos" placeholder="Apellidos.."></b-input>
                                    <br>
                                    <label class="control-label input-label">Contacto:</label>
                                    <b-input v-model="a_contacto" placeholder="Contacto.."></b-input>
                                    <br>
                                    <label class="control-label input-label">Email:</label>
                                    <b-input v-model="a_email" placeholder="Email.."></b-input>
                                    <br>
                                    <label class="control-label input-label">Dirección:</label>
                                    <b-input v-model="a_direccion" placeholder="Dirección.."></b-input>
                                    <br>
                                    <b-button :disabled="btn_actualizar"
                                              @click="actualizar"
                                              variant="success">Actualizar
                                              <b-spinner v-if="btn_actualizar" small label="Small Spinner"></b-spinner></b-button>
                                    <b-button :disabled="btn_inhabilitar"  @click="inhabilitar(data.item.id)" variant="danger">Inhabilitar
                                        <b-spinner v-if="btn_inhabilitar" small label="Small Spinner"></b-spinner>
                                    </b-button>

                                    <!-- <b-alert
                                    :show="a_dismissCountDown"
                                        dismissible
                                        variant="success"

                                    >
                                    <b>{{a_correcto}}</b>
                                    </b-alert>

                                    <b-alert
                                        :show="a_dismissCountDown3"
                                        variant="warning"
                                        @dismissed="a_dismissCountDown3=0"
                                        @dismiss-count-down="a_countDownChanged3"
                                        >
                                        <b>{{a_errores}}</b>
                                        </b-alert> -->


                                </div>
                            </fieldset>
                        </b-modal>
                    </div>
                </template>

            </b-table>
          </b-card-body>
        </b-card>
      </div>
    </div>
  </div>
</template>

<script>
export default {
    data(){
        return{
            cabeza: [
                { key: 'venta_id', label: 'ID venta', variant: 'dark' },
                { key: 'cliente', label: 'Cliente' },
                { key: 'contacto', label: 'Contacto' },
                { key: 'detalle_credito', label: 'Detalle del credito' },
                { key: 'monto_credito', label: 'Monto del credito' },
                { key: 'fecha', label: 'Fecha' },
                { key: 'activo', label:'Estado' },
                { key: 'opciones', label: 'opciones' },
            ],
            get_tabla:[]
        }
    },
    created(){
        this.cliente_deuda();
    },

    methods:{
        cliente_deuda(){
            this.axios.get('api/cliente_deuda').then((res)=>{
                if(res.data.estado=='success'){
                    this.get_tabla = res.data.listar;
                }
            });
        },
        formatPrice(value) {
            let val = (value / 1).toFixed(0).replace('.', ',')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        },
    },

}
</script>

<style>

</style>
