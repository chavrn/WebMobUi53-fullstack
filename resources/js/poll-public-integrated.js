import './bootstrap'
import { createApp } from 'vue'
import PublicPollView from './PublicPollView.vue'

// Récupération de l'élément root
const el = document.getElementById('app')

if (!el) {
    console.error('❌ #app introuvable – Vue ne peut pas se monter')
} else {
    // Les props viennent du Blade via data-props
    const props = JSON.parse(el.dataset.props)

    console.log('✅ poll-public-integrated.js chargé')
    console.log('📦 Props reçues :', props)

    createApp(PublicPollView, props).mount('#app')
}

