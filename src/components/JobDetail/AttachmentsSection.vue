<template>
  <div class="card mb-3">
    <div class="card-body">
      <h6 class="card-subtitle mb-3">Přílohy</h6>

      <div class="mb-3">
        <label for="cv" class="form-label">Životopis</label>
        <input
          id="cv"
          type="file"
          class="form-control"
          accept=".pdf,.doc,.docx"
          @change="handleFileUpload($event, 2)"
        />
        <div class="form-text">Podporované formáty: PDF, DOC, DOCX</div>
      </div>

      <div class="mb-3">
        <label for="photo" class="form-label">Fotografie</label>
        <input
          id="photo"
          type="file"
          class="form-control"
          accept="image/*"
          @change="handleFileUpload($event, 4)"
        />
        <div class="form-text">Podporované formáty: JPG, PNG</div>
      </div>

      <div class="mb-3">
        <label for="other" class="form-label">Další příloha</label>
        <input id="other" type="file" class="form-control" @change="handleFileUpload($event, 1)" />
      </div>

      <AttachmentsList :attachments="attachments" @remove="removeAttachment" />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import AttachmentsList from './AttachmentsList.vue'
import type { Attachment } from '../../types/forms'

export default defineComponent({
  name: 'AttachmentsSection',
  components: {
    AttachmentsList
  },
  props: {
    attachments: {
      type: Array as () => Attachment[],
      required: true
    }
  },
  emits: ['update:attachments', 'error'],
  setup(props, { emit }) {
    const MAX_FILE_SIZE = 2 * 1024 * 1024 // 2MB
    const ALLOWED_EXTENSIONS = [
      '.pdf',
      '.doc',
      '.docx', // Documents
      '.jpg',
      '.jpeg',
      '.png', // Images
      '.txt',
      '.rtf', // Text files
      '.xls',
      '.xlsx', // Excel files
      '.ppt',
      '.pptx', // PowerPoint files
      '.zip',
      '.rar' // Archives
    ]

    const handleFileUpload = async (event: Event, type: number) => {
      const input = event.target as HTMLInputElement
      if (!input.files?.length) return

      const file = input.files[0]
      const extension = '.' + file.name.split('.').pop()?.toLowerCase()

      if (!ALLOWED_EXTENSIONS.includes(extension)) {
        emit('error', 'Nepodporovaný formát souboru')
        input.value = ''
        return
      }

      if (file.size > MAX_FILE_SIZE) {
        emit('error', 'Soubor je příliš velký. Maximální velikost je 2 MB.')
        input.value = ''
        return
      }

      try {
        const base64 = await new Promise<string>((resolve, reject) => {
          const reader = new FileReader()
          reader.onload = () => resolve(reader.result as string)
          reader.onerror = reject
          reader.readAsDataURL(file)
        })

        const newAttachments = [...props.attachments]
        newAttachments.push({
          base64: base64.split(',')[1],
          filename: file.name,
          type
        })

        emit('update:attachments', newAttachments)
        input.value = ''
      } catch (e) {
        emit('error', 'Nepodařilo se nahrát soubor')
      }
    }

    const removeAttachment = (index: number) => {
      const newAttachments = [...props.attachments]
      newAttachments.splice(index, 1)
      emit('update:attachments', newAttachments)
    }

    return {
      handleFileUpload,
      removeAttachment
    }
  }
})
</script>
