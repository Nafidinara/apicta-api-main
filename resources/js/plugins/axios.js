// eslint-disable-next-line regex/invalid
import axios from 'axios'

const axiosIns = axios.create({
// You can add your headers here
// ================================
// baseURL: 'https://some-domain.com/api/',
// timeout: 1000,
// headers: {'X-Custom-Header': 'foobar'}
})


// ℹ️ Add request interceptor to send the authorization header on each subsequent request after login
axiosIns.interceptors.request.use(config => {
  // Retrieve token from localStorage
  const token = localStorage.getItem('access_token')

  // If token is found
  if (token) {
    // Get request headers and if headers is undefined assign blank object
    config.headers = config.headers || {}

    // Set authorization header
    config.headers.Authorization = token ? `Bearer ${token}` : ''
  }

  // Return modified config
  return config
})

// ℹ️ Add response interceptor to handle 401 response
// axiosIns.interceptors.response.use(response => {
//   return response
// }, error => {
//   // Handle error
//   if (error.response.status === 401) {
//     // ℹ️ Logout user and redirect to login page
//     // Remove "userData" from localStorage
//     // localStorage.removeItem('userData')

//     // Remove "accessToken" from localStorage
//     localStorage.removeItem('access_token')

//     // If 401 response returned from api
//     router.push('/login')
//   }
//   else {
//     return Promise.reject(error)
//   }
// })

export default axiosIns
