<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { apiFetch } from '@/shared/api/http'
import type { OrderStatus, OrderWithTicket } from '@/features/orders/types/order'

const orders = ref<OrderWithTicket[]>([])
const isLoading = ref(true)
const error = ref<string | null>(null)
const selectedOrder = ref<OrderWithTicket | null>(null)

function openTicket(order: OrderWithTicket): void {
  selectedOrder.value = order
}

function closeTicket(): void {
  selectedOrder.value = null
}

const now = ref(Date.now())
const timer = setInterval(() => {
  now.value = Date.now()
}, 1000)
onUnmounted(() => clearInterval(timer))

function effectiveStatus(order: OrderWithTicket): OrderStatus {
  if (order.status === 'pending' && new Date(order.expires_at).getTime() <= now.value) {
    return 'expired'
  }

  return order.status
}

const dateFormatter = new Intl.DateTimeFormat(undefined, {
  dateStyle: 'medium',
  timeStyle: 'short',
})

function formatDate(value: string): string {
  return dateFormatter.format(new Date(value))
}

function formatEventDate(value: string | null): string {
  if (!value) {
    return 'TBA'
  }

  return dateFormatter.format(new Date(value))
}

function statusBadgeClass(status: OrderStatus): string {
  if (status === 'pending') {
    return 'bg-warning text-dark'
  }

  if (status === 'confirmed') {
    return 'bg-success'
  }

  return 'bg-secondary'
}

function isInactive(status: OrderStatus): boolean {
  return status === 'expired' || status === 'cancelled'
}

onMounted(async () => {
  try {
    const response = await apiFetch('/orders')

    if (!response.ok) {
      error.value = `Failed to load your orders (${response.status}).`
      return
    }

    orders.value = await response.json()
  } catch {
    error.value = 'Unable to reach the server. Please try again later.'
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="container py-4">
    <h1 class="mb-4">My Orders</h1>

    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading…</span>
      </div>
    </div>

    <div v-else-if="error" class="alert alert-danger" role="alert">
      {{ error }}
    </div>

    <div v-else-if="orders.length === 0" class="alert alert-info" role="alert">
      You haven't reserved any tickets yet.
      <RouterLink :to="{ name: 'events' }" class="alert-link">Browse events</RouterLink>.
    </div>

    <div v-else>
      <div
        v-for="order in orders"
        :key="order.id"
        class="card mb-3"
        :class="{ 'opacity-50': isInactive(effectiveStatus(order)) }"
      >
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <h5 class="card-title mb-1">{{ order.ticket.event.title }}</h5>
            <p class="card-text text-muted mb-2">
              Reserved until: {{ formatDate(order.expires_at) }}
            </p>
            <span class="badge" :class="statusBadgeClass(effectiveStatus(order))">
              {{ effectiveStatus(order) }}
            </span>
          </div>

          <div>
            <RouterLink
              v-if="effectiveStatus(order) === 'pending'"
              class="btn btn-warning"
              :to="{ name: 'order-checkout', params: { id: order.id } }"
            >
              Complete Payment
            </RouterLink>

            <button
              v-else-if="effectiveStatus(order) === 'confirmed'"
              type="button"
              class="btn btn-success"
              @click="openTicket(order)"
            >
              View Ticket
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal fade"
      :class="{ show: selectedOrder }"
      :style="{ display: selectedOrder ? 'block' : 'none' }"
      tabindex="-1"
      role="dialog"
      :aria-hidden="!selectedOrder"
      @click.self="closeTicket"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div v-if="selectedOrder" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Digital Ticket</h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              aria-label="Close"
              @click="closeTicket"
            ></button>
          </div>
          <div class="modal-body text-center">
            <h4 class="mb-1">{{ selectedOrder.ticket.event.title }}</h4>
            <p class="text-muted mb-3">
              {{ formatEventDate(selectedOrder.ticket.event.sale_starts_at) }}
            </p>
            <img
              :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=ticket-${selectedOrder.ticket.id}`"
              alt="Ticket QR code"
              width="150"
              height="150"
              class="mb-3"
            />
            <p class="mb-0">
              <span class="text-muted">Ticket ID:</span>
              <strong>#{{ selectedOrder.ticket.id }}</strong>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeTicket">Close</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="selectedOrder" class="modal-backdrop fade show" @click="closeTicket"></div>
  </div>
</template>
