<template>
  <app-layout>
    <template #header>
      <div class="grid grid-cols-2 items-center">
        <div>
          <multiselect
            style="width: 50%; z-index: 10"
            class="float-left rounded-lg border border-white"
            placeholder="Select Company."
            v-model="selected_folder2"
            track-by="id"
            label="name"
            :options="folders"
            @update:model-value="foch"
          >
          </multiselect>

          <jet-button @click="folderModification" class="ml-2 mt-1"
            >Folder Modification</jet-button
          >
        </div>

        <div class="justify-end">
          <multiselect
            style="width: 50%; z-index: 10"
            class="float-right rounded-lg border border-white"
            placeholder="Select Company."
            v-model="co_id"
            track-by="id"
            label="name"
            :options="options"
            @update:model-value="coch"
          >
          </multiselect>
        </div>
      </div>
    </template>

    <FlashMessage />

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-2">
      <!-- <jet-button @click="create" class="mt-4 ml-8">Create</jet-button> -->

      <!-- <form @submit.prevent="form.get(route('years.create'))"> -->
      <!-- <div class="grid grid-cols-2"> -->

      <jet-button @click="uploadFile" class="ml-2">Upload File</jet-button>

      <div class="">
        <div class="obsolute overflow-x-auto mt-2 ml-2 sm:rounded-2xl">
          <table class="w-full shadow-lg border rounded-2xl">
            <thead>
              <tr class="bg-gray-800 text-white">
                <th class="py-1 px-4 border">File Name</th>
                <th class="py-1 px-4 border">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr
                class="bg-gray-50"
                v-for="item in balances.data"
                :key="item.id"
              >
                <td class="w-4/12 px-4 border w-2/5">
                  {{ item.name }}
                </td>
                <td class="w-4/12px-4 border w-2/6 text-center">
                  <button
                    class="
                      border
                      bg-indigo-300
                      rounded-xl
                      px-4
                      m-1
                      hover:text-white hover:bg-indigo-400
                    "
                    @click="downloadFile(item.id)"
                    type="button"
                  >
                    <span>Download</span>
                  </button>
                  <button
                    class="
                      border
                      bg-red-500
                      rounded-xl
                      px-4
                      m-1
                      hover:text-white hover:bg-red-600
                    "
                    @click="deleteFileFolder(item.id)"
                    type="button"
                  >
                    <!-- v-if="item.delete" -->
                    <span>Delete</span>
                  </button>
                </td>
              </tr>
              <tr v-if="balances.data.length === 0">
                <td class="border-t px-6 py-4 bg-gray-100" colspan="4">
                  No Record found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <paginator class="mt-6" :balances="balances" />
      </div>
      <!-- </form> -->
    </div>
  </app-layout>
</template>

<style src="@suadelabs/vue3-multiselect/dist/vue3-multiselect.css"></style>
<script>
import AppLayout from "@/Layouts/AppLayout";
import JetButton from "@/Jetstream/Button";
import { useForm } from "@inertiajs/inertia-vue3";
import Multiselect from "@suadelabs/vue3-multiselect";
import Paginator from "@/Layouts/Paginator";
import FlashMessage from "@/Layouts/FlashMessage";
// import { Head, Link } from "@inertiajs/inertia-vue3";

export default {
  components: {
    AppLayout,
    JetButton,
    useForm,
    Multiselect,
    Paginator,
    FlashMessage,
    // Link,
    // Head,
  },

  props: {
    folders: Object,
    selected_folder: Object,
    balances: Object,
    companies: Object,
    company: Object,
  },

  data() {
    return {
      co_id: this.company,
      options: this.companies,
      folders: this.folders,
      selected_folder2: this.selected_folder,
    };
  },

  setup(props) {
    const form = useForm({});
    return { form };
  },

  methods: {
    uploadFile() {
      this.$inertia.get(
        route("filing.uploadFile", this.selected_folder2["id"])
      );
    },

    foch() {
      this.$inertia.get(route("filing.folder", this.selected_folder2["id"]));
    },

    downloadFile: function (id) {
      this.$inertia.get(route("filing.downloadFile", id));
    },

    deleteFileFolder: function (id) {
      this.$inertia.get(route("filing.deleteFileFolder", id));
    },

    folderModification: function () {
      this.$inertia.get(route("filing", ["execution"]));
    },

    // edit(id) {
    //   this.$inertia.get(route("years.edit", id));
    // },

    // destroy(id) {
    //   this.$inertia.delete(route("years.destroy", id));
    // },

    coch() {
      this.$inertia.get(route("companies.coch", this.co_id["id"]));
    },
  },
};
</script>
