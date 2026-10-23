import React from 'react';

export default function HeroBanner() {
  return (
    <div className="relative bg-slate-900 text-white rounded-2xl overflow-hidden mb-8 p-6 sm:p-10 md:p-12 shadow-lg">
      {/* Background Image dengan efek Overlay */}
      <div 
        className="absolute inset-0 bg-cover bg-center opacity-25 mix-blend-luminosity"
        style={{ backgroundImage: `url('https://images.unsplash.com/photo-1534483509719-3feaee7c30da?q=80&w=1200')` }}
      />
      <div className="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-900/80 to-transparent" />

      {/* Konten Hero */}
      <div className="relative z-10 max-w-xl">
        <div className="flex items-center gap-2 text-emerald-400 font-semibold tracking-wider text-xs uppercase mb-3">
          <span className="w-5 h-[2px] bg-emerald-400"></span>
          SEGAR SETIAP HARI DARI LAUT
        </div>
        <h1 className="text-3xl sm:text-4xl md:text-5xl font-extrabold mb-4 leading-tight">
          Hasil Laut Segar, <br />
          <span className="text-emerald-400">Harga Terbaik</span>
        </h1>
        <p className="text-slate-300 text-sm sm:text-base mb-6 leading-relaxed">
          Pilihan komoditas laut berkualitas untuk rumah makan, usaha, dan keluarga Anda. Pesan mudah langsung dari Bu Desy.
        </p>
        <div className="flex flex-wrap items-center gap-4 sm:gap-6 text-xs sm:text-sm font-medium text-slate-200">
          <span className="flex items-center gap-1.5">
            <svg className="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
            </svg>
            Kualitas terjamin
          </span>
          <span className="flex items-center gap-1.5">
            <svg className="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
            </svg>
            Harga transparan
          </span>
        </div>
      </div>
    </div>
  );
}