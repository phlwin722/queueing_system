import axios from "axios"; 

const backendUrl = "http://localhost:8000";

const axiosClient = axios.create({
  baseURL: `${backendUrl}`
});

axiosClient.interceptors.request.use((config => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}));
 

export { axiosClient }; 