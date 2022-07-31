<template>
  <div
    v-if="$page.props.flash.success"
    class="bg-green-600 text-white text-center"
  >
    {{ $page.props.flash.success }}
  </div>

  <div
    v-if="$page.props.flash.warning"
    class="bg-yellow-600 text-white text-center"
  >
    {{ $page.props.flash.warning }}
  </div>

  <div v-if="$page.props.flash.error" class="bg-red-600 text-white text-center">
    {{ $page.props.flash.error }}
  </div>
</template>
