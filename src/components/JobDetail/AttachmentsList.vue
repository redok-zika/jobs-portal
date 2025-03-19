<template>
  <div v-if="attachments.length > 0" class="mt-3">
    <h6 class="mb-2">Nahrané přílohy:</h6>
    <ul class="list-group">
      <li
        v-for="(attachment, index) in attachments"
        :key="index"
        class="list-group-item d-flex justify-content-between align-items-center"
      >
        <span>{{ attachment.filename }}</span>
        <button type="button" class="btn btn-sm btn-danger" @click="$emit('remove', index)">
          Odstranit
        </button>
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import type { Attachment } from '../../types/forms'

export default defineComponent({
  name: 'AttachmentsList',
  props: {
    attachments: {
      type: Array as () => Attachment[],
      required: true
    }
  },
  emits: ['remove']
})
</script>
