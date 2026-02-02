import React, { useState } from 'react'
import { Helmet } from 'react-helmet-async'
import { useLanguage } from '../hooks/useLanguage'
import './Blogs.css'

const Blogs = () => {
  const { t } = useLanguage()
  const [activeCategory, setActiveCategory] = useState('all')

  // Apply Blogs page styles to header
  React.useEffect(() => {
    document.body.classList.add('blogs-page')
    return () => {
      document.body.classList.remove('blogs-page')
    }
  }, [])

  const blogArticles = [
 {
  id: 1,
  category: 'menu-baru',
  image: '/images/promo.jpg',
  title: t('Varian Baru: Banana Strudle Mini'),
  excerpt: t('Perkenalkan varian terbaru kami: Banana Strudle Mini dengan rasa pisang yang autentik dan tekstur lembut.'),
  author: t('Tim Monyenyo'),
  date: 'Jul 20, 2025',
  readTime: 'New Product'
},

  {
  id: 2,
  category: 'promo',
  image: '/images/promo1.jpg',
  title: t('Diskon Spesial Akhir Tahun'),
  excerpt: t('Dapatkan potongan hingga 30% untuk semua dessert premium favorit Anda. Promo terbatas, jangan sampai terlewat!'),
  author: t('Tim Monyenyo'),
  date: '10 Des 2024',
  readTime: 'Promo'
},

    {
      id: 3,
      category: 'event',
      image: '/images/bestseller.jpg',
      title: t('A Day in Monyenyo Kitchen'),
      excerpt: t('Follow our bakers through their daily routine of creating fresh, delicious pastries from dawn to dusk.'),
      author: t('by Team Monyenyo'),
      date: 'Dec 8, 2024',
      readTime: 'Best Seller'
    },
  ]

  const categories = [
  { id: 'all', name: t('Semua Update'), icon: 'fas fa-th-large', count: 3 },
  { id: 'promo', name: t('Promo'), icon: 'fas fa-tags', count: 1 },
  { id: 'menu-baru', name: t('Menu Baru'), icon: 'fas fa-utensils', count: 1 },
  { id: 'event', name: t('Event & Kabar'), icon: 'fas fa-calendar-alt', count: 1 }
]


  const filteredArticles = activeCategory === 'all' 
    ? blogArticles 
    : blogArticles.filter(article => article.category === activeCategory)

  const handleCategoryChange = (categoryId) => {
    setActiveCategory(categoryId)
  }

  return (
    <>
      <Helmet>
        <title>Blogs - Monyenyo</title>
        <meta name="description" content="Read our latest stories, recipes, and insights about traditional Indonesian brownies and pastries." />
        <link rel="icon" href="/images/favicon_large.ico" type="image/x-icon" />
      </Helmet>
      
      <div className="blogs-page">
        {/* Blog Hero Section */}
        <section className="about-hero">
          <div className="container">
            <div className="about-hero-content">
              <div className="hero-text-center">
                <span className="company-label">{t('KABAR MONYENYO')}</span>
                <h1 className="hero-main-title">{t('PROMO & INFO TERBARU')}</h1>
                <p className="hero-description">
                  {t('Dapatkan promo spesial, menu terbaru, tips penyajian lezat, dan update menarik langsung dari dapur Monyenyo.')}
                </p>
              </div>
            </div>
          </div>
        </section>

        {/* Featured Article Section */}
     <section className="featured-article">
  <div className="container">
    <div className="featured-content">
      <div className="featured-image">
        <img src="/images/promo1.jpg" alt="Promo Spesial" />
        <div className="featured-badge">{t('PROMO')}</div>
      </div>
      <div className="featured-text">
        <div className="article-meta">
          <span className="article-category">{t('Promo Spesial')}</span>
          <span className="article-date">Juli 20, 2025</span>
        </div>
        <h2 className="featured-title">{t('Diskon 20% untuk Brownies Favoritmu')}</h2>
        <p className="featured-excerpt">
          {t('Nikmati kelezatan brownies premium Monyenyo dengan potongan harga spesial minggu ini. Jangan sampai terlewatkan!')}
        </p>
        <div className="featured-author">
          <div className="author-avatar">
            <i className="fas fa-user-circle"></i>
          </div>
          <div className="author-info">
            <span className="author-name">Tim Monyenyo</span>
            <span className="author-title">{t('Promo Campaign')}</span>
          </div>
        </div>
        <a href="#" className="featured-read-btn">{t('Lihat Detail Promo')}</a>
      </div>
    </div>
  </div>
</section>


        {/* Blog Categories Section */}
        <section className="blog-categories">
          <div className="container">
          <div className="categories-header">
      
            </div>
            <div className="categories-grid">
              {categories.map(category => (
                <div 
                  key={category.id}
                  className={`category-card ${activeCategory === category.id ? 'active' : ''}`}
                  onClick={() => handleCategoryChange(category.id)}
                >
                  <i className={category.icon}></i>
                  <span>{category.name}</span>
                  <div className="category-count">{category.count}</div>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Blog Articles Grid */}
        <section className="blog-articles">
          <div className="container">
            <div className="articles-grid">
              {filteredArticles.map(article => (
                <article
                  key={article.id}
                  className="blog-card"
                  style={filteredArticles.length === 1 ? { maxWidth: '350px' } : {}}
                >
                  <div className="blog-image">
                    <img src={article.image} alt={article.title} />
                    <div className="blog-overlay">
                      <div className="read-time">{article.readTime}</div>
                    </div>
                  </div>
                  <div className="blog-content">
                    <div className="blog-meta">
                      <span className="blog-category">{t(article.category.charAt(0).toUpperCase() + article.category.slice(1))}</span>
                      <span className="blog-date">{article.date}</span>
                    </div>
                    <h3 className="blog-title">{article.title}</h3>
                    <p className="blog-excerpt">{article.excerpt}</p>
                    <div className="blog-author">
                      <span>{article.author}</span>
                    </div>
                  </div>
                </article>
              ))}
            </div>

            {/* Load More Button */}
            <div className="load-more-section">
              <button className="load-more-btn">{t('Load More Stories')}</button>
            </div>
          </div>
        </section>

        {/* Newsletter Section */}
        <section className="newsletter-section">
          <div className="container">
            <div className="newsletter-content">
              <div className="newsletter-icon">
                <i className="fas fa-envelope"></i>
              </div>
              <h2 className="newsletter-title">{t('Stay in the Loop')}</h2>
              <p className="newsletter-description">
                {t('Get our latest stories, recipes, and exclusive offers delivered straight to your inbox.')}
              </p>
              <form className="newsletter-form">
                <div className="input-group">
                  <input 
                    type="email" 
                    className="newsletter-input" 
                    placeholder={t('Enter your email address')} 
                    required 
                  />
                  <button type="submit" className="newsletter-submit">{t('Subscribe')}</button>
                </div>
                <p className="newsletter-privacy">
                  {t('We respect your privacy. Unsubscribe at any time.')}
                </p>
              </form>
            </div>
          </div>
        </section>
      </div>
    </>
  );
}

export default Blogs