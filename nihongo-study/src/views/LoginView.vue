<template>
  <n-form>
    <n-form-item label="input">
      <n-input v-model:value="form.username" placeholder="username" />
    </n-form-item>
    <n-form-item>
      <n-input v-model:value="form.password" type="password" show-password-on="mousedown" placeholder="password" />
    </n-form-item>
  </n-form>
</template>

<script lang="ts" setup>
import { ref } from "vue"
const form = ref({
  username: "",
  password: ""
})
</script>

<style scoped>

</style>