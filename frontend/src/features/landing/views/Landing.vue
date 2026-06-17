<script setup lang="ts">
import { RouterLink } from "vue-router";
</script>

<template>
    <div
        class="container py-4 d-flex align-items-center justify-content-center landing-wrapper"
    >
        <section
            class="p-4 p-md-5 rounded-3 bg-dark border border-secondary text-center w-100"
        >
            <h2 class="display-6 fw-bold">Don't miss the next drop</h2>
            <p class="lead text-light mb-4">
                Create a free account to grab tickets the moment sales go live.
            </p>
            <div
                class="d-flex flex-column flex-sm-row justify-content-center gap-3"
            >
                <RouterLink
                    :to="{ name: 'register' }"
                    class="btn btn-primary btn-lg px-4"
                >
                    Sign up to buy tickets
                </RouterLink>
                <RouterLink
                    :to="{ name: 'events' }"
                    class="btn btn-outline-light btn-lg px-4"
                >
                    Browse events
                </RouterLink>
            </div>
            <p class="mt-4 mb-0">
                <small class="text-muted">
                    Hosting an event?
                    <RouterLink
                        :to="{ name: 'organizer-register' }"
                        class="text-success text-decoration-underline"
                    >
                        Register
                    </RouterLink>
                    or
                    <RouterLink
                        :to="{ name: 'organizer-login' }"
                        class="text-success text-decoration-underline"
                    >
                        Login
                    </RouterLink>
                    as an Organizer
                </small>
            </p>
        </section>
    </div>
</template>

<style scoped>
.landing-wrapper {
    min-height: calc(100vh - 66px);
}
</style>
