import React from 'react';

export default function ProductCard({ item, waNumber = '6281234567890' }) {
  const getStockStatus = (stock) => {
    if (stock <= 0) {
      return { label: 'Habis', textClr: 'text-rose-700', bgClr: 'bg-rose-50', dotClr: 'bg-rose-500' };
    }
    if (stock <= 5) {
      return { label: 'Stok Menipis', textClr: 'text-amber-700', bgClr: 'bg-amber-50', dotClr: 'bg-amber-500' };
    }
    return { label: 'Tersedia', textClr: 'text-emerald-700', bgClr: 'bg-emerald-50', dotClr: 'bg-emerald-500' };
  };

  const status = getStockStatus(item.stock);
  const waMessage = encodeURIComponent(
    `Halo Bu Desy, saya ingin memesan ${item.name} seharga Rp ${item.selling_price?.toLocaleString('id-ID')} / ${item.unit || 'kg'}`
  );
  const waUrl = `https://wa.me/${waNumber}?text=${waMessage}`;

  return (
    <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 flex flex-col h-full">
      {/* Gambar & Badge Kategori */}
      <div className="relative aspect-[4/3] bg-slate-100 overflow-hidden">
        <img
          src={item.image_url || 'https://images.unsplash.com/photo-1534483509719-3feaee7c30da?q=80&w=400'}
          alt={item.name}
          className="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
        />
        <span className="absolute top-2.5 left-2.5 bg-white/90 backdrop-blur-md text-slate-800 text-[11px] font-semibold px-2.5 py-0.5 rounded-md shadow-sm">
          {item.category}
        </span>
      </div>

      {/* Informasi Produk */}
      <div className="p-4 flex-1 flex flex-col justify-between space-y-3">
        <div>
          <h3 className="font-semibold text-slate-800 text-sm sm:text-base capitalize line-clamp-1 mb-1.5">
            {item.name}
          </h3>

          {/* Badge Status Stok */}
          <span className={`inline-flex items-center gap-1.5 text-[11px] font-medium px-2.5 py-0.5 rounded-full ${status.bgClr} ${status.textClr}`}>
            <span className={`w-1.5 h-1.5 rounded-full ${status.dotClr}`} />
            {status.label}
          </span>
        </div>

        {/* Harga & Tombol WhatsApp */}
        <div className="pt-1">
          <div className="mb-3">
            <span className="text-emerald-700 font-bold text-base sm:text-lg">
              Rp {item.selling_price?.toLocaleString('id-ID')}
            </span>
            <span className="text-slate-400 text-xs font-normal"> / {item.unit || 'kg'}</span>
          </div>

          {item.stock > 0 ? (
            <a
              href={waUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-medium text-xs sm:text-sm py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-sm"
            >
              Pesan via WhatsApp
            </a>
          ) : (
            <button
              disabled
              className="w-full bg-slate-100 text-slate-400 font-medium text-xs sm:text-sm py-2.5 px-3 rounded-xl cursor-not-allowed"
            >
              Stok Habis
            </button>
          )}
        </div>
      </div>
    </div>
  );
}