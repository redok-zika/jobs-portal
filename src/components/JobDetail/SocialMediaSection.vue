<template>
  <div class="mb-4">
    <h6 class="mb-3">Sociální sítě</h6>

    <div class="mb-3">
      <label for="linkedin" class="form-label">LinkedIn profil</label>
      <input
        type="url"
        class="form-control"
        id="linkedin"
        :value="linkedin"
        @input="$emit('update:linkedin', ($event.target as HTMLInputElement).value)"
        placeholder="https://linkedin.com/in/vas-profil"
      />
    </div>

    <div class="mb-3">
      <label for="facebook" class="form-label">Facebook profil</label>
      <input
        type="url"
        class="form-control"
        id="facebook"
        :value="facebook"
        @input="$emit('update:facebook', ($event.target as HTMLInputElement).value)"
        placeholder="https://facebook.com/vas.profil"
      />
    </div>

    <div class="mb-3">
      <label for="twitter" class="form-label">Twitter profil</label>
      <input
        type="url"
        class="form-control"
        id="twitter"
        :value="twitter"
        @input="$emit('update:twitter', ($event.target as HTMLInputElement).value)"
        placeholder="https://twitter.com/vasprofil"
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
