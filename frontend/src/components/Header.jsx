import React from 'react';

export default function Header() {
  const link = (hash, label) => (
    <a href={hash} style={{ marginRight: 12, textDecoration: 'none', color: '#2563eb', fontWeight: 600 }}>{label}</a>
  );

  return (
    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 18 }}>
      <div style={{ fontSize: 18, fontWeight: 700, color: '#111827' }}>Kahuna</div>
      <nav>
        {link('#/auth', 'Autentificare')}
        {link('#/products', 'Produse')}
        {link('#/admin', 'Admin')}
        {link('#/register-product', 'Înregistrează')}
      </nav>
    </div>
  );
}
