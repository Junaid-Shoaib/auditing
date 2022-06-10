<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-white my-2">Year</h2>
    </template>
    <div class="">
      <form @submit.prevent="submit">
        <div class="p-2 mr-2 mb-2 mt-4 ml-6 flex flex-wrap">
          <label class="my-2 mr-8 text-right w-36 font-bold"
            >Begin Date :</label
          >
          <input
            type="date"
            v-model="form.begin"
            label="date"
            placeholder="Enter Begin date:"
            class="pr-2 pb-2 rounded-md placeholder-indigo-300"
          />
          <div v-if="errors.begin">{{ errors.begin }}</div>
        </div>

        <div class="p-2 mr-2 mb-2 mt-4 ml-6 flex flex-wrap">
          <label class="my-2 mr-8 text-right w-36 font-bold">End Date :</label>
          <input
            type="date"
            v-model="form.end"
            class="pr-2 pb-2 rounded-md placeholder-indigo-300"
            label="date"
            placeholder="Enter End date:"
          />
          <div v-if="errors.end">{{ errors.end }}</div>
        </div>
        <div
          class="
            px-4
            py-2
            bg-gray-200
            border-t border-gray-200
            flex
            ml-60
            items-center
          "
        >
          <button
            class="
              border
              rounded-xl
              shadow-md
              p-1
              px-4
              mt-1
              bg-gray-800
              text-white
              ml-2
              inline-block
              hover:bg-gray-700 hover:text-white
            "
            type="submit"
          >
            Update Year
          </button>
        </div>
      </form>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
// import Datepicker from "vue3-datepicker";
// import format from "date-fns/format";
import Input from "../../Jetstream/Input.vue";

export default {
  components: {
    AppLayout,
    // Datepicker,
    Input,
  },

  props: {
    errors: Object,
    year: Object,
  },

  data() {
    return {
      form: {
        begin: this.year.begin,
        end: this.year.end,
      },
    };
  },

  methods: {
    submit() {
      this.$inertia.put(route("years.update", this.year.id), this.form);
    },
  },
};
</script>
