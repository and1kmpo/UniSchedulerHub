export const useTitle = (title) => {
    const appName = import.meta.env.VITE_APP_NAME || 'TARRAYA'
    const fullTitle = `${title} - ${appName}`
    document.title = fullTitle
}
