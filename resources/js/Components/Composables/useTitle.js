export const useTitle = (title) => {
    const appName = import.meta.env.VITE_APP_NAME || 'UniSchedulerHub'
    const fullTitle = `${title} - ${appName}`
    document.title = fullTitle
}
