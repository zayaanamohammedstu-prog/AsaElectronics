import React, { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useCart } from '../contexts/CartContext'
import { useAuth } from '../contexts/AuthContext'
import { createOrder, initializePayment } from '../services/api'
import { logPurchase } from '../utils/analytics'

const CheckoutPage = () => {
  const navigate = useNavigate()
  const { cartItems, getTotal, clearCart } = useCart()
  const { user } = useAuth()
  const [loading, setLoading] = useState(false)
  const [paymentLoading, setPaymentLoading] = useState(false)

  const handlePlaceOrder = async () => {
    try {
      setLoading(true)

      // Create order
      const orderData = {
        items: cartItems.map(item => ({
          product_id: item.id,
          quantity: item.quantity
        }))
      }

      const response = await createOrder(orderData)
      const order = response.order

      // Initialize payment
      setPaymentLoading(true)
      const paymentResponse = await initializePayment(
        getTotal(),
        user.email,
        order.id,
        `${window.location.origin}/payment/callback`
      )

      if (paymentResponse.status && paymentResponse.data.authorization_url) {
        // Redirect to PayStack payment page
        // Note: Using window.location for external redirect (PayStack checkout)
        window.location.href = paymentResponse.data.authorization_url
      } else {
        alert('Failed to initialize payment')
      }
    } catch (error) {
      console.error('Checkout error:', error)
      alert('Failed to process order: ' + (error.response?.data?.error || error.message))
    } finally {
      setLoading(false)
      setPaymentLoading(false)
    }
  }

  if (cartItems.length === 0) {
    navigate('/cart')
    return null
  }

  return (
    <div className="checkout-page" style={{ padding: '40px 20px' }}>
      <div className="container">
        <h1>Checkout</h1>

        <div style={{ marginTop: '30px', display: 'grid', gridTemplateColumns: '2fr 1fr', gap: '40px' }}>
          <div>
            <div className="card">
              <h2>Order Summary</h2>
              {cartItems.map(item => (
                <div key={item.id} style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '15px' }}>
                  <div>
                    <strong>{item.name}</strong>
                    <p style={{ color: 'var(--text-light)' }}>Quantity: {item.quantity}</p>
                  </div>
                  <div>
                    <strong>${(parseFloat(item.price) * item.quantity).toFixed(2)}</strong>
                  </div>
                </div>
              ))}
            </div>

            <div className="card" style={{ marginTop: '20px' }}>
              <h2>Customer Information</h2>
              <p><strong>Name:</strong> {user.first_name} {user.last_name}</p>
              <p><strong>Email:</strong> {user.email}</p>
              {user.phone && <p><strong>Phone:</strong> {user.phone}</p>}
            </div>
          </div>

          <div>
            <div className="card">
              <h2>Payment</h2>
              <div style={{ marginTop: '20px' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '10px' }}>
                  <span>Subtotal:</span>
                  <strong>${getTotal().toFixed(2)}</strong>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '10px' }}>
                  <span>Shipping:</span>
                  <strong>Free</strong>
                </div>
                <hr style={{ margin: '15px 0' }} />
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '20px' }}>
                  <strong>Total:</strong>
                  <strong>${getTotal().toFixed(2)}</strong>
                </div>
              </div>
              <button
                onClick={handlePlaceOrder}
                className="btn btn-primary"
                disabled={loading || paymentLoading}
                style={{ width: '100%', marginTop: '20px' }}
              >
                {paymentLoading ? 'Redirecting to payment...' : loading ? 'Processing...' : 'Pay with PayStack'}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default CheckoutPage
