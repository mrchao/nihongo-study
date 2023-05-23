<script lang="ts" setup>
// name: DemoView
// date: 2023/3/3
// user: devine
import {ref} from "vue"
</script>

<template>
  <h1>日本語・単語・練習</h1>
  <var-divider />
  <var-chip size="small" type="info">技术栈：vue3、pinia、Typescript、varletUI、Vite</var-chip>

  <var-divider />
  <var-space direction="column">
    <var-space align="center">
      <span class="title">2023-05-20</span>
      <var-chip size="mini">v1.1.0</var-chip>
    </var-space>
    <span class="title">功能:</span>
    <ol>
      <li>新增新标日按课学习单词功能，支持艾宾浩斯记忆法</li>
      <li>新增学习单词学习检测功能，5组单词配对</li>
    </ol>
  </var-space>

  <var-divider />
  <var-space direction="column">
    <var-space align="center">
      <span class="title">2023-03-22</span>
      <var-chip size="mini">v1.0.0</var-chip>
    </var-space>
    <span class="title">功能:</span>
    <ol>
      <li>新标日初级全量单词，按单元/课节练习拼写</li>
      <li>拼写支持汉字和假名双正确判断，支持语音自动播放</li>
      <li>练习打分评级分F-SSS，根据正确率和单词的熟悉程度综合给出评价</li>
      <li>20次最近练习日志记录，可查看每个单词的错误和拼写时间</li>
    </ol>
  </var-space>

</template>


<style scoped>
h4 {
  margin: 0;
  color: #607d8b;
}
.title {
  color: #666;
  font-size: 14px;
  font-weight: bold;
}
ol {
  font-family: "Andale Mono";
  font-size: 14px;
  color: #9e9e9e;
  margin: 0;
}
ol li {
  padding-left: 0;
}
</style>