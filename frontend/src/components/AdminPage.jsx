import React from 'react';

export default function AdminPage({ onAddProduct, handleChange, loading, errors = {} }) {
  const small = (msg) => (<div style={{ fontSize: 12, color: '#ef4444', marginTop: 6 }}>{msg}</div>);
  const hint = (txt) => (<div style={{ fontSize: 12, color: '#9ca3af', marginTop: 6 }}>{txt}</div>);
  return (
    <section>
      <h3>Admin: Adaugă produs</h3>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 140px 120px', gap: 8, marginBottom: 8 }}>
        <div>
          <input name="serial" placeholder="Serial" onChange={handleChange} style={{ width: '100%' }} />
          {errors.serial ? small(errors.serial) : hint('required')}
        </div>
        <div>
          <input name="name" placeholder="Nume produs" onChange={handleChange} style={{ width: '100%' }} />
          {errors.name ? small(errors.name) : hint('required')}
        </div>
        <div>
          <input name="warranty_years" type="number" placeholder="Ani garanție" onChange={handleChange} style={{ width: '100%' }} />
          {errors.warranty_years ? small(errors.warranty_years) : hint('ex: 24')}
        </div>
        <div style={{ display: 'flex', alignItems: 'flex-end' }}>
          <button onClick={onAddProduct} disabled={loading}>Add Product</button>
        </div>
      </div>
    </section>
  );
}
