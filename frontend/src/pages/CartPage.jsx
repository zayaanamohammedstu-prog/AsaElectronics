import React from 'react'
import { useNavigate } from 'react-router-dom'
import { useCart } from '../contexts/CartContext'
import { logRemoveFromCart } from '../utils/analytics'

const CartPage = () => {
  const navigate = useNavigate()
  const { cartItems, updateQuantity, removeFromCart, getTotal } = useCart()

  const handleRemove = (item) => {
    removeFromCart(item.id)
    logRemoveFromCart(item.id, item.name)
  }

  const handleCheckout = () => {
    navigate('/checkout')
  }

  if (cartItems.length === 0) {
    return (
      <div className="cart-page" style={{ padding: '80px 20px', textAlign: 'center' }}>
        <div className="container">
          <h1>Your Cart is Empty</h1>
          <p style={{ marginBottom: '20px' }}>Add some products to get started!</p>
          <button onClick={() => navigate('/products')} className="btn btn-primary">
            Continue Shopping
          </button>
        </div>
      </div>
    )
  }

  return (
    <div className="cart-page" style={{ padding: '40px 20px' }}>
      <div className="container">
        <h1>Shopping Cart</h1>
        
        <div style={{ marginTop: '30px' }}>
          {cartItems.map(item => (
            <div
              key={item.id}
              className="card"
              style={{ marginBottom: '20px', display: 'flex', gap: '20px' }}
            >
              {item.image_url && (
                <img
                  src={item.image_url}
                  alt={item.name}
                  style={{ width: '100px', height: '100px', objectFit: 'cover', borderRadius: '6px' }}
                />
              )}
              <div style={{ flex: 1 }}>
                <h3>{item.name}</h3>
                <p style={{ color: 'var(--primary-color)', fontSize: '18px', fontWeight: 'bold' }}>
                  ${parseFloat(item.price).toFixed(2)}
                </p>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <input
                  type="number"
                  min="1"
                  max={item.stock_quantity}
                  value={item.quantity}
                  onChange={(e) => updateQuantity(item.id, parseInt(e.target.value) || 1)}
                  style={{ width: '80px', padding: '5px' }}
                />
                <button onClick={() => handleRemove(item)} className="btn btn-danger">
                  Remove
                </button>
              </div>
              <div style={{ textAlign: 'right', minWidth: '100px' }}>
                <strong>${(parseFloat(item.price) * item.quantity).toFixed(2)}</strong>
              </div>
            </div>
          ))}

          <div className="card" style={{ marginTop: '30px', textAlign: 'right' }}>
            <h2>Total: ${getTotal().toFixed(2)}</h2>
            <button
              onClick={handleCheckout}
              className="btn btn-primary"
              style={{ marginTop: '20px', width: '200px' }}
            >
              Proceed to Checkout
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}

export default CartPage
