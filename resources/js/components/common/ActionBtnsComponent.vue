<template>
  <div class="d-flex align-center justify-space-between">
    <v-btn color="secondary" v-if="backUrl" :href="backUrl">{{backLabel}}</v-btn>
    <v-btn color="primary" type="submit">{{ label }}</v-btn>
  </div>
</template>

<script>
export default {
  name: 'ActionBtnsComponent',
  props: {
    label: {
      type: String,
      default: ''
    },
    backUrl:{
      type: String,
      default: null
    },
    backLabel:{
      type: String,
      default: ''
    },
  }
}
</script>

<style>

</style>
