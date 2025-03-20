<template>
  <div class="mb-4">
    <h6 class="mb-3">Sociální sítě</h6>

    <div class="mb-3">
      <label for="linkedin" class="form-label">LinkedIn profil</label>
      <input
        id="linkedin"
        type="url"
        class="form-control"
        :value="linkedin"
        placeholder="https://linkedin.com/in/vas-profil"
        @input="$emit('update:linkedin', ($event.target as HTMLInputElement).value)"
      />
    </div>

    <div class="mb-3">
      <label for="facebook" class="form-label">Facebook profil</label>
      <input
        id="facebook"
        type="url"
        class="form-control"
        :value="facebook"
        placeholder="https://facebook.com/vas.profil"
        @input="$emit('update:facebook', ($event.target as HTMLInputElement).value)"
      />
    </div>

    <div class="mb-3">
      <label for="twitter" class="form-label">Twitter profil</label>
      <input
        id="twitter"
        type="url"
        class="form-control"
        :value="twitter"
        placeholder="https://twitter.com/vasprofil"
        @input="$emit('update:twitter', ($event.target as HTMLInputElement).value)"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'SocialMediaSection',
  props: {
    linkedin: {
      type: String,
      default: ''
    },
    facebook: {
      type: String,
      default: ''
    },
    twitter: {
      type: String,
      default: ''
    }
  },
  emits: ['update:linkedin', 'update:skype', 'update:facebook', 'update:twitter']
})
</script>
