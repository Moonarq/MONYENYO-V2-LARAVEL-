import React from 'react'
import { Link } from 'react-router-dom'
import { useLanguage } from '../../hooks/useLanguage'
import './BlogPreviewSection.css'

const BlogPreviewSection = () => {
  const { t } = useLanguage()

 const featuredPost = {
  image: '/images/promo.jpg',
  category: t('Produk Baru'),
   title: t('Varian Baru: Banana Strudle Mini'),
  excerpt: t('Perkenalkan varian terbaru kami: Banana Strudle Mini dengan rasa pisang yang autentik dan tekstur lembut.'),
  author: 'Tim Monyenyo',
  date: 'Juli 20, 2025'
}
 const recentPosts = [
  {
    image: '/images/promo1.jpg',
    category: t('Promo'),
    title: t('Weekend Sale – Diskon 20%'),
    date: 'Maret 8, 2024',
    
  },
  {
    image: '/images/Blog2.png',
    category: t('Menu Baru'),
    title: t('Banana Strudel Resmi Hadir!'),
    date: 'Maret 5, 2024',
   
  },
  {
    image: '/images/bestseller.jpg',
    category: t('Event'),
    title: t('Monyenyo di Festival Kuliner Jakarta'),
    date: 'Maret 2, 2024',
   
  },
  {
    image: '/images/Blog1.png',
    category: t('Tips'),
    title: t('Cara Menyimpan Brownies Agar Tetap Fresh'),
    date: 'Februari 28, 2024',
  
  }
]

  

  return (
    <section className="blog-preview-section" id="blogs">
      <div className="container">
        <div className="blog-preview-header">
       <h2 className="blog-preview-title">{t('PROMO & BERITA TERBARU')}</h2>
        <p className="blog-preview-description">
          {t("Temukan promo menarik dan informasi terbaru dari Monyenyo untuk setiap momen spesial.")}
        </p>
        </div>
        
        <div className="blog-preview-grid">
          {/* Featured Blog Post */}
          <div className="featured-blog-post">
            <div className="featured-blog-image">
              <img src={featuredPost.image} alt={featuredPost.title} />
            </div>
            <div className="featured-blog-content">
              <div className="featured-blog-meta">
                <span className="featured-blog-category">{featuredPost.category}</span>
                <span className="featured-blog-date">{featuredPost.date}</span>
              </div>
              <h3 className="featured-blog-title">{featuredPost.title}</h3>
              <p className="featured-blog-excerpt">{featuredPost.excerpt}</p>
              <div className="featured-blog-author">
                <i className="fas fa-user-circle"></i>
                <span>{featuredPost.author}</span>
              </div>
            </div>
          </div>

          {/* Recent Blog Posts */}
          <div className="recent-blog-posts">
            {recentPosts.map((post, index) => (
              <div key={index} className="recent-blog-post">
                <div className="recent-blog-image">
                  <img
                    src={post.image}
                    alt={post.title}
                    style={post.image === '/images/desktop4.jpg' ? { height: '100%', width: '100%', objectFit: 'cover', objectPosition: 'center' } : {}}
                  />
                  <div className="recent-blog-time">{post.readTime}</div>
                </div>
                <div className="recent-blog-content">
                  <span className="recent-blog-category">{post.category}</span>
                  <h4 className="recent-blog-title">{post.title}</h4>
                  <span className="recent-blog-date">{post.date}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
        
        <div className="blog-preview-cta">
          <Link to="/blogs" className="blog-preview-btn">
            {t("Yuk Lihat Yang Baru")}
          </Link>
        </div>
      </div>
    </section>
  )
}

export default BlogPreviewSection
