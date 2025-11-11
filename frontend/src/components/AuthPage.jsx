import React from 'react';

export default function AuthPage({ form, handleChange, onRegister, onLogin, onLogout, loading, errors = {} }) {
  const small = (msg, color = '#ef4444') => (
    <div style={{ fontSize: 12, color, marginTop: 6 }}>{msg}</div>
  );

  return (
    <section>
      <h3>Autentificare</h3>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr 120px', gap: 8, marginBottom: 8 }}>
        <div>
          <input name="name" placeholder="Nume (register)" onChange={handleChange} style={{ width: '100%' }} />
          {errors.name ? small(errors.name) : <div style={{ fontSize: 12, color: '#9ca3af', marginTop: 6 }}>required</div>}
        </div>
        <div>
          <input name="email" placeholder="Email" onChange={handleChange} style={{ width: '100%' }} />
          {errors.email ? small(errors.email) : <div style={{ fontSize: 12, color: '#9ca3af', marginTop: 6 }}>required, format: a@b.c</div>}
        </div>
        <div>
          <input name="password" type="password" placeholder="Parola" onChange={handleChange} style={{ width: '100%' }} />
          {errors.password ? small(errors.password) : <div style={{ fontSize: 12, color: '#9ca3af', marginTop: 6 }}>min 6 caractere</div>}
        </div>
        <div>
          <select name="role" onChange={handleChange} defaultValue="client" style={{ width: '100%' }}>
            <option value="client">client</option>
            <option value="admin">admin</option>
          </select>
          <div style={{ fontSize: 12, color: '#9ca3af', marginTop: 6 }}>alege rol (implicit: client)</div>
        </div>
      </div>
      <div style={{ display: 'flex', gap: 8 }}>
        <button onClick={onRegister} disabled={loading}>Register</button>
        <button onClick={onLogin} disabled={loading}>Login</button>
        <button onClick={onLogout} disabled={loading}>Logout</button>
      </div>
    </section>
  );
}
