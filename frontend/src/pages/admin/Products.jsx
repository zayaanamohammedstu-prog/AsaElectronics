import React, { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { getProducts, createProduct, updateProduct, deleteProduct, getCategories } from '../../services/api'
import './Dashboard.css'

const AdminProducts = () => {
  const [products, setProducts] = useState([])
  const [categories, setCategories] = useState([])
  const [loading, setLoading] = useState(true)
  const [showForm, setShowForm] = useState(false)
  const [editingProduct, setEditingProduct] = useState(null)
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    price: '',
    stock_quantity: '',
    category_id: '',
    image_url: '',
    sku: '',
    is_active: 1
  })

  useEffect(() => {
    loadData()
  }, [])

  const loadData = async () => {
    try {
      const [productsData, categoriesData] = await Promise.all([
        getProducts({ limit: 1000 }),
        getCategories()
      ])
      setProducts(productsData.products || [])
      setCategories(categoriesData.categories || [])
    } catch (error) {
      console.error('Failed to load data:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    try {
      if (editingProduct) {
        await updateProduct(editingProduct.id, formData)
      } else {
        await createProduct(formData)
      }
      setShowForm(false)
      setEditingProduct(null)
      resetForm()
      loadData()
    } catch (error) {
      alert('Failed to save product: ' + (error.response?.data?.error || error.message))
    }
  }

  const handleEdit = (product) => {
    setEditingProduct(product)
    setFormData({
      name: product.name,
      description: product.description || '',
      price: product.price,
      stock_quantity: product.stock_quantity,
      category_id: product.category_id || '',
      image_url: product.image_url || '',
      sku: product.sku,
      is_active: product.is_active
    })
    setShowForm(true)
  }

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this product?')) {
      try {
        await deleteProduct(id)
        loadData()
      } catch (error) {
        alert('Failed to delete product')
      }
    }
  }

  const resetForm = () => {
    setFormData({
      name: '',
      description: '',
      price: '',
      stock_quantity: '',
      category_id: '',
      image_url: '',
      sku: '',
      is_active: 1
    })
  }

  if (loading) {
    return <div className="loading">Loading...</div>
  }

  return (
    <div className="admin-dashboard">
      <div className="container">
        <h1>Products Management</h1>

        <div className="admin-nav">
          <Link to="/admin" className="btn btn-secondary">Dashboard</Link>
          <Link to="/admin/products" className="btn btn-primary">Products</Link>
          <Link to="/admin/orders" className="btn btn-secondary">Orders</Link>
          <Link to="/admin/users" className="btn btn-secondary">Users</Link>
        </div>

        <button
          onClick={() => {
            setShowForm(!showForm)
            setEditingProduct(null)
            resetForm()
          }}
          className="btn btn-primary"
          style={{ marginBottom: '20px' }}
        >
          {showForm ? 'Cancel' : 'Add New Product'}
        </button>

        {showForm && (
          <div className="card" style={{ marginBottom: '30px' }}>
            <h2>{editingProduct ? 'Edit Product' : 'Add New Product'}</h2>
            <form onSubmit={handleSubmit}>
              <div className="grid grid-2">
                <div className="input-group">
                  <label>Name *</label>
                  <input
                    type="text"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    required
                  />
                </div>
                <div className="input-group">
                  <label>Price *</label>
                  <input
                    type="number"
                    step="0.01"
                    value={formData.price}
                    onChange={(e) => setFormData({ ...formData, price: e.target.value })}
                    required
                  />
                </div>
                <div className="input-group">
                  <label>Stock Quantity *</label>
                  <input
                    type="number"
                    value={formData.stock_quantity}
                    onChange={(e) => setFormData({ ...formData, stock_quantity: e.target.value })}
                    required
                  />
                </div>
                <div className="input-group">
                  <label>Category</label>
                  <select
                    value={formData.category_id}
                    onChange={(e) => setFormData({ ...formData, category_id: e.target.value })}
                  >
                    <option value="">Select Category</option>
                    {categories.map(cat => (
                      <option key={cat.id} value={cat.id}>{cat.name}</option>
                    ))}
                  </select>
                </div>
                <div className="input-group">
                  <label>SKU</label>
                  <input
                    type="text"
                    value={formData.sku}
                    onChange={(e) => setFormData({ ...formData, sku: e.target.value })}
                  />
                </div>
                <div className="input-group">
                  <label>Image URL</label>
                  <input
                    type="url"
                    value={formData.image_url}
                    onChange={(e) => setFormData({ ...formData, image_url: e.target.value })}
                  />
                </div>
              </div>
              <div className="input-group">
                <label>Description</label>
                <textarea
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  rows="4"
                />
              </div>
              <div className="input-group">
                <label>
                  <input
                    type="checkbox"
                    checked={formData.is_active == 1}
                    onChange={(e) => setFormData({ ...formData, is_active: e.target.checked ? 1 : 0 })}
                  />
                  {' '}Active
                </label>
              </div>
              <button type="submit" className="btn btn-primary">
                {editingProduct ? 'Update Product' : 'Create Product'}
              </button>
            </form>
          </div>
        )}

        <div className="admin-table">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>SKU</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {products.map(product => (
                <tr key={product.id}>
                  <td>{product.id}</td>
                  <td>{product.name}</td>
                  <td>{product.category_name || 'N/A'}</td>
                  <td>${parseFloat(product.price).toFixed(2)}</td>
                  <td>{product.stock_quantity}</td>
                  <td>{product.sku}</td>
                  <td>{product.is_active ? 'Active' : 'Inactive'}</td>
                  <td>
                    <button onClick={() => handleEdit(product)} className="btn btn-secondary" style={{ marginRight: '5px' }}>
                      Edit
                    </button>
                    <button onClick={() => handleDelete(product.id)} className="btn btn-danger">
                      Delete
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}

export default AdminProducts
