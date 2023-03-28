<script setup lang="ts">

</script>

<template>
  <n-config-provider>
    <n-loading-bar-provider>
    <n-notification-provider>
      <n-message-provider>
        <router-view />
      </n-message-provider>
    </n-notification-provider>
    </n-loading-bar-provider>
  </n-config-provider>

</template>

<style scoped>

</style>
