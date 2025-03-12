<template>
  <v-app-bar app class="border-b-md" flat :height="82">
    <v-container fluid>
      <v-row align="center">
        <v-col cols="2" class="d-flex align-center">
            <v-avatar color="secondary" class="mx-2">
              <v-img v-if="logo" :src="logo"></v-img>
              <template v-else>
                <span>{{ title.slice(0, 3) }}</span>
              </template>
            </v-avatar>
            <div class="text-h5 font-weight-bold">{{ title }}</div>
        </v-col>
        <v-col cols="8"></v-col>
        <v-col cols="2" class="d-flex justify-end align-center">
          <v-menu
              offset="10"
          >
            <template v-slot:activator="{ props }">
              <div class="d-flex flex-column align-center mr-4">
                <div class="text-body-1 text-capitalize">{{username}}</div>
              </div>
              <v-avatar
                  v-bind="props"
                  image="/bpadmin/img/default.jpg"
              >
              </v-avatar>
            </template>

            <v-list border="md" class="pa-0 border-md">
              <v-list-item link>
                <v-list-item-title @click="logout">Logout</v-list-item-title>
                <form ref="logout-form" :action="logoutroute" method="POST" style="display: none;">
                  <input type="hidden" name="_token" :value="csrftoken"/>
                </form>
              </v-list-item>
            </v-list>
          </v-menu>
        </v-col>
      </v-row>
    </v-container>
  </v-app-bar>
</template>

<script>
export default {
    name: 'AppHeader',
    props: {
        username: {
            type: String,
            default: 'TestUser'
        },
        logoutroute: {
            type: String,
            default: ''
        },
        csrftoken: {
            type: String,
            default:''
        },
        logo: {
          type: String,
          default: null,
        },
        title: {
          type: String,
          default: 'BPAdmin',
        },
    },
    methods: {
        logout() {
            this.$refs["logout-form"].submit();
        }
    }
}
</script>

<style scoped>

</style>
