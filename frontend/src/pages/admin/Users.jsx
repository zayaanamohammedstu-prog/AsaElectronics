import React, { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../../services/api'
import './Dashboard.css'

const AdminUsers = () => {
  const [users, setUsers] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    loadUsers()
  }, [])

  const loadUsers = async () => {
    try {
      const response = await api.get('/auth.php/users')
      setUsers(response.data.users || [])
    } catch (error) {
      console.error('Failed to load users:', error)
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return <div className="loading">Loading...</div>
  }

  return (
    <div className="admin-dashboard">
      <div className="container">
        <h1>Users Management</h1>

        <div className="admin-nav">
          <Link to="/admin" className="btn btn-secondary">Dashboard</Link>
          <Link to="/admin/products" className="btn btn-secondary">Products</Link>
          <Link to="/admin/orders" className="btn btn-secondary">Orders</Link>
          <Link to="/admin/users" className="btn btn-primary">Users</Link>
        </div>

        <div className="admin-table">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Registered</th>
              </tr>
            </thead>
            <tbody>
              {users.map(user => (
                <tr key={user.id}>
                  <td>{user.id}</td>
                  <td>{user.first_name} {user.last_name}</td>
                  <td>{user.email}</td>
                  <td>{user.phone || 'N/A'}</td>
                  <td>
                    <span style={{
                      padding: '5px 10px',
                      borderRadius: '4px',
                      background: user.role === 'admin' ? 'var(--primary-color)' : 'var(--bg-secondary)',
                      color: user.role === 'admin' ? 'white' : 'var(--text-color)',
                      fontSize: '12px'
                    }}>
                      {user.role}
                    </span>
                  </td>
                  <td>{new Date(user.created_at).toLocaleDateString()}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {users.length === 0 && (
          <p style={{ textAlign: 'center', padding: '40px' }}>No users found</p>
        )}
      </div>
    </div>
  )
}

export default AdminUsers
