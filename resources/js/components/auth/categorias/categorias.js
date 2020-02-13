
export default {

    data() {

        return {
            // REGISTRO
            categoria: '',

            // ALERTS INGRESO
            errores: [],
            correcto: '',
            dismissSecs: 5,
            dismissCountDown: 0,

            // ALERTS MODIFICAR
            errores2: [],
            correcto2: '',
            dismissSecs2: 5,
            dismissCountDown2: 0,

            // ALERTS BUSCADOR
            errores3: '',
            dismissSecs3: 5,
            dismissCountDown3: 0,


            // TABLA
            loading: false,
            productosFields: [
                { key: 'index', label: 'ID' },
                { key: 'cat', label: 'Categorias' },
                { key: 'fecha', label: 'Creado' },
                { key: 'opc', label: 'Opciones' },
            ],
            listarCategorias: [],

            //ACTUALIZAR
            campoUpd: '',

            // BUSCADOR
            buscadorCategoria: '',
            categoriaSearch: '',
            idCategoria: '0',
            btn_buscar: true,

        }

    },

    methods: {
        url(ruta) {
            this.$router.push({ path: ruta }).catch(error => {
                if (error.name != "NavigationDuplicated") {
                    throw error;
                }
            });
        },

        countDownChanged(dismissCountDown) {
            this.dismissCountDown = dismissCountDown
        },
        showAlert() {
            this.dismissCountDown = this.dismissSecs
        },

        countDownChanged2(dismissCountDown2) {
            this.dismissCountDown2 = dismissCountDown2
        },
        showAlert2() {
            this.dismissCountDown2 = this.dismissSecs2
        },

        countDownChanged3(dismissCountDown3) {
            this.dismissCountDown3 = dismissCountDown3
        },
        showAlert3() {
            this.dismissCountDown3 = this.dismissSecs3
        },

        showModal(id) {
            console.log(this.$refs);
            this.$refs['editarModalCategoria' + id].show();
        },
        hideModal(id) {
            this.$refs['editarModalCategoria' + id].hide();
            this.campoUpd = '';
            this.errores2 = [];
        },

        registrar_categorias() {
            const data = {
                'descripcion': this.categoria,

            }
            this.axios.post('api/registro_categoria', data).then((response) => {
                if (response.data.estado == 'success') {
                    this.correcto = response.data.mensaje;
                    this.showAlert();
                    this.categoria = '';
                    this.errores = [];
                    this.traer_categorias();
                }

                if (response.data.estado == 'failed_v') {
                    this.errores = response.data.mensaje;
                }

                if (response.data.estado == 'failed') {
                    alert(response.data.mensaje);
                }

            });
        },

        traer_categorias() {
            this.axios.get('api/traer_categorias').then((response) => {
                if(response.data.estado == 'success'){
                    this.listarCategorias = response.data.cat;
                }
            })

        },

        actualizar_dato(id, campo, input) {
            const data = {
                'id': id,
                'campo': campo,
                'input': input,
            }
            this.axios.post('api/modificar_campo_categoria', data).then((response) => {
                if (response.data.estado == 'success') {
                    this.correcto2 = response.data.mensaje;
                    this.showAlert2();
                    this.campoUpd = '';
                    this.errores2 = [];
                    this.traer_categorias();
                    this.hideModal(id);
                }
                if (response.data.estado == 'failed_v') {
                    this.errores2 = response.data.mensaje;
                }

                if (response.data.estado == 'failed') {
                    alert(response.data.mensaje);
                    this.campoUpd = '';
                }
            })
                .catch(error => {
                    alert(error);
                    this.loading = false;
                })
        },

        traer_categoria() {
            this.listarCategorias = [];
            this.axios.get('api/buscar_categoria/' + this.buscadorCategoria).then((response) => {

                if (this.buscadorCategoria == '') {
                    alert("El campo no puede quedar vacio, ingrese una categoria porfavor.");
                    this.traer_categorias();
                } else {

                    if (response.data.estado == 'success') {

                        this.listarCategorias = response.data.categoria;
                        this.buscadorCategoria = '';
                        this.btn_buscar = true;
                    } else {

                        if (response.data.estado == 'failed') {
                            this.showAlert3();
                            this.errores3 = response.data.mensaje;
                            this.buscadorCategoria = '';
                            this.btn_buscar = true;
                            this.traer_categorias();
                        }
                    }
                }

            })
                .catch(error => {
                    alert(error);
                })
        },

        escribiendo() {
            if (this.buscadorCategoria.toLowerCase().trim() == '') {
                this.btn_buscar = true;
            } else {

                this.btn_buscar = false;
            }
        }

    },

    mounted() {
        this.traer_categorias();
    },
}