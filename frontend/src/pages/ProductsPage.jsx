import React, { useEffect, useState } from 'react'
import { useSearchParams, Link } from 'react-router-dom'
import { getProducts, getCategories } from '../services/api'
import { useCart } from '../contexts/CartContext'
import { logAddToCart } from '../utils/analytics'

const ProductsPage = () => {
  const [products, setProducts] = useState([])
  const [categories, setCategories] = useState([])
  const [loading, setLoading] = useState(true)
  const [searchParams, setSearchParams] = useSearchParams()
  const { addToCart } = useCart()

  const categoryId = searchParams.get('category')
  const search = searchParams.get('search')

  useEffect(() => {
    loadData()
  }, [categoryId, search])

  const loadData = async () => {
    try {
      setLoading(true)
      const filters = { is_active: 1, limit: 50 }
      
      if (categoryId) {
        filters.category_id = categoryId
      }
      
      if (search) {
        filters.search = search
      }

      const [productsData, categoriesData] = await Promise.all([
        getProducts(filters),
        getCategories()
      ])
      
      setProducts(productsData.products || [])
      setCategories(categoriesData.categories || [])
    } catch (error) {
      console.error('Failed to load products:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleAddToCart = (product) => {
    addToCart(product, 1)
    logAddToCart(product.id, product.name)
    alert(`${product.name} added to cart!`)
  }

  const handleCategoryFilter = (catId) => {
    if (catId) {
      setSearchParams({ category: catId })
    } else {
      setSearchParams({})
    }
  }

  if (loading) {
    return <div className="loading">Loading...</div>
  }

  return (
    <div className="products-page" style={{ padding: '40px 20px' }}>
      <div className="container">
        <h1>Products</h1>
        
        <div className="filters" style={{ marginBottom: '30px' }}>
          <button
            onClick={() => handleCategoryFilter(null)}
            className={!categoryId ? 'btn btn-primary' : 'btn btn-secondary'}
            style={{ marginRight: '10px' }}
          >
            All Products
          </button>
          {categories.map(category => (
            <button
              key={category.id}
              onClick={() => handleCategoryFilter(category.id)}
              className={categoryId == category.id ? 'btn btn-primary' : 'btn btn-secondary'}
              style={{ marginRight: '10px' }}
            >
              {category.name}
            </button>
          ))}
        </div>

        <div className="grid grid-4">
          {products.map(product => (
            <div key={product.id} className="product-card">
              <Link to={`/products/${product.id}`}>
                {product.image_url && (
                  <img src={product.image_url} alt={product.name} />
                )}
                <h3>{product.name}</h3>
                <p className="price">${parseFloat(product.price).toFixed(2)}</p>
                <p className="stock">
                  {product.stock_quantity > 0 ? `${product.stock_quantity} in stock` : 'Out of Stock'}
                </p>
              </Link>
              <button
                onClick={() => handleAddToCart(product)}
                className="btn btn-primary"
                disabled={product.stock_quantity <= 0}
                style={{ width: '100%', marginTop: '10px' }}
              >
                {product.stock_quantity > 0 ? 'Add to Cart' : 'Out of Stock'}
              </button>
            </div>
          ))}
        </div>

        {products.length === 0 && (
          <p style={{ textAlign: 'center', padding: '40px' }}>No products found</p>
        )}
      </div>
    </div>
  )
}

export default ProductsPage
