<template>
  <v-app>
    <app-header/>
    <v-main>
      <left-bar :items="items"/>
      <crud-layout>
        <template v-slot:title>
          <span>Test</span>
          <v-btn color="primary" ><v-icon>mdi-plus</v-icon>Create</v-btn>
        </template>
        <crud-items :searchable="true" :headers="[{title:'ID', key:'id', sortable:true},{title:'Name', key:'name', sortable:true}, {title:'',key:'actions', sortable: false}]" :items="data" :routes="routes"></crud-items>
        <v-alert type="success" title="Alert" text="Test text"></v-alert>
        <v-alert type="warning" title="Alert" text="Test text"></v-alert>
        <v-alert type="error" title="Alert" text="Test text"></v-alert>
        <v-alert type="info" title="Alert" text="Test text"></v-alert>
        <v-alert color="muted" icon="$info" title="Alert" text="Test text"></v-alert>
      </crud-layout>
      <app-footer copyright="Test copyright"/>
    </v-main>
<!--    <auth-layout>-->
<!--      <template v-slot:title>BPAdmin</template>-->
<!--      <v-form>-->
<!--        <string-input type="email" label="Email"></string-input>-->
<!--        <string-input type="password" label="Password"></string-input>-->
<!--        <submit-input label="Login"></submit-input>-->
<!--      </v-form>-->
<!--    </auth-layout>-->
  </v-app>
</template>

<script>


export default {
  name: 'App',
  data() {
    return {
      items:{"pages":{"title":"Home","href":"http:\/\/localhost\/admin","active":true,"icon":"mdi-chart-areaspline"},"users":{"icon":"mdi-account-group","items":{"users":{"icon":"mdi-account-group","title":"Users","href":"http:\/\/localhost\/admin\/users","active":false}},"title":"Users","href":"http:\/\/localhost\/admin\/users","active":false}},
      data: {"current_page":1,"data":[{"id":1,"name":"SuperAdmin1","email":"admin@test.com","email_verified_at":null,"created_at":"2025-02-20T19:15:13.000000Z","updated_at":"2025-02-26T00:10:51.000000Z"},{"id":2,"name":"test","email":"test@test.com","email_verified_at":null,"created_at":"2025-02-26T00:14:20.000000Z","updated_at":"2025-02-26T00:14:20.000000Z"}],"first_page_url":"http:\/\/localhost\/admin\/auth\/login?page=1","from":1,"last_page":1,"last_page_url":"http:\/\/localhost\/admin\/auth\/login?page=1","links":[{"url":null,"label":"&laquo; Previous","active":false},{"url":"http:\/\/localhost\/admin\/auth\/login?page=1","label":"1","active":true},{"url":null,"label":"Next &raquo;","active":false}],"next_page_url":null,"path":"http:\/\/localhost\/admin\/auth\/login","per_page":10,"prev_page_url":null,"to":2,"total":2},
      routes:{"index":"http:\/\/localhost\/admin\/users","create":"http:\/\/localhost\/admin\/users\/create","show":"http:\/\/localhost\/admin\/users\/:id","edit":"http:\/\/localhost\/admin\/users\/:id\/edit","delete":"http:\/\/localhost\/admin\/users\/:id"},
    }
  }
}
</script>
