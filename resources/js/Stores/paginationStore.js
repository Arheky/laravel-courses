import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

export const paginationStore = reactive({
  links: [],
  currentUrl: null,
  entitySetter: null,
  entityName: null,

  /**
   * Sayfalama linklerini store’a kaydet
   */
  setLinks(data) {
    this.links = data || []
  },

  /**
   * Store’u başlat (hangi entity ile çalışacağını tanımlar)
   */
  init({ links, url, onDataUpdate }) {
    this.links = links || []
    this.currentUrl = url || null
    this.entitySetter = onDataUpdate || null
  },

  /**
   * Yeni sayfaya git ve reactive entity’yi güncelle
   */
  goTo(url) {
    if (!url || !this.entitySetter) return

    // Önce sayfa içerik alanına fade-out animasyonu uygula
    fadeOutPage()

    router.visit(url, {
      preserveState: true,
      replace: true,
      onSuccess: (page) => {
        // courses / lessons / students entity’sini bul
        const entityName = Object.keys(page.props).find((k) =>
          ['courses', 'lessons', 'students'].includes(k)
        )

        if (entityName) {
          const entity = page.props[entityName]
          const data = Array.isArray(entity) ? entity : entity.data || []
          const links = entity.links || []

          this.entitySetter(data)
          this.links = links
          this.entityName = entityName

          // Sayfa değiştiğinde otomatik olarak yukarı kaydır
          scrollToTopSmooth(() => fadeInPage())
        }
      },
    })
  },
})

/**
 * Yumuşak kaydırma efekti
 */
function scrollToTopSmooth(callback) {
  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
  // Kaydırma bitince callback çağır
  setTimeout(() => callback && callback(), 350)
}

/**
 * Fade animasyonları
 */
function fadeOutPage() {
  const main = document.querySelector('main')
  if (!main) return
  main.style.opacity = 0
  main.style.transition = 'opacity 0.35s ease-in-out'
}

function fadeInPage() {
  const main = document.querySelector('main')
  if (!main) return
  main.style.opacity = 1
  main.style.transition = 'opacity 0.45s ease-in-out'
}
