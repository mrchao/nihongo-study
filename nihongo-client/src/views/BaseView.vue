<script lang="ts" setup>
// name: BaseView
// date: 2023/3/3
// user: devine
import {ref, onMounted} from "vue"
import { useRouter } from "vue-router"
import { useStorage } from "vue3-storage"
import {useUserStore} from "../stores/user";
const router = useRouter()
const storage = useStorage()
const userStore = useUserStore()

const toHomeRoute = () => {
  router.push("/")
}

onMounted(() => {
  if(!userStore.load()) {
    router.push("/login")
  }
})

</script>

<template>
  <var-app-bar :elevation="false" title="首页">
    <template #left>
      <var-button @click="toHomeRoute" color="transparent" text-color="#FFF" round text>
        <var-icon name="chevron-left" :size="24" />
      </var-button>
    </template>
    <template #right>
      <var-space>
        <var-button type="info" ripple @click="router.push('/study')">学习</var-button>
        <var-button type="info" ripple @click="router.push('/match')">配对</var-button>
        <var-button type="info" ripple @click="router.push('/write')">听写</var-button>
        <var-button type="info" ripple @click="router.push('/topic')">测验</var-button>
      </var-space>
    </template>
  </var-app-bar>
  <main>
    <router-view />
  </main>
</template>

<style scoped>
main {
  padding: 0 1em
}
</style>