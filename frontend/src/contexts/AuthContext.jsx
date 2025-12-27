import React, { createContext, useState, useContext, useEffect } from 'react'
import { login as apiLogin, register as apiRegister, getCurrentUser } from '../services/api'

const AuthContext = createContext(null)

export const useAuth = () => {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const token = localStorage.getItem('token')
    if (token) {
      loadUser()
    } else {
      setLoading(false)
    }
  }, [])

  const loadUser = async () => {
    try {
      const userData = await getCurrentUser()
      setUser(userData.user)
    } catch (error) {
      localStorage.removeItem('token')
    } finally {
      setLoading(false)
    }
  }

  const login = async (email, password) => {
    const response = await apiLogin(email, password)
    localStorage.setItem('token', response.token)
    setUser(response.user)
    return response
  }

  const register = async (email, password, firstName, lastName, phone) => {
    const response = await apiRegister(email, password, firstName, lastName, phone)
    localStorage.setItem('token', response.token)
    setUser(response.user)
    return response
  }

  const logout = () => {
    localStorage.removeItem('token')
    setUser(null)
  }

  const isAdmin = () => {
    return user?.role === 'admin'
  }

  const value = {
    user,
    loading,
    login,
    register,
    logout,
    isAdmin
  }

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}
