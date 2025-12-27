import React from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'
import { useCart } from '../contexts/CartContext'

const Navbar = () => {
  const { user, logout, isAdmin } = useAuth()
  const { getItemCount } = useCart()

  return (
    <nav className="navbar">
      <div className="navbar-content">
        <Link to="/" className="navbar-brand">
          Asa Electronics
        </Link>
        <div className="navbar-links">
          <Link to="/">Home</Link>
          <Link to="/products">Products</Link>
          {user ? (
            <>
              <Link to="/cart">
                Cart
                {getItemCount() > 0 && (
                  <span className="cart-badge">{getItemCount()}</span>
                )}
              </Link>
              {isAdmin() && <Link to="/admin">Admin</Link>}
              <button onClick={logout} className="btn btn-secondary">
                Logout
              </button>
            </>
          ) : (
            <>
              <Link to="/cart">
                Cart
                {getItemCount() > 0 && (
                  <span className="cart-badge">{getItemCount()}</span>
                )}
              </Link>
              <Link to="/login">Login</Link>
              <Link to="/register">Register</Link>
            </>
          )}
        </div>
      </div>
    </nav>
  )
}

export default Navbar
