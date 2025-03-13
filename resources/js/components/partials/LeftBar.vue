<template>
  <v-navigation-drawer v-model="drawer" permanent color="surface" class="border-e-md">
    <v-list class="ml-4" density="compact" v-model:opened="opened">
      <template v-for="(item, key) in items" :key="key">
        <v-list-group v-if="item.items" :value="item.title" class="mb-1">
          <template v-slot:activator="{ props }">
            <v-list-item v-bind="props" :prepend-icon="item.icon" class="pl-5 pr-2 mb-1 rounded-e-xl">
              <v-list-item-title class="font-weight-medium">{{ item.title }}</v-list-item-title>
            </v-list-item>
          </template>
          <v-list-item
              v-for="(child, childKey) in item.items"
              :key="childKey"
              :href="child.href"
              :class="{ 'active': child.active }"
              class=" rounded-e-xl"
              :prepend-icon="child.icon"
              link
          >
            <v-list-item-title class="font-weight-medium">{{ child.title }}</v-list-item-title>
          </v-list-item>
        </v-list-group>
        <v-list-item v-else :href="item.href" :class="{ 'active': item.active }" class="pl-5 pr-1 mb-1 rounded-e-xl" :prepend-icon="item.icon">
          <v-list-item-title class="font-weight-medium">{{ item.title }}</v-list-item-title>
        </v-list-item>
      </template>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
export default {
  name: 'LeftBar',
  props: {
    items: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      drawer: true,
      opened: Object.values(this.items).filter((item) => Boolean(item.active)).map((item) => item.title),
    };
  },
};
</script>

<style scoped>
.v-list-item {
  border-left: 4px solid transparent;
}

.active {
  border-left: 4px solid rgb(var(--v-theme-primary));
  background-color: rgb(var(--v-theme-background));
}

.v-list-group {
  --prepend-width: 0px;
}
</style>
