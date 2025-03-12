<template>
<v-textarea
  :model-value="value"
  :name="name"
  :label="label"
></v-textarea>
</template>

<script>
export default {
  name: 'TextInput',
  props: {
    value: {
      type: String,
      default: ''
    },
    name: {
      type: String,
      default: ''
    },
    label: {
      type: String,
      default: ''
    }
  }
}
</script>

<style scoped>

</style>
