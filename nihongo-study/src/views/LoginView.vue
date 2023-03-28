<template>
  <n-form>
    <n-form-item label="账号">
      <n-input v-model:value="form.username" placeholder="username" />
    </n-form-item>
    <n-form-item label="密码">
      <n-input v-model:value="form.password" type="password" show-password-on="mousedown" placeholder="password" />
    </n-form-item>
    <n-form-item>
      <n-button block type="primary" @click="submit">提交</n-button>
    </n-form-item>
  </n-form>
</template>

<script lang="ts" setup>
import { ref } from "vue"
import {useStorage} from "vue3-storage"
import * as md5 from "ts-md5"
import {useRouter} from "vue-router";

const storage = useStorage()
const router = useRouter()
const form = ref({
  username: "",
  password: ""
})
const username = 'admin'
const password = 'haruki'
const submit = () => {
  if(form.value.username === username && form.value.password === password) {
    const d = new Date()
    const token = md5.Md5.hashStr( Math.round(d.valueOf() / 1000).toString())
    storage.setStorageSync("admin-token", token, 86400 * 1000)
    router.push("/")
  }
}

</script>

<style scoped>
.n-form {
  margin-top: 30vh

}
</style>