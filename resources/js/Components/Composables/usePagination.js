// resources/js/Composables/usePagination.js
import { ref } from 'vue'
import axios from 'axios'
import { useAlert } from '@/Components/Composables/useAlert'

export default function usePagination(initialUrl = null) {
    const data = ref([])
    const meta = ref({})
    const links = ref([])
    const loading = ref(false)

    const { toastError } = useAlert()

   const fetchPage = async (url = initialUrl) => {
    if (!url) return

    loading.value = true

    try {
        const response = await axios.get(url)

        const resource = response.data

        if (resource?.data && resource?.meta && resource?.links) {
            data.value = resource.data
            meta.value = resource.meta 
            links.value = normalizePaginationLinks(resource.links)
            console.log('[DEBUG] parsed', {
                data: data.value,
                meta: meta.value,
                links: links.value
            })
        } else {
            console.warn('[DEBUG] Formato inesperado:', response.data)
            toastError('Unexpected response format.')
        }
    } catch (err) {
        console.error('[DEBUG] Error en axios:', err)
        toastError('Error fetching data.')
    } finally {
        loading.value = false
    }
}

function normalizePaginationLinks(linksObject) {
    const pages = []

    // Simula botones prev, current, next usando el objeto de links
    if (linksObject.prev) {
        pages.push({ label: '&laquo; Previous', url: linksObject.prev, active: false })
    }

    pages.push({ label: 'Page ' + meta.value.current_page, url: null, active: true })

    if (linksObject.next) {
        pages.push({ label: 'Next &raquo;', url: linksObject.next, active: false })
    }

    return pages
}



    return {
        data,
        meta,
        links,
        loading,
        fetchPage,
    }
}
