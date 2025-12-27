import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json'
  }
})

// Add token to requests
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Auth API
export const login = async (email, password) => {
  const response = await api.post('/auth.php/login', { email, password })
  return response.data
}

export const register = async (email, password, firstName, lastName, phone) => {
  const response = await api.post('/auth.php/register', {
    email,
    password,
    first_name: firstName,
    last_name: lastName,
    phone
  })
  return response.data
}

export const getCurrentUser = async () => {
  const response = await api.get('/auth.php/me')
  return response.data
}

// Products API
export const getProducts = async (filters = {}) => {
  const response = await api.get('/products.php/products', { params: filters })
  return response.data
}

export const getProduct = async (id) => {
  const response = await api.get(`/products.php/${id}`)
  return response.data
}

export const createProduct = async (productData) => {
  const response = await api.post('/products.php/products', productData)
  return response.data
}

export const updateProduct = async (id, productData) => {
  const response = await api.put(`/products.php/${id}`, productData)
  return response.data
}

export const deleteProduct = async (id) => {
  const response = await api.delete(`/products.php/${id}`)
  return response.data
}

// Categories API
export const getCategories = async () => {
  const response = await api.get('/categories.php')
  return response.data
}

// Orders API
export const createOrder = async (orderData) => {
  const response = await api.post('/orders.php/orders', orderData)
  return response.data
}

export const getOrders = async (filters = {}) => {
  const response = await api.get('/orders.php/orders', { params: filters })
  return response.data
}

export const getOrder = async (id) => {
  const response = await api.get(`/orders.php/${id}`)
  return response.data
}

export const updateOrderStatus = async (id, status) => {
  const response = await api.put(`/orders.php/${id}/status`, { status })
  return response.data
}

export const getAnalytics = async (startDate, endDate) => {
  const response = await api.get('/orders.php/analytics', {
    params: { start_date: startDate, end_date: endDate }
  })
  return response.data
}

// Payment API
export const initializePayment = async (amount, email, orderId, callbackUrl) => {
  const response = await api.post('/payments.php/initialize', {
    amount,
    email,
    order_id: orderId,
    callback_url: callbackUrl
  })
  return response.data
}

export const verifyPayment = async (reference) => {
  const response = await api.get('/payments.php/verify', {
    params: { reference }
  })
  return response.data
}

export default api
