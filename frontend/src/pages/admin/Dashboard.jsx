import React, { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Line, Bar, Doughnut } from 'react-chartjs-2'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { getOrders, getProducts, getAnalytics } from '../../services/api'
import './Dashboard.css'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
)

const AdminDashboard = () => {
  const [stats, setStats] = useState({
    totalOrders: 0,
    totalProducts: 0,
    pendingOrders: 0,
    totalRevenue: 0
  })
  const [salesData, setSalesData] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    loadDashboardData()
  }, [])

  const loadDashboardData = async () => {
    try {
      const [ordersData, productsData, analyticsData] = await Promise.all([
        getOrders({ limit: 1000 }),
        getProducts({ limit: 1000 }),
        getAnalytics()
      ])

      const orders = ordersData.orders || []
      const products = productsData.products || []
      const analytics = analyticsData.analytics || []

      const totalRevenue = orders
        .filter(order => order.payment_status === 'completed')
        .reduce((sum, order) => sum + parseFloat(order.total_amount), 0)

      const pendingOrders = orders.filter(order => order.status === 'pending').length

      setStats({
        totalOrders: orders.length,
        totalProducts: products.length,
        pendingOrders,
        totalRevenue
      })

      setSalesData(analytics)
    } catch (error) {
      console.error('Failed to load dashboard data:', error)
    } finally {
      setLoading(false)
    }
  }

  const salesChartData = {
    labels: salesData.slice(0, 30).reverse().map(d => d.date),
    datasets: [
      {
        label: 'Daily Sales',
        data: salesData.slice(0, 30).reverse().map(d => parseFloat(d.total_sales || 0)),
        borderColor: 'rgb(37, 99, 235)',
        backgroundColor: 'rgba(37, 99, 235, 0.5)',
        tension: 0.4
      }
    ]
  }

  const ordersChartData = {
    labels: salesData.slice(0, 30).reverse().map(d => d.date),
    datasets: [
      {
        label: 'Orders',
        data: salesData.slice(0, 30).reverse().map(d => parseInt(d.order_count || 0)),
        backgroundColor: 'rgba(16, 185, 129, 0.7)'
      }
    ]
  }

  if (loading) {
    return <div className="loading">Loading dashboard...</div>
  }

  return (
    <div className="admin-dashboard">
      <div className="container">
        <h1>Admin Dashboard</h1>

        <div className="admin-nav">
          <Link to="/admin" className="btn btn-primary">Dashboard</Link>
          <Link to="/admin/products" className="btn btn-secondary">Products</Link>
          <Link to="/admin/orders" className="btn btn-secondary">Orders</Link>
          <Link to="/admin/users" className="btn btn-secondary">Users</Link>
        </div>

        <div className="stats-grid">
          <div className="stat-card">
            <h3>Total Orders</h3>
            <p className="stat-value">{stats.totalOrders}</p>
          </div>
          <div className="stat-card">
            <h3>Total Products</h3>
            <p className="stat-value">{stats.totalProducts}</p>
          </div>
          <div className="stat-card">
            <h3>Pending Orders</h3>
            <p className="stat-value">{stats.pendingOrders}</p>
          </div>
          <div className="stat-card">
            <h3>Total Revenue</h3>
            <p className="stat-value">${stats.totalRevenue.toFixed(2)}</p>
          </div>
        </div>

        <div className="charts-grid">
          <div className="chart-card">
            <h2>Sales Overview (Last 30 Days)</h2>
            <Line data={salesChartData} />
          </div>
          <div className="chart-card">
            <h2>Orders Overview (Last 30 Days)</h2>
            <Bar data={ordersChartData} />
          </div>
        </div>
      </div>
    </div>
  )
}

export default AdminDashboard
