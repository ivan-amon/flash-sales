<script setup lang="ts">
import HelloWorld from './components/HelloWorld.vue'
import logo from './assets/logo.svg'
</script>

<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img :src="logo" alt="Flash Sales logo" width="32" height="32" class="me-2" />
        Flash Sales
      </a>
    </div>
  </nav>

  <HelloWorld />
</template>
