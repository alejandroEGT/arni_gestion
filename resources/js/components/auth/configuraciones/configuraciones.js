export default {
    data() {
        return {
            nombre: '',
            email: '',
            password: '',
            passRepetir: '',

            // ALERTS MODIFICAR
            errores: [],
            correcto2: '',
            dismissSecs2: 5,
            dismissCountDown2: 0,

            // PASSWORD ERROR
            errorPassword: '',
            countPassword: false,

            // ERROR BLOQUEAR
            errorBloquear: '',
            countBloquear: false,

            // ALERTS BLOQUEAR
            correcto3: '',
            dismissSecs3: 5,
            dismissCountDown3: 0,

            usuariosFields: [
                { key: 'name', label: 'Nombre Usuario' },
                { key: 'email', label: 'Correo Electronico' },
                { key: 'creado', label: 'Fecha de Incorporacion' },
                { key: 'bloquear', label: '' },

            ],
            listarUsuarios: [],

            selectedRol: null,
            optionsRol: [
                { value: null, text: 'Seleccione una opcion' },
                { value: '1', text: 'Administrador' },
                { value: '2', text: 'Cajera' },
            ],

            logo: '',
            empresa: '',
            direccion: '',
            listarConf: [],
            logoNull: false,

            // ALERTS MODIFICAR
            erroresConf: [],
            correcto: '',
            dismissSecs: 3,
            dismissCountDown: 0,

        }
    },
    methods: {
        formatPrice(value) {
            let val = (value / 1).toFixed(0).replace('.', ',')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        },

        // MODAL CREAR USUARIO
        showModalCrearUsuario() {
            this.$refs['crearUsuario'].show();
        },
        hideModalCrearUsuario() {
            this.$refs['crearUsuario'].hide();
        },
        // MODAL BLOQUEAR USUARIO
        showModalBloquearUsuario() {
            this.$refs['bloquearUsuario'].show();
            this.traer_usuarios();
        },
        hideModalBloquearUsuario() {
            this.$refs['bloquearUsuario'].hide();
        },
        // MODAL CONFIG
        showModalConf() {
            this.$refs['configuraciones'].show();
            this.traer_configuraciones();
        },
        hideModalConf() {
            this.$refs['configuraciones'].hide();
        },

        contadorUsuarioCreado(dismissCountDown2) {
            this.dismissCountDown2 = dismissCountDown2;
        },
        alertUsuarioCreado() {
            this.dismissCountDown2 = this.dismissSecs2;
        },

        contadorRolUsuario(dismissCountDown3) {
            this.dismissCountDown3 = dismissCountDown3;
        },
        alertRolUsuario() {
            this.dismissCountDown3 = this.dismissSecs3;
        },

        contadorConf(dismissCountDown) {
            this.dismissCountDown = dismissCountDown;
            if (this.dismissCountDown == 0) {
                this.logout();
            }
        },
        alertConf() {
            this.dismissCountDown = this.dismissSecs;
        },

        crear_usuario() {

            const data = {
                'name': this.nombre,
                'email': this.email,
                'rol': this.selectedRol,
                'password': this.password,
                'passRepetir': this.passRepetir,
            }
            this.axios.post('api/crear_usuario', data).then((response) => {
                if (response.data.estado == 'success') {
                    this.correcto2 = response.data.mensaje;
                    this.alertUsuarioCreado();
                    this.nombre = '';
                    this.email = '';
                    this.password = '';
                    this.passRepetir = '';
                    this.errores = [];
                    this.countPassword = false;

                }

                else if (response.data.estado == 'failed_v') {
                    this.errores = response.data.mensaje;
                    this.countPassword = false;
                }

                else if (response.data.estado == 'errorPassword') {
                    this.errorPassword = response.data.mensaje;
                    this.countPassword = true;
                    this.errores = [];

                }
            })
                .catch(error => {
                    alert(error);
                })
        },

        traer_usuarios() {
            this.axios.get('api/traer_usuarios').then((response) => {
                this.listarUsuarios = response.data;
                // console.log(this.listarUsuarios);
            })

        },

        bloquear_usuario(metodo, id) {
            const data = {
                'metodo': metodo,
                'id': id,
            }
            this.axios.post('api/delete_usuario', data).then((response) => {
                if (response.data.estado == 'success') {
                    this.correcto3 = response.data.mensaje;
                    this.alertRolUsuario();
                    this.countBloquear = false;
                    this.traer_usuarios();

                }

                else if (response.data.estado == 'failed') {
                    this.errorBloquear = response.data.mensaje;
                    this.countBloquear = true;

                }
            })
                .catch(error => {
                    alert(error);
                })
        },

        crear_actualizar_configuraciones() {

            let formData = new FormData();
            formData.append('logo', this.logo[0]);
            formData.append('empresa', this.empresa);
            formData.append('direccion', this.direccion);

            this.axios.post('api/registro_configuraciones', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then((response) => {

                if (response.data.estado == 'success') {
                    this.correcto = response.data.mensaje;
                    this.alertConf();
                    this.traer_configuraciones();
                    this.erroresConf = [];
                }

                if (response.data.estado == 'failed_v') {
                    this.erroresConf = response.data.mensaje;
                }

                if (response.data.estado == 'failed') {
                    alert(response.data.mensaje);
                }
            });


        },

        traer_configuraciones() {
            this.axios.get('api/traer_configuraciones').then((response) => {
                if (response.data.estado == 'success') {

                    this.logoNull = true;

                    this.listarConf = response.data.configuraciones;
                    if (this.empresa == null || this.direccion == null) {
                        this.empresa = '';
                        this.direccion = '';
                    } else {
                        this.empresa = this.listarConf.empresa;
                        this.direccion = this.listarConf.direccion;
                    }
                }
            })
        },

        onFileChange(e) {
            this.logo = e.target.files || e.dataTransfer.files;
            console.log(this.logo[0]);
        },

        logout: function () {
            this.$auth.logout({
                makeRequest: true,
                redirect: "/"
            });
        }

    },


    mounted() {

    },
}