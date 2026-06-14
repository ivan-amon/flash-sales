<script setup lang="ts">
import { onBeforeUnmount, ref } from 'vue'
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'

const ASPECT_RATIO = 4 / 3
const ASPECT_TOLERANCE = 0.01
const MAX_FILE_BYTES = 2048 * 1024
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp']
const MAX_OUTPUT_WIDTH = 1600
const MAX_OUTPUT_HEIGHT = 1200

const props = defineProps<{
  modelValue: File | null
  error?: string[]
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: File | null): void
}>()

const fileInput = ref<HTMLInputElement | null>(null)
const cropperImage = ref<HTMLImageElement | null>(null)

const previewUrl = ref<string | null>(null)
const localError = ref<string | null>(null)
const isCropperOpen = ref(false)

let cropper: Cropper | null = null
let cropperSourceUrl: string | null = null
let pendingFileName = 'cover.jpg'
let pendingOutputType = 'image/jpeg'

function readDimensions(url: string): Promise<{ width: number; height: number }> {
  return new Promise((resolve, reject) => {
    const image = new Image()
    image.onload = () => {
      resolve({ width: image.naturalWidth, height: image.naturalHeight })
    }
    image.onerror = () => {
      reject(new Error('Could not read the selected image.'))
    }
    image.src = url
  })
}

function setPreview(file: File): void {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
  }
  previewUrl.value = URL.createObjectURL(file)
}

function outputTypeFor(file: File): string {
  return ALLOWED_TYPES.includes(file.type) ? file.type : 'image/jpeg'
}

async function onFileSelected(eventPayload: Event): Promise<void> {
  localError.value = null

  const input = eventPayload.target as HTMLInputElement
  const file = input.files?.[0]

  if (!file) {
    return
  }

  if (!ALLOWED_TYPES.includes(file.type)) {
    localError.value = 'The image must be a JPEG, PNG, or WebP file.'
    resetInputElement()
    return
  }

  if (file.size > MAX_FILE_BYTES) {
    localError.value = 'The image must be 2 MB or smaller.'
    resetInputElement()
    return
  }

  pendingFileName = file.name
  pendingOutputType = outputTypeFor(file)

  const sourceUrl = URL.createObjectURL(file)

  try {
    const { width, height } = await readDimensions(sourceUrl)
    const ratio = width / height

    if (Math.abs(ratio - ASPECT_RATIO) <= ASPECT_TOLERANCE) {
      URL.revokeObjectURL(sourceUrl)
      setPreview(file)
      emit('update:modelValue', file)
    } else {
      openCropper(sourceUrl)
    }
  } catch {
    URL.revokeObjectURL(sourceUrl)
    localError.value = 'Could not read the selected image. Please try another file.'
  } finally {
    resetInputElement()
  }
}

function openCropper(sourceUrl: string): void {
  cropperSourceUrl = sourceUrl
  isCropperOpen.value = true

  requestAnimationFrame(() => {
    if (!cropperImage.value) {
      return
    }

    cropperImage.value.src = sourceUrl
    cropper = new Cropper(cropperImage.value, {
      aspectRatio: ASPECT_RATIO,
      viewMode: 1,
      autoCropArea: 1,
      movable: true,
      zoomable: true,
      background: false,
    })
  })
}

function confirmCrop(): void {
  if (!cropper) {
    return
  }

  const canvas = cropper.getCroppedCanvas({
    maxWidth: MAX_OUTPUT_WIDTH,
    maxHeight: MAX_OUTPUT_HEIGHT,
    imageSmoothingQuality: 'high',
  })

  canvas.toBlob(
    (blob) => {
      if (!blob) {
        localError.value = 'Could not process the cropped image. Please try again.'
        return
      }

      const croppedFile = new File([blob], pendingFileName, { type: pendingOutputType })
      setPreview(croppedFile)
      emit('update:modelValue', croppedFile)
      closeCropper()
    },
    pendingOutputType,
    0.9,
  )
}

function closeCropper(): void {
  isCropperOpen.value = false

  if (cropper) {
    cropper.destroy()
    cropper = null
  }

  if (cropperSourceUrl) {
    URL.revokeObjectURL(cropperSourceUrl)
    cropperSourceUrl = null
  }
}

function removeImage(): void {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
  }

  localError.value = null
  emit('update:modelValue', null)
  resetInputElement()
}

function resetInputElement(): void {
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

onBeforeUnmount(() => {
  closeCropper()

  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
  }
})
</script>

<template>
  <div class="mb-4">
    <label for="cover_image" class="form-label">
      Cover image
      <span class="text-muted fw-normal">(Optional)</span>
    </label>

    <div v-if="previewUrl" class="mb-2">
      <img
        :src="previewUrl"
        alt="Cover preview"
        class="img-fluid rounded border"
        style="aspect-ratio: 4 / 3; object-fit: cover; width: 100%"
      />
    </div>

    <div class="d-flex gap-2">
      <input
        id="cover_image"
        ref="fileInput"
        type="file"
        accept="image/jpeg,image/png,image/webp"
        class="form-control"
        :class="{ 'is-invalid': error || localError }"
        @change="onFileSelected"
      />
      <button
        v-if="previewUrl"
        type="button"
        class="btn btn-outline-secondary"
        @click="removeImage"
      >
        Remove
      </button>
    </div>

    <div v-if="localError" class="invalid-feedback d-block">
      {{ localError }}
    </div>
    <div v-else-if="error" class="invalid-feedback d-block">
      {{ error[0] }}
    </div>

    <div
      class="modal fade"
      :class="{ show: isCropperOpen }"
      :style="{ display: isCropperOpen ? 'block' : 'none' }"
      tabindex="-1"
      role="dialog"
      aria-modal="true"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Crop to 4:3</h5>
            <button type="button" class="btn-close" aria-label="Close" @click="closeCropper"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted small">
              Adjust the frame so your image fits the required 4:3 horizontal format.
            </p>
            <div style="max-height: 60vh">
              <img ref="cropperImage" alt="Image to crop" style="max-width: 100%; display: block" />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" @click="closeCropper">
              Cancel
            </button>
            <button type="button" class="btn btn-primary" @click="confirmCrop">
              Crop &amp; use
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="isCropperOpen" class="modal-backdrop fade show"></div>
  </div>
</template>
