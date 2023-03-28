<script lang="ts" setup>
import { h, computed, ref } from 'vue'
import {RouterLink, RouterView, useRouter} from 'vue-router'
import type { MenuOption } from 'naive-ui'
import { useMessage } from 'naive-ui'
import {useStorage} from "vue3-storage";

const storage = useStorage()
const router = useRouter();

const menuList = [
  ['/', '首页'],
  // ['/about', '关于'],
  // ['/login', '登陆'],
  ['/word', '单词录入'],
  ['/grammar', '句式录入'],
  ['/dict', '词典管理'],
]
const menuOptions = computed(() => {

  const token = storage.getStorageSync("admin-token")
  if(! token) {
    router.push("/login");
    return
  }

  const arr: MenuOption[] = []
  menuList.forEach((menu, index) => {
    const [routerPath, labelName, ] = menu
    arr.push({
      label: () =>
          h(
              RouterLink,
              { to: { path: routerPath } },
              {
                default: () => labelName,
              }
          ),
      key: index,
    })
  })
  return arr
})


// window.$message = useMessage()
</script>

<template>
  <n-layout has-sider>
    <n-layout-sider>
      <n-menu :options="menuOptions" />
    </n-layout-sider>
    <n-layout>
      <router-view />
    </n-layout>
  </n-layout>
</template>

<style scoped>
.n-layout {
  height: 100vh
}
</style>