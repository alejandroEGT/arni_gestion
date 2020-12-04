<template>
  <div>
    <section id="cover" class="min-vh-100">
      <div id="cover-caption">
        <div class="container">
          <div class="row text-white">
            <div
              class="col-xl-5 col-lg-6 col-md-8 col-sm-10 mx-auto text-center form p-4 fondoLogin"
            >
              <h1 class="display-6 py-2">Formulario de Ingreso ARNI</h1>
              <div class="px-2">
                <div class="justify-content-center">
                  <div class="form-group">
                    <input
                      v-model="email"
                      name="correo"
                      type="text"
                      class="form-control"
                      placeholder="Correo"
                    />
                  </div>
                  <div class="form-group">
                    <input
                      v-model="password"
                      name="pass"
                      type="password"
                      class="form-control"
                      placeholder="******"
                    />
                  </div>
                  <button :disabled="load_login" type="submit" class="btn btn-primary btn-lg" @click="login()">
                      Ingresar
                      <div v-if="load_login" class="spinner-border text-dark spinner-border-sm" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>

                  </button>
                </div>
                <!-- <a @click="url('recuperarPassword')"><em style="cursor:pointer;" class="float-left">Olvido su contraseña</em></a> -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
#cover {
   background:#E5E7E9;
    background-size: cover;
    height: 100%;
    text-align: center;
    display: flex;
    align-items: center;
    position: relative;
}
#cover-caption {
  width: 100%;
  position: relative;
  z-index: 1;
}
/* only used for background overlay not needed for centering */
.fondoLogin:before {
  content: "";
  height: 100%;
  left: 0;
  position: absolute;
  top: 0;
  width: 100%;
  background-color: rgba(0, 0, 0, 0.3);
  z-index: -1;
  border-radius: 10px;
}
</style>

<script>
export default {
  data() {
    return {
      email: "",
      password: "",

      load_login: false
    };
  },
  methods: {
    login() {
        this.load_login = true;
      var app = this;
      this.$auth.login({
        params: {
          email: app.email,
          password: app.password
        },
        success: function() {this.load_login = false;},

        error: function() {
          this.load_login = false;
          alert("Error, Correo y/o contraseña incorrecto.");
          this.password = "";
        },

        rememberMe: true,
        redirect: "/index",
        fetchUser: true
      });
    },

    url(ruta) {
      this.$router.push({ path: ruta }).catch(error => {
        if (error.name != "NavigationDuplicated") {
          throw error;
        }
      });
    }
  }
};
</script>
