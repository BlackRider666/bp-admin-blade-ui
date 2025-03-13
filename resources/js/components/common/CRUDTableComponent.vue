<template>
  <v-data-table-server
      :headers="headers"
      :items="items.data"
      :options.sync="pagination"
      @update:options="searchItems"
      :items-length="items.total"
      v-model:items-per-page="pagination.itemsPerPage"
      v-model:page="pagination.page"
      v-model:sort-by="pagination.sortBy"
      must-sort
      :items-per-page-options="[
                      {value:5,title:'5'},
                      {value:10,title:'10'},
                      {value:15,title:'15'},
                      {value:20,title:'20'}
                      ]"
  >
    <template v-slot:item.actions="{ item }">
      <v-btn
          v-if="!!routes.show"
          :href="routes.show.replace(':id', item.id)"
          icon="mdi-eye"
          size="sm"
          variant="text"
          color="info"
          class="mx-1"
      ></v-btn>
      <v-btn
          v-if="!!routes.edit"
          icon="mdi-pencil"
          size="sm"
          variant="text"
          :href="routes.edit.replace(':id', item.id)"
          color="warning"
          class="mx-1"
      ></v-btn>
      <v-btn
          v-if="!!routes.delete"
          icon="mdi-delete"
          size="sm"
          variant="text"
          color="error"
          @click="deleteItem(item.id)"
          class="mx-1"
      ></v-btn>
      <form v-if="!!routes.delete" :ref="`delete-form-${item.id}`" :action="routes.delete.replace(':id', item.id)" method="POST" style="display: none;">
        <input type="hidden" name="_token" :value="csrfToken"/>
        <input type="hidden" name="_method" value="DELETE"/>
      </form>
    </template>
    <template v-if="Boolean(searchable)" v-slot:top>
      <form ref="search-form" class="flex-fill mb-2" :action="routes.index" method="GET">
        <input type="hidden" name="sortBy" v-if="pagination.sortBy.length > 0" v-model="pagination.sortBy[0].key"/>
        <input type="hidden" name="perPage" v-if="pagination.itemsPerPage" v-model="pagination.itemsPerPage"/>
        <input type="hidden" name="page" v-if="pagination.page" v-model="pagination.page"/>
        <input type="hidden" name="sortDesc" v-if="pagination.sortBy.length > 0" v-model="pagination.sortBy[0].order"/>
        <v-text-field
            name="q"
            v-model="search"
            label="Search"
            hide-details
            clearable
            @click:clear="clearSearch"
        >
          <template v-slot:append-inner>
            <v-btn icon="mdi-magnify" type="submit" variant="text" size="sm" ></v-btn>
          </template>
        </v-text-field>
      </form>
    </template>
  </v-data-table-server>
</template>

<script>
export default {
  name: 'TableComponent',
  props: {
    headers: {
      type: Array,
      default: () => [],
    },
    items: {
      type: Object,
      default: () => {
        return {
          current_page: 0,
          per_page: 10,
        }
      },
    },
    routes: {
      type: Object,
      default: () => {
        return {};
      },
    },
    csrfToken: {
      type: String,
      default: '',
    },
    filters: {
      type: Object,
      default: () => {
        return {};
      },
    },
    searchable: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      pagination:
          {
            page: this.items.current_page,
            itemsPerPage: parseInt(this.items.per_page),
            sortBy: this.filters.sortBy? [{key: this.filters.sortBy, order: this.filters.sortDesc}]: [{key:'id',order:'desc'}],
          },
      search: this.filters.q ? this.filters.q: null,
    }
  },
  methods: {
    deleteItem(id) {
      this.$refs[`delete-form-${id}`].submit()
    },
    searchItems() {
      let searchForm = this.$refs["search-form"];
      if (searchForm) {
        setTimeout(function () {
          searchForm.submit()
        },400)
      }
    },
    clearSearch() {
      this.search = null;
      this.searchItems();
    },
  },
}
</script>

<style scoped>
</style>
