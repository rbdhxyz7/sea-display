import { useEffect, useState } from 'react'
import { supabase } from './supabaseClient'

function App() {
  // State Data
  const [commodities, setCommodities] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [selectedCategory, setSelectedCategory] = useState('Semua')

  // State Authentication & Modal Admin
  const [session, setSession] = useState(null)
  const [showLoginModal, setShowLoginModal] = useState(false)
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [loginError, setLoginError] = useState('')

  // State Form Tambah/Edit Barang (Admin)
  const [isEditing, setIsEditing] = useState(false)
  const [editId, setEditId] = useState(null)
  const [formData, setFormData] = useState({
    nama_komoditas: '',
    kategori: 'Ikan Laut',
    stok_kg: 0,
    satuan: 'kg',
    harga_jual: 0,
    harga_beli: 0,
    status_stok: 'Tersedia'
  })

  useEffect(() => {
    // Check Active Session
    supabase.auth.getSession().then(({ data: { session } }) => {
      setSession(session)
    })

    const { data: { subscription } } = supabase.auth.onAuthStateChange((_event, session) => {
      setSession(session)
    })

    fetchCommodities()

    return () => subscription.unsubscribe()
  }, [])

  // 1. Ambil Data Komoditas
  async function fetchCommodities() {
    setLoading(true)
    const { data, error } = await supabase
      .from('commodities')
      .select('*')
      .order('id', { ascending: true })
    
    if (error) {
      console.error('Gagal mengambil data:', error.message)
    } else {
      setCommodities(data || [])
    }
    setLoading(false)
  }

  // 2. Fungsi Login Admin
  async function handleLogin(e) {
    e.preventDefault()
    setLoginError('')
    const { error } = await supabase.auth.signInWithPassword({ email, password })
    if (error) {
      setLoginError('Login gagal: Check email/password anda!')
    } else {
      setShowLoginModal(false)
      setEmail('')
      setPassword('')
    }
  }

  // 3. Fungsi Logout Admin
  async function handleLogout() {
    await supabase.auth.signOut()
  }

  // 4. Tambah / Update Data (Admin CRUD)
  async function handleSubmitForm(e) {
    e.preventDefault()
    if (isEditing) {
      const { error } = await supabase
        .from('commodities')
        .update(formData)
        .eq('id', editId)
      if (error) alert('Gagal update: ' + error.message)
    } else {
      const { error } = await supabase
        .from('commodities')
        .insert([formData])
      if (error) alert('Gagal tambah: ' + error.message)
    }
    resetForm()
    fetchCommodities()
  }

  // 5. Edit Handler
  function handleEdit(item) {
    setIsEditing(true)
    setEditId(item.id)
    setFormData({
      nama_komoditas: item.nama_komoditas,
      kategori: item.kategori,
      stok_kg: item.stok_kg,
      satuan: item.satuan,
      harga_jual: item.harga_jual,
      harga_beli: item.harga_beli,
      status_stok: item.status_stok
    })
  }

  // 6. Delete Handler
  async function handleDelete(id) {
    if (confirm('Yakin ingin menghapus komoditas ini?')) {
      const { error } = await supabase.from('commodities').delete().eq('id', id)
      if (error) alert('Gagal hapus: ' + error.message)
      else fetchCommodities()
    }
  }

  function resetForm() {
    setIsEditing(false)
    setEditId(null)
    setFormData({
      nama_komoditas: '',
      kategori: 'Ikan Laut',
      stok_kg: 0,
      satuan: 'kg',
      harga_jual: 0,
      harga_beli: 0,
      status_stok: 'Tersedia'
    })
  }

  // Filter Data untuk Tampilan Pengunjung
  const filteredCommodities = commodities.filter((item) => {
    const matchesSearch = item.nama_komoditas.toLowerCase().includes(search.toLowerCase())
    const matchesCategory = selectedCategory === 'Semua' || item.kategori === selectedCategory
    return matchesSearch && matchesCategory
  })

  return (
    <div style={{ fontFamily: 'Segoe UI, sans-serif', backgroundColor: '#f8fafc', minHeight: '100vh', padding: '16px' }}>
      <div style={{ maxWidth: '800px', margin: '0 auto' }}>
        
        {/* HEADER WEBSITE */}
        <header style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '16px 0', borderBottom: '2px solid #e2e8f0' }}>
          <div>
            <h1 style={{ margin: 0, fontSize: '20px', color: '#0f172a' }}>UD Bintang Laut Desy</h1>
            <small style={{ color: '#64748b' }}>Pasar Cipondoh - Pendataan Stok Real-Time</small>
          </div>
          {session ? (
            <div style={{ textAlign: 'right' }}>
              <span style={{ fontSize: '12px', background: '#dcfce7', color: '#166534', padding: '4px 8px', borderRadius: '4px', fontWeight: 'bold' }}>
                Mode Admin
              </span>
              <button onClick={handleLogout} style={{ marginLeft: '8px', padding: '6px 12px', background: '#ef4444', color: 'white', border: 'none', borderRadius: '6px', cursor: 'pointer' }}>
                Logout
              </button>
            </div>
          ) : (
            <button onClick={() => setShowLoginModal(true)} style={{ padding: '8px 16px', background: '#2563eb', color: 'white', border: 'none', borderRadius: '6px', cursor: 'pointer', fontWeight: 'bold' }}>
              Login Admin
            </button>
          )}
        </header>

        {/* ================= MODAL LOGIN ADMIN ================= */}
        {showLoginModal && (
          <div style={{ position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(0,0,0,0.5)', display: 'flex', justifyContent: 'center', alignItems: 'center', zIndex: 100 }}>
            <div style={{ background: 'white', padding: '24px', borderRadius: '12px', width: '90%', maxWidth: '360px' }}>
              <h3 style={{ marginTop: 0 }}>Login Pemilik / Admin</h3>
              {loginError && <p style={{ color: 'red', fontSize: '13px' }}>{loginError}</p>}
              <form onSubmit={handleLogin}>
                <div style={{ marginBottom: '12px' }}>
                  <label style={{ display: 'block', fontSize: '12px', marginBottom: '4px' }}>Email</label>
                  <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required style={{ width: '100%', padding: '8px', borderRadius: '6px', border: '1px solid #ccc' }} />
                </div>
                <div style={{ marginBottom: '16px' }}>
                  <label style={{ display: 'block', fontSize: '12px', marginBottom: '4px' }}>Password</label>
                  <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required style={{ width: '100%', padding: '8px', borderRadius: '6px', border: '1px solid #ccc' }} />
                </div>
                <div style={{ display: 'flex', gap: '8px' }}>
                  <button type="submit" style={{ flex: 1, padding: '10px', background: '#2563eb', color: 'white', border: 'none', borderRadius: '6px', cursor: 'pointer' }}>Masuk</button>
                  <button type="button" onClick={() => setShowLoginModal(false)} style={{ flex: 1, padding: '10px', background: '#cbd5e1', border: 'none', borderRadius: '6px', cursor: 'pointer' }}>Batal</button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* ================= PANEL KHUSUS ADMIN (CRUD FORM) ================= */}
        {session && (
          <section style={{ background: 'white', padding: '16px', borderRadius: '12px', marginTop: '16px', border: '1px solid #e2e8f0', boxShadow: '0 1px 3px rgba(0,0,0,0.1)' }}>
            <h3 style={{ margin: '0 0 12px 0', color: '#1e293b' }}>{isEditing ? 'Ubah Data Komoditas' : 'Tambah Komoditas Baru'}</h3>
            <form onSubmit={handleSubmitForm} style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
              <div>
                <label style={{ fontSize: '12px' }}>Nama Komoditas</label>
                <input type="text" value={formData.nama_komoditas} onChange={(e) => setFormData({...formData, nama_komoditas: e.target.value})} required style={{ width: '100%', padding: '8px', borderRadius: '6px', border: '1px solid #cbd5e1' }} />
              </div>
              <div>
                <label style={{ fontSize: '12px' }}>Kategori</label>
                <select value={formData.kategori} onChange={(e) => setFormData({...formData, kategori: e.target.value})} style={{ width: '100%', padding: '8px', borderRadius: '6px', border: '1px solid #cbd5e1' }}>
                  <option value="Ikan Laut">Ikan Laut</option>
                  <option value="Udang/Kerang">Udang/Kerang</option>
                  <option value="Cumi-cumi">Cumi-cumi</option>
                </select>
              </div>
              <div>
                <label style={{ fontSize: '12px' }}>Sisa Stok (kg)</label>
                <input type="number" step="0.1" value={formData.stok_kg} onChange={(e) => setFormData({...formData, stok_kg: parseFloat(e.target.value)})} required style={{ width: '100%', padding: '8px', borderRadius: '6px', border: '1px solid #cbd5e1' }} />
              </div>
              <div>
                <label style={{ fontSize: '12px' }}>Harga Jual / kg (Rp)</label>
                <input type="number" value={formData.harga_jual} onChange={(e) => setFormData({...formData, harga_jual: parseInt(e.target.value)})} required style={{ width: '100%', padding: '8px', borderRadius: '6px', border: '1px solid #cbd5e1' }} />
              </div>
              <div>
                <label style={{ fontSize: '12px' }}>Harga Beli / Kulakan (Rp)</label>
                <input type="number" value={formData.harga_beli} onChange={(e) => setFormData({...formData, harga_beli: parseInt(e.target.value)})} required style={{ width: '100%', padding: '8px', borderRadius: '6px', border: '1px solid #cbd5e1' }} />
              </div>
              <div>
                <label style={{ fontSize: '12px' }}>Status Stok</label>
                <select value={formData.status_stok} onChange={(e) => setFormData({...formData, status_stok: e.target.value})} style={{ width: '100%', padding: '8px', borderRadius: '6px', border: '1px solid #cbd5e1' }}>
                  <option value="Tersedia">Tersedia</option>
                  <option value="Sisa Sedikit">Sisa Sedikit</option>
                  <option value="Habis">Habis</option>
                </select>
              </div>
              <div style={{ gridColumn: 'span 2', display: 'flex', gap: '8px', marginTop: '8px' }}>
                <button type="submit" style={{ padding: '10px 16px', background: isEditing ? '#f59e0b' : '#16a34a', color: 'white', border: 'none', borderRadius: '6px', cursor: 'pointer', fontWeight: 'bold' }}>
                  {isEditing ? 'Simpan Perubahan' : 'Tambah Barang'}
                </button>
                {isEditing && (
                  <button type="button" onClick={resetForm} style={{ padding: '10px 16px', background: '#94a3b8', color: 'white', border: 'none', borderRadius: '6px', cursor: 'pointer' }}>
                    Batal
                  </button>
                )}
              </div>
            </form>
          </section>
        )}

        {/* ================= TAMPILAN KATALOG PENGUNJUNG ================= */}
        <main style={{ marginTop: '24px' }}>
          {/* SEARCH & FILTER BAR */}
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px', marginBottom: '16px' }}>
            <input 
              type="text" 
              placeholder="Cari ikan, cumi, udang..." 
              value={search} 
              onChange={(e) => setSearch(e.target.value)}
              style={{ flex: 1, padding: '10px', borderRadius: '8px', border: '1px solid #cbd5e1', fontSize: '14px' }}
            />
            <select value={selectedCategory} onChange={(e) => setSelectedCategory(e.target.value)} style={{ padding: '10px', borderRadius: '8px', border: '1px solid #cbd5e1', fontSize: '14px' }}>
              <option value="Semua">Semua Kategori</option>
              <option value="Ikan Laut">Ikan Laut</option>
              <option value="Udang/Kerang">Udang/Kerang</option>
              <option value="Cumi-cumi">Cumi-cumi</option>
            </select>
          </div>

          {/* LIST KARTU KOMODITAS */}
          {loading ? (
            <p style={{ textAlign: 'center', color: '#64748b' }}>Memuat data stok...</p>
          ) : filteredCommodities.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '32px', background: 'white', borderRadius: '8px', color: '#64748b' }}>
              Tidak ada data komoditas ditemukan.
            </div>
          ) : (
            <div style={{ display: 'grid', gap: '12px' }}>
              {filteredCommodities.map((item) => (
                <div key={item.id} style={{ background: 'white', padding: '16px', borderRadius: '10px', border: '1px solid #e2e8f0', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <div>
                    <h3 style={{ margin: '0 0 4px 0', fontSize: '16px', color: '#0f172a' }}>{item.nama_komoditas}</h3>
                    <div style={{ display: 'flex', gap: '6px', alignItems: 'center' }}>
                      <span style={{ fontSize: '11px', background: '#f1f5f9', padding: '2px 6px', borderRadius: '4px', color: '#475569' }}>{item.kategori}</span>
                      <span style={{ 
                        fontSize: '11px', 
                        padding: '2px 6px', 
                        borderRadius: '4px', 
                        fontWeight: 'bold',
                        background: item.status_stok === 'Tersedia' ? '#dcfce7' : item.status_stok === 'Sisa Sedikit' ? '#fef9c3' : '#fee2e2',
                        color: item.status_stok === 'Tersedia' ? '#166534' : item.status_stok === 'Sisa Sedikit' ? '#854d0e' : '#991b1b'
                      }}>
                        {item.status_stok}
                      </span>
                    </div>
                    {/* HARGA BELI HANYA TAMPIL SAAT ADMIN LOGIN */}
                    {session && (
                      <small style={{ display: 'block', marginTop: '6px', color: '#ea580c', fontWeight: 'bold' }}>
                        Harga Beli (Modal): Rp {item.harga_beli?.toLocaleString('id-ID')} / {item.satuan}
                      </small>
                    )}
                  </div>

                  <div style={{ textAlign: 'right' }}>
                    <div style={{ fontSize: '16px', fontWeight: 'bold', color: '#2563eb' }}>
                      Rp {item.harga_jual?.toLocaleString('id-ID')} <span style={{ fontSize: '12px', color: '#64748b' }}>/ {item.satuan}</span>
                    </div>
                    <small style={{ color: '#475569', display: 'block', marginTop: '2px' }}>
                      Stok: <strong>{item.stok_kg} {item.satuan}</strong>
                    </small>

                    {/* TOMBOL AKSI UNTUK ADMIN */}
                    {session && (
                      <div style={{ marginTop: '8px', display: 'flex', gap: '4px', justifyContent: 'flex-end' }}>
                        <button onClick={() => handleEdit(item)} style={{ padding: '4px 8px', background: '#f59e0b', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer', fontSize: '12px' }}>Edit</button>
                        <button onClick={() => handleDelete(item.id)} style={{ padding: '4px 8px', background: '#ef4444', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer', fontSize: '12px' }}>Hapus</button>
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </main>
      </div>
    </div>
  )
}

export default App