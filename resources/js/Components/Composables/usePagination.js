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
        } else {
            toastError('The academic data response could not be synchronized.')
        }
    } catch {
        toastError('The academic data could not be loaded.')
    } finally {
        loading.value = false
    }
}

function normalizePaginationLinks(linksObject) {
    const pages = []

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
