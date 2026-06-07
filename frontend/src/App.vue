<script setup lang="ts">
import logo from './assets/logo.svg'
</script>

<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <router-link class="navbar-brand d-flex align-items-center" to="/">
        <img :src="logo" alt="Flash Sales logo" width="32" height="32" class="me-2" />
        Flash Sales
      </router-link>
    </div>
  </nav>

  <router-view />
</template>
