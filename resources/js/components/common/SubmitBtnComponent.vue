<template>
  <div class="d-flex align-center justify-center">
    <v-btn color="primary" type="submit">{{ label }}</v-btn>
  </div>
</template>

<script>
export default {
  name: 'SubmitBtnComponent',
  props: {
    label: {
      type: String,
      default: ''
    }
  }
}
</script>

<style scoped>

</style>
