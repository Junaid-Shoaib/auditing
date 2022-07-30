<template>
  <app-layout>
    <template #header>
      <div class="grid grid-cols-2 items-center">
        <h2 class="font-semibold text-xl text-gray-800 my-2">
          Upload Trial in Excel
        </h2>
      </div>
    </template>

    <FlashMessage />

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-2">
      <!-- <jet-button @click="create" class="ml-2">Create Account</jet-button> -->

      <div class="">
        <div class="relative overflow-x-auto mt-2 ml-2 sm:rounded-2xl">
          <form @submit.prevent="submit">
            <div class="p-8 -mr-6 -mb-8 flex flex-wrap">
              <input type="file" v-on:change="onFileChange" />
              <!-- <progress
                v-if="form2.progress"
                :value="form2.progress.percentage"
                max="100"
              >
                {{ form2.progress.percentage }}%
              </progress> -->
              <button
                class="border bg-indigo-300 rounded-xl px-4 py-2 m-4"
                type="submit"
              >
                Upload Trial Balance
              </button>
            </div>
          </form>
        </div>
        <!-- <paginator class="mt-6" :balances="balances" /> -->
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import JetButton from "@/Jetstream/Button";
import Paginator from "@/Layouts/Paginator";
import FlashMessage from "@/Layouts/FlashMessage";
import { pickBy } from "lodash";
import { throttle } from "lodash";
import Multiselect from "@suadelabs/vue3-multiselect";
import { useForm } from "@inertiajs/inertia-vue3";

export default {
  setup() {
    const form = useForm({
      avatar: null,
    });
    return { form };
  },

  components: {
    AppLayout,
    FlashMessage,
    // Treeselect,
  },

  props: {
    data: Object,
    fold: Object,
    show_folder: Boolean,
    show_upload: Boolean,
    show_groups: Boolean,
  },

  data() {
    return {
      value: null,
    };
  },

  methods: {
    submit() {
      this.form.post(route("trial.read"));
    },

    onFileChange(e) {
      var files = e.target.files || e.dataTransfer.files;
      if (!files.length) return;
      this.form.avatar = files[0];
    },
  },
};
</script>
