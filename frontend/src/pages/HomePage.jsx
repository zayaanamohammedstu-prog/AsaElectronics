import React, { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { getProducts, getCategories } from '../services/api'
import './HomePage.css'

const HomePage = () => {
  const [featuredProducts, setFeaturedProducts] = useState([])
  const [categories, setCategories] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    loadData()
  }, [])

  const loadData = async () => {
    try {
      const [productsData, categoriesData] = await Promise.all([
        getProducts({ limit: 8, is_active: 1 }),
        getCategories()
      ])
      setFeaturedProducts(productsData.products || [])
      setCategories(categoriesData.categories || [])
    } catch (error) {
      console.error('Failed to load data:', error)
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return <div className="loading">Loading...</div>
  }

  return (
    <div className="home-page">
      <section className="hero">
        <div className="container">
          <h1>Welcome to Asa Electronics</h1>
          <p>Discover the latest in quality electronics</p>
          <Link to="/products" className="btn btn-primary">
            Shop Now
          </Link>
        </div>
      </section>

      <section className="categories-section">
        <div className="container">
          <h2>Shop by Category</h2>
          <div className="grid grid-3">
            {categories.map(category => (
              <Link
                key={category.id}
                to={`/products?category=${category.id}`}
                className="category-card"
              >
                <h3>{category.name}</h3>
                <p>{category.description}</p>
                <span className="product-count">{category.product_count} products</span>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section className="featured-products">
        <div className="container">
          <h2>Featured Products</h2>
          <div className="grid grid-4">
            {featuredProducts.map(product => (
              <Link
                key={product.id}
                to={`/products/${product.id}`}
                className="product-card"
              >
                {product.image_url && (
                  <img src={product.image_url} alt={product.name} />
                )}
                <h3>{product.name}</h3>
                <p className="price">${parseFloat(product.price).toFixed(2)}</p>
                <p className="stock">
                  {product.stock_quantity > 0 ? 'In Stock' : 'Out of Stock'}
                </p>
              </Link>
            ))}
          </div>
        </div>
      </section>
    </div>
  )
}

export default HomePage
