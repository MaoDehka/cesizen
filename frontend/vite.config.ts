import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  define: {
    __GIT_COMMIT__: JSON.stringify(process.env.VITE_GIT_COMMIT?.substring(0, 7) || 'dev'),
    __GIT_BRANCH__: JSON.stringify(process.env.VITE_GIT_BRANCH || 'local'),
    __BUILD_TIME__: JSON.stringify(new Date().toISOString())
  }
})