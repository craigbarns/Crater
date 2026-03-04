import axios from 'axios'
import Ls from '@/scripts/services/ls.js'

window.Ls = Ls
window.axios = axios
axios.defaults.withCredentials = true

axios.defaults.headers.common = {
  'X-Requested-With': 'XMLHttpRequest',
}

/**
 * Interceptors
 */

axios.interceptors.request.use(function (config) {
  // Pass selected company to header on all requests
  const companyId = Ls.get('selectedCompany')

  const authToken = Ls.get('auth.token')

  if (authToken) {
    config.headers.common.Authorization = authToken
  }

  if (companyId) {
    config.headers.common['company'] = companyId
  }

  return config
})

axios.interceptors.response.use(
  (response) => response,
  (error) => {
    // Catch server errors (500+) that may not be handled by individual store actions
    if (error.response && error.response.status >= 500) {
      console.error('[Server Error]', error.response.status, error.response.config?.url)
    }
    return Promise.reject(error)
  }
)
