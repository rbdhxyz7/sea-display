import React from 'react';

export default function FilterBar({ 
  searchQuery, 
  setSearchQuery, 
  selectedCategory, 
  setSelectedCategory, 
  categories 
}) {
  return (
    <div className="space-y-4 mb-8">
      {/* Input Pencarian */}
      <div className="relative">
        <svg 
          className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" 
          fill="none" 
          viewBox="0 0 24 24" 
          stroke="currentColor"
        >
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          type="text"
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          placeholder="Cari komoditas..."
          className="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all placeholder:text-slate-400 shadow-sm"
        />
      </div>

      {/* Filter Kategori Model Pills */}
      <div className="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        {['Semua', ...categories].map((cat) => {
          const isActive = selectedCategory === cat;
          return (
            <button
              key={cat}
              onClick={() => setSelectedCategory(cat)}
              className={`px-5 py-2 rounded-xl text-xs sm:text-sm font-medium transition-all whitespace-nowrap border ${
                isActive
                  ? 'bg-teal-600 border-teal-600 text-white shadow-sm'
                  : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'
              }`}
            >
              {cat}
            </button>
          );
        })}
      </div>
    </div>
  );
}