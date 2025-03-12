<template>
<v-container fluid class="fill-height align-content-start">
  <v-row>
    <v-col cols="12">
      <v-card class="py-3 px-1 rounded-lg">
        <v-card-title class="d-flex justify-space-between align-center text-h5 font-weight-bold mb-4">
          <slot name="title"></slot>
        </v-card-title>
        <v-card-text>
          <slot></slot>
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</v-container>
</template>

<script>
export default {
  name: 'CRUDLayout'
}
</script>

<style scoped>
.fill-height {
  height: calc(100% - 82px);
}
</style>
