import React from 'react';

export default function RegisterProductPage({ onRegisterProduct, handleChange, loading, errors = {} }) {
  const small = (msg) => (<div style={{ fontSize: 12, color: '#ef4444', marginTop: 6 }}>{msg}</div>);
  const hint = (txt) => (<div style={{ fontSize: 12, color: '#9ca3af', marginTop: 6 }}>{txt}</div>);
  return (
    <section>
      <h3>Client: Înregistrează produs</h3>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 140px', gap: 8, marginBottom: 8 }}>
        <div>
          <input name="serial" placeholder="Serial" onChange={handleChange} style={{ width: '100%' }} />
          {errors.serial ? small(errors.serial) : hint('required')}
        </div>
        <div>
          <input name="purchase_date" placeholder="YYYY-MM-DD" onChange={handleChange} style={{ width: '100%' }} />
          {errors.purchase_date ? small(errors.purchase_date) : hint('format: YYYY-MM-DD')}
        </div>
        <div style={{ display: 'flex', alignItems: 'flex-end' }}>
          <button onClick={onRegisterProduct} disabled={loading}>Register Product</button>
        </div>
      </div>
    </section>
  );
}
