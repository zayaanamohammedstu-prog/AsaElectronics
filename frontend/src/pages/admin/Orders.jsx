import React, { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { getOrders, updateOrderStatus } from '../../services/api'
import './Dashboard.css'

const AdminOrders = () => {
  const [orders, setOrders] = useState([])
  const [loading, setLoading] = useState(true)
  const [filter, setFilter] = useState('all')

  useEffect(() => {
    loadOrders()
  }, [filter])

  const loadOrders = async () => {
    try {
      const filters = { limit: 1000 }
      if (filter !== 'all') {
        filters.status = filter
      }
      const data = await getOrders(filters)
      setOrders(data.orders || [])
    } catch (error) {
      console.error('Failed to load orders:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleStatusChange = async (orderId, newStatus) => {
    try {
      await updateOrderStatus(orderId, newStatus)
      loadOrders()
    } catch (error) {
      alert('Failed to update order status')
    }
  }

  if (loading) {
    return <div className="loading">Loading...</div>
  }

  return (
    <div className="admin-dashboard">
      <div className="container">
        <h1>Orders Management</h1>

        <div className="admin-nav">
          <Link to="/admin" className="btn btn-secondary">Dashboard</Link>
          <Link to="/admin/products" className="btn btn-secondary">Products</Link>
          <Link to="/admin/orders" className="btn btn-primary">Orders</Link>
          <Link to="/admin/users" className="btn btn-secondary">Users</Link>
        </div>

        <div style={{ marginBottom: '20px' }}>
          <label style={{ marginRight: '10px' }}>Filter by status:</label>
          <select
            value={filter}
            onChange={(e) => setFilter(e.target.value)}
            style={{ padding: '10px', borderRadius: '6px', border: '1px solid var(--border-color)' }}
          >
            <option value="all">All Orders</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <div className="admin-table">
          <table>
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {orders.map(order => (
                <tr key={order.id}>
                  <td>#{order.id}</td>
                  <td>{order.first_name} {order.last_name}<br/><small>{order.email}</small></td>
                  <td>${parseFloat(order.total_amount).toFixed(2)}</td>
                  <td>
                    <select
                      value={order.status}
                      onChange={(e) => handleStatusChange(order.id, e.target.value)}
                      style={{ padding: '5px', borderRadius: '4px', border: '1px solid var(--border-color)' }}
                    >
                      <option value="pending">Pending</option>
                      <option value="processing">Processing</option>
                      <option value="shipped">Shipped</option>
                      <option value="delivered">Delivered</option>
                      <option value="cancelled">Cancelled</option>
                    </select>
                  </td>
                  <td>
                    <span style={{
                      padding: '5px 10px',
                      borderRadius: '4px',
                      background: order.payment_status === 'completed' ? 'var(--success-color)' : 'var(--warning-color)',
                      color: 'white',
                      fontSize: '12px'
                    }}>
                      {order.payment_status}
                    </span>
                  </td>
                  <td>{new Date(order.created_at).toLocaleDateString()}</td>
                  <td>
                    <Link to={`/admin/orders/${order.id}`} className="btn btn-secondary">
                      View Details
                    </Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {orders.length === 0 && (
          <p style={{ textAlign: 'center', padding: '40px' }}>No orders found</p>
        )}
      </div>
    </div>
  )
}

export default AdminOrders
