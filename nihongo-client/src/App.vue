<script setup lang="ts">
document.addEventListener('gesturestart', function (e:Event) {
  if(e.preventDefault) {
    e.preventDefault()
  }else{
    e.returnValue = false
  }
});
</script>

<template>
        <router-view />
</template>

<style scoped>

</style>
