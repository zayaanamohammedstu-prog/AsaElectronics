import React, { useEffect, useState } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { getProduct } from '../services/api'
import { useCart } from '../contexts/CartContext'
import { logProductView, logAddToCart } from '../utils/analytics'

const ProductDetailPage = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const [product, setProduct] = useState(null)
  const [quantity, setQuantity] = useState(1)
  const [loading, setLoading] = useState(true)
  const { addToCart } = useCart()

  useEffect(() => {
    loadProduct()
  }, [id])

  const loadProduct = async () => {
    try {
      const data = await getProduct(id)
      setProduct(data.product)
      logProductView(data.product.id, data.product.name)
    } catch (error) {
      console.error('Failed to load product:', error)
      alert('Product not found')
      navigate('/products')
    } finally {
      setLoading(false)
    }
  }

  const handleAddToCart = () => {
    addToCart(product, quantity)
    logAddToCart(product.id, product.name)
    // Using alert for simplicity - consider implementing a toast notification system
    alert(`${quantity} ${product.name}(s) added to cart!`)
  }

  if (loading) {
    return <div className="loading">Loading...</div>
  }

  if (!product) {
    return <div className="container">Product not found</div>
  }

  return (
    <div className="product-detail-page" style={{ padding: '40px 20px' }}>
      <div className="container">
        <div className="product-detail" style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '40px' }}>
          <div>
            {product.image_url ? (
              <img
                src={product.image_url}
                alt={product.name}
                style={{ width: '100%', borderRadius: '8px' }}
              />
            ) : (
              <div style={{ width: '100%', height: '400px', background: '#f0f0f0', borderRadius: '8px' }} />
            )}
          </div>
          <div>
            <h1>{product.name}</h1>
            <p className="price" style={{ fontSize: '32px', color: 'var(--primary-color)', margin: '20px 0' }}>
              ${parseFloat(product.price).toFixed(2)}
            </p>
            <p style={{ marginBottom: '20px' }}>{product.description}</p>
            
            <div style={{ marginBottom: '20px' }}>
              <strong>SKU:</strong> {product.sku}
            </div>
            
            <div style={{ marginBottom: '20px' }}>
              <strong>Category:</strong> {product.category_name || 'Uncategorized'}
            </div>
            
            <div style={{ marginBottom: '30px' }}>
              <strong>Availability:</strong>{' '}
              <span style={{ color: product.stock_quantity > 0 ? 'var(--success-color)' : 'var(--danger-color)' }}>
                {product.stock_quantity > 0 ? `${product.stock_quantity} in stock` : 'Out of Stock'}
              </span>
            </div>

            {product.stock_quantity > 0 && (
              <div>
                <div className="input-group" style={{ marginBottom: '20px' }}>
                  <label>Quantity</label>
                  <input
                    type="number"
                    min="1"
                    max={product.stock_quantity}
                    value={quantity}
                    onChange={(e) => setQuantity(Math.max(1, Math.min(product.stock_quantity, parseInt(e.target.value) || 1)))}
                    style={{ width: '100px' }}
                  />
                </div>
                <button onClick={handleAddToCart} className="btn btn-primary" style={{ width: '100%' }}>
                  Add to Cart
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}

export default ProductDetailPage
