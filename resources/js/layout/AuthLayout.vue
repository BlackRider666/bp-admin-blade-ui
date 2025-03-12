<template>
    <v-container fluid class="fill-height">
        <v-row class="justify-center">
            <v-col cols="6">
                <v-card>
                    <v-card-title class="d-flex justify-center align-center text-h5 font-weight-bold mb-4">
                        <slot name="title"></slot>
                    </v-card-title>
                    <v-divider/>
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
    name: 'AuthLayout'
}
</script>

<style scoped>

</style>
