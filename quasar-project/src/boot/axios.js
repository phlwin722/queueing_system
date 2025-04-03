import axios from 'axios'

  axios.defaults.baseURL = process.env.NODE_ENV === 'production'
    ? window.location.origin
    : 'http://127.0.0.1:8003'

export { axios }