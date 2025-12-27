import React from 'react'

const Footer = () => {
  return (
    <footer className="footer">
      <div className="footer-content">
        <div>
          <h3>Asa Electronics</h3>
          <p>Your trusted source for quality electronics</p>
          <p>&copy; 2024 Asa Electronics. All rights reserved.</p>
        </div>
        <div>
          <h3>Quick Links</h3>
          <a href="/products">Products</a>
          <a href="/about">About Us</a>
          <a href="/contact">Contact</a>
        </div>
        <div>
          <h3>Customer Service</h3>
          <a href="/shipping">Shipping Information</a>
          <a href="/returns">Returns & Refunds</a>
          <a href="/privacy">Privacy Policy</a>
        </div>
      </div>
    </footer>
  )
}

export default Footer
