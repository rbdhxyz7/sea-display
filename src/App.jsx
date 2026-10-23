import React, { useEffect, useState } from 'react';
import { supabase } from './supabaseClient';
import HeroBanner from './components/HeroBanner';
import FilterBar from './components/FilterBar';
import ProductCard from './components/ProductCard';

export default function App() {
  const [products, setProducts] = useState([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('Semua');
  const [loading, setLoading] = useState(true);

  const categories = ['Ikan', 'Udang', 'Cumi', 'Kerang'];

  useEffect(() => {
    fetchProducts();

    const channel = supabase
      .channel('public:commodities')
      .on('postgres_changes', { event: '*', schema: 'public', table: 'commodities' }, () => {
        fetchProducts();
      })
      .subscribe();

    return () => {
      supabase.removeChannel(channel);
    };
  }, []);

  const fetchProducts = async () => {
    try {
      const { data, error } = await supabase.from('commodities').select('*');
      if (error) throw error;
      if (data) setProducts(data);
    } catch (err) {
      console.error('Error fetching commodities:', err);
    } finally {
      setLoading(false);
    }
  };

  const filteredProducts = products.filter((item) => {
    const matchesSearch = item.name?.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesCategory = selectedCategory === 'Semua' || item.category === selectedCategory;
    return matchesSearch && matchesCategory;
  });

  return (
    <div className="min-h-screen bg-white text-slate-800">
      <header className="border-b border-slate-100 sticky top-0 bg-white/90 backdrop-blur-md z-50">
        <div className="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center font-bold text-sm">
              S
            </div>
            <span className="font-bold text-lg text-slate-800">Sea Display</span>
          </div>
          <button className="p-2 text-slate-500 hover:text-slate-800 transition-colors rounded-full hover:bg-slate-100">
            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </button>
        </div>
      </header>

      <main className="max-w-6xl mx-auto px-4 py-6">
        <HeroBanner />

        <section>
          <div className="mb-4">
            <span className="text-teal-600 font-bold text-xs uppercase tracking-wider">KATALOG HARI INI</span>
            <h2 className="text-2xl font-bold text-slate-800 mt-1">Pilih hasil laut favorit Anda</h2>
          </div>

          <FilterBar
            searchQuery={searchQuery}
            setSearchQuery={setSearchQuery}
            selectedCategory={selectedCategory}
            setSelectedCategory={setSelectedCategory}
            categories={categories}
          />

          {loading ? (
            <div className="text-center py-12 text-slate-400">Memuat katalog...</div>
          ) : filteredProducts.length === 0 ? (
            <div className="text-center py-16 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
              <p className="text-slate-500 font-medium text-sm">Tidak ada komoditas yang ditemukan.</p>
            </div>
          ) : (
            <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
              {filteredProducts.map((item) => (
                <ProductCard key={item.id} item={item} />
              ))}
            </div>
          )}
        </section>
      </main>
    </div>
  );
}