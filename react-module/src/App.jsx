import { useState, useEffect } from 'react'
import './App.css'

const IMAGE_NAMES = {
  1: '/ganesh-1.jpg',
  2: '/ganesh-2.jpg',
  3: '/ganesh-3.jpg',
}

const DESCRIPTIONS = {
  1: 'A beautiful compact idol for your home.',
  2: 'A traditional idol for your Ganesh festival.',
  3: 'A grand idol for a memorable celebration.',
}

function App() {
  const [idols, setIdols] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [search, setSearch] = useState('')
  const [maxPrice, setMaxPrice] = useState('all')
  const [sort, setSort] = useState('low')

  useEffect(() => {
    async function loadIdols() {
      try {
        const response = await fetch(
          'http://localhost:3001/api/idols'
        )

        console.log('API HTTP STATUS:', response.status)

        if (!response.ok) {
          throw new Error(`Server error: ${response.status}`)
        }

        const data = await response.json()

        console.log('API DATA:', data)

        if (!data.success || !Array.isArray(data.idols)) {
          throw new Error(data.message || 'Invalid API response')
        }

        const formattedIdols = data.idols.map((idol) => {
          const id = Number(idol.id)

          return {
            ...idol,
            id,
            price: Number(idol.price),
            stock: Number(idol.stock),
            size:
              id === 1 ? 'Small' :
              id === 2 ? 'Medium' : 'Large',
            image: IMAGE_NAMES[id] || '/ganesh-1.jpg',
            description:
              DESCRIPTIONS[id] || 'A beautiful Ganesh idol.',
          }
        })

        setIdols(formattedIdols)
        setError('')
      } catch (err) {
        console.error('Failed to load idols:', err)
        setError(
          'Unable to connect to the idols API. Please check Apache and try again.'
        )
      } finally {
        setLoading(false)
      }
    }

    loadIdols()
  }, [])

  const filteredIdols = idols
    .filter((idol) => {
      const matchesName = idol.name
        .toLowerCase()
        .includes(search.toLowerCase())

      const matchesPrice =
        maxPrice === 'all' || idol.price <= Number(maxPrice)

      return matchesName && matchesPrice
    })
    .sort((a, b) =>
      sort === 'high'
        ? b.price - a.price
        : a.price - b.price
    )

  function handleBooking(idol) {
    const bookingUrl =
      `http://localhost/ganesh-idol-booking/book.php?id=${encodeURIComponent(String(idol.id))}`

    const link = document.createElement('a')
    link.href = bookingUrl
    link.rel = 'noopener'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }

  if (loading) {
    return (
      <div className="app">
        <h2>Loading Ganesh idols...</h2>
      </div>
    )
  }

  if (error) {
    return (
      <div className="app">
        <h2>Unable to load Ganesh idols</h2>
        <p>{error}</p>
        <button onClick={() => window.location.reload()}>
          Try Again
        </button>
      </div>
    )
  }

  return (
    <div className="app">
      <header className="navbar">
        <a className="brand" href="#home">
          <span className="brand-icon">🪔</span>
          <span>
            Girijatmak
            <small>ARTS & IDOLS</small>
          </span>
        </a>

        <nav>
          <a href="#home">Home</a>
          <a href="#catalogue">Our Idols</a>
          <a href="#about">About</a>
        </nav>

        <a className="nav-button" href="#catalogue">
          Explore Idols
        </a>
      </header>

      <main>
        <section className="hero" id="home">
          <div className="hero-content">
            <span className="eyebrow">
              🙏 GANPATI BAPPA MORYA 🙏
            </span>

            <h1>
              Bring home the
              <br />
              <span>blessings of Bappa.</span>
            </h1>

            <p>
              Discover beautiful Ganesh idols made to bring devotion,
              happiness and positivity into your home.
            </p>

            <a className="primary-button" href="#catalogue">
              Explore Collection <span>→</span>
            </a>

            <div className="hero-note">
              <span>✦</span> Celebrate tradition. Create memories.
            </div>
          </div>

          <div className="hero-art">
            <div className="art-circle">
              <span className="om-symbol">ॐ</span>
              <div className="ganesh-emoji">🕉️</div>
              <div className="hero-lamp">🪔</div>
              <div className="hero-flower">🌺</div>
            </div>

            <span className="art-caption">
              DIVINITY • TRADITION • LOVE
            </span>
          </div>
        </section>

        <section className="trust-strip">
          <div>
            <span>🪷</span>
            <p>
              <strong>Traditional Designs</strong>
              <small>Inspired by devotion</small>
            </p>
          </div>

          <div>
            <span>✨</span>
            <p>
              <strong>Made for Your Home</strong>
              <small>Find your perfect idol</small>
            </p>
          </div>

          <div>
            <span>🙏</span>
            <p>
              <strong>Celebrate with Faith</strong>
              <small>Every Bappa is special</small>
            </p>
          </div>
        </section>

        <section className="catalogue" id="catalogue">
          <div className="section-heading">
            <span className="eyebrow">OUR COLLECTION</span>
            <h2>Find your Bappa</h2>
            <p>
              Choose the idol that feels right for your celebration.
            </p>
          </div>

          <div className="filters">
            <input
              type="search"
              placeholder="Search Ganesh idols..."
              value={search}
              onChange={(event) => setSearch(event.target.value)}
              aria-label="Search idols"
            />

            <select
              value={maxPrice}
              onChange={(event) => setMaxPrice(event.target.value)}
              aria-label="Filter by maximum price"
            >
              <option value="all">All prices</option>
              <option value="500">Up to ₹500</option>
              <option value="1000">Up to ₹1,000</option>
              <option value="2000">Up to ₹2,000</option>
            </select>

            <select
              value={sort}
              onChange={(event) => setSort(event.target.value)}
              aria-label="Sort idols"
            >
              <option value="low">Price: Low to High</option>
              <option value="high">Price: High to Low</option>
            </select>
          </div>

          <p className="result-count">
            Showing {filteredIdols.length} of {idols.length} idols
          </p>

          <div className="idol-grid">
            {filteredIdols.map((idol) => (
              <article className="idol-card" key={idol.id}>
                <div className="idol-image">
                  <img
                    src={idol.image}
                    alt={idol.name}
                    onError={(event) => {
                      event.currentTarget.style.display = 'none'
                    }}
                  />

                  <span className="size-tag">{idol.size}</span>
                  <span className="image-fallback">🙏</span>
                </div>

                <div className="idol-info">
                  <span className="product-label">GANESH IDOL</span>
                  <h3>{idol.name}</h3>
                  <p>{idol.description}</p>

                  <p className="stock-info">
                    {idol.stock > 0
                      ? `Available: ${idol.stock}`
                      : 'Currently out of stock'}
                  </p>

                  <div className="product-bottom">
                    <strong>
                      ₹{idol.price.toLocaleString('en-IN')}
                    </strong>

                    <button
                      onClick={() => handleBooking(idol)}
                      disabled={idol.stock <= 0}
                    >
                      {idol.stock > 0
                        ? 'Select Idol →'
                        : 'Out of Stock'}
                    </button>
                  </div>
                </div>
              </article>
            ))}
          </div>

          {filteredIdols.length === 0 && (
            <p className="empty-message">
              No idols found. Try a different search or price.
            </p>
          )}
        </section>

        <section className="about" id="about">
          <span className="eyebrow">OUR THOUGHT</span>

          <h2>
            More than an idol.
            <br />
            A feeling of devotion.
          </h2>

          <p>
            At Girijatmak Arts, we celebrate the love, traditions and
            beautiful memories that make every Ganesh festival special.
          </p>

          <span className="about-mantra">॥ श्री गणेशाय नमः ॥</span>
        </section>
      </main>

      <footer className="footer">
        <a className="brand footer-brand" href="#home">
          🪔 Girijatmak Arts
        </a>

        <p>Made with devotion. 🙏 Ganpati Bappa Morya!</p>

        <small>© {new Date().getFullYear()} Girijatmak Arts</small>
      </footer>
    </div>
  )
}

export default App