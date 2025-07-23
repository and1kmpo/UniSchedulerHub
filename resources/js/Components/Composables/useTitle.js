export const useTitle = (title) => {
    const appName = import.meta.env.VITE_APP_NAME || 'Sistema'
    const fullTitle = `${title} - ${appName}`
    document.title = fullTitle
}
