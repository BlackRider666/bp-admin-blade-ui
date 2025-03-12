<template>
<v-text-field
  type="numeric"
  :model-value="value"
  :name="name"
  :label="label"
></v-text-field>
</template>

<script>
export default {
  name: 'IntegerInput',
  props: {
    value: {
      type: Number,
      default: 0
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
