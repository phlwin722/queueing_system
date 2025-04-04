import axios from 'axios'

  axios.defaults.baseURL = process.env.NODE_ENV === 'production'
    ? window.location.origin
    : 'http://192.168.0.164:8000'

export { axios }