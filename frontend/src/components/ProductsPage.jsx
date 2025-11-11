import React from 'react';

export default function ProductsPage({ listProducts, myProducts, productDetail, serial, setSerial, loading }) {
  return (
    <section>
      <h3>Produse</h3>
      <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 8 }}>
        <button onClick={listProducts} disabled={loading}>List Products</button>
        <button onClick={myProducts} disabled={loading}>My Registered Products</button>
        <input placeholder="Serial produs" value={serial} onChange={(e) => setSerial(e.target.value)} style={{ flex: 1 }} />
        <button onClick={productDetail} disabled={loading}>Product Detail</button>
      </div>
    </section>
  );
}
