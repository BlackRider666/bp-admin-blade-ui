<template>
    <v-table>
        <tbody>
        <tr
            v-for="item in items"
            :key="item.key"
        >
            <td>{{ item.key }}</td>
            <td>{{ item.value }}</td>
        </tr>
        </tbody>
    </v-table>
</template>
<script>
export default {
    name: "TableComponent",
    props: {
        items: {
            type: Array,
            default:() => [],
        }
    },
}
</script>
<style scoped>

</style>
