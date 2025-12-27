import ReactGA from 'react-ga4'

export const initGA = () => {
  const trackingId = import.meta.env.VITE_GA_TRACKING_ID
  if (trackingId) {
    ReactGA.initialize(trackingId)
  }
}

export const logPageView = (path) => {
  ReactGA.send({ hitType: 'pageview', page: path })
}

export const logEvent = (category, action, label = null) => {
  ReactGA.event({
    category,
    action,
    label
  })
}

export const logPurchase = (transactionId, value, items) => {
  ReactGA.event({
    category: 'Ecommerce',
    action: 'Purchase',
    label: transactionId,
    value
  })
}

export const logProductView = (productId, productName) => {
  logEvent('Product', 'View', `${productId} - ${productName}`)
}

export const logAddToCart = (productId, productName) => {
  logEvent('Product', 'Add to Cart', `${productId} - ${productName}`)
}

export const logRemoveFromCart = (productId, productName) => {
  logEvent('Product', 'Remove from Cart', `${productId} - ${productName}`)
}
