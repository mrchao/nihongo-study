<script lang="ts" setup>
// name: RegisterView
// date: 2023/3/19
// user: haruki
import {ref, onMounted} from "vue"
import {UserType} from "../types";
import { useStorage } from "vue3-storage"
import { useRouter } from "vue-router"
import {Md5} from 'ts-md5'
import {useUserStore} from "../stores/user";
const router = useRouter()
const storage = useStorage()
const userStore = useUserStore()
const overlay = ref<boolean>(true)

const form = ref<UserType>({
  nickname: "",
  device_id: ""
});

onMounted(() => {
  const keyStr = navigator.appVersion + navigator.userAgent
  form.value.device_id = Md5.hashStr(keyStr);
  userStore.login(form.value).then(result => {
    if(result) {
      router.push("/")
      return
    }
    overlay.value = false
  })
})

const login = async () => {
  const result = await userStore.login(form.value);
  if(result) {
    router.push("/").then()
  }
}

</script>

<template>

    <var-overlay v-model:show="overlay" @click.stop>
      <var-loading description="登陆中..." :loading="overlay" />
    </var-overlay>

  <var-space justify="center">
    <var-avatar class="logo" :size="128" color="#4a79f5">サ</var-avatar>
  </var-space>

  <var-cell>
    <var-input placeholder="昵称" v-model="form.nickname" />
    <var-input placeholder="登陆KEY" v-model="form.device_id" />
  </var-cell>
  <var-cell>
    <var-button block @click="userStore.login(form)">登陆</var-button>
  </var-cell>
</template>

<style scoped>
.logo {
  font-size: 96px;
  margin: 1em 0;
}
</style>