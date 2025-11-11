import React, { useState, useEffect } from "react";
import Header from "./components/Header";
import AuthPage from "./components/AuthPage";
import ProductsPage from "./components/ProductsPage";
import AdminPage from "./components/AdminPage";
import RegisterProductPage from "./components/RegisterProductPage";

const API = "http://localhost:8000";

function App() {
  const [token, setToken] = useState("");
  const [role, setRole] = useState("");
  const [userEmail, setUserEmail] = useState("");
  const [userName, setUserName] = useState("");
  const [response, setResponse] = useState("");
  const [form, setForm] = useState({});
  const [serial, setSerial] = useState("");
  const [loading, setLoading] = useState(false);
  const [alert, setAlert] = useState({ type: "", msg: "" });

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleToken = (e) => setToken(e.target.value);
  const handleSerial = (e) => setSerial(e.target.value);

  const [route, setRoute] = useState(window.location.hash || '#/auth');

  useEffect(() => {
    const onHash = () => setRoute(window.location.hash || '#/auth');
    window.addEventListener('hashchange', onHash);
    return () => window.removeEventListener('hashchange', onHash);
  }, []);

  // Load the saved token and user information
  useEffect(() => {
    const t = localStorage.getItem('token');
    const r = localStorage.getItem('role');
    const e = localStorage.getItem('email');
    const n = localStorage.getItem('name');
    if (t) setToken(t);
    if (r) setRole(r);
    if (e) setUserEmail(e);
    if (n) setUserName(n);
  }, []);

  // If a token exists on load, verify it and fetch user information from /me
  useEffect(() => {
    const tryLoadUser = async () => {
      if (!token) return;
      const data = await call('/me', 'GET', null, true);
      if (data && data.user) {
        setRole(data.user.role || 'client');
        setUserEmail(data.user.email || '');
        setUserName(data.user.name || '');
        localStorage.setItem('role', data.user.role || 'client');
        localStorage.setItem('email', data.user.email || '');
        localStorage.setItem('name', data.user.name || '');
      }
    };
    tryLoadUser();
  // Runs only when the token changes
  }, [token]);

  const call = async (endpoint, method = "GET", body = null, useToken = true) => {
    setLoading(true);
    setAlert({ type: "", msg: "" });
    setResponse("");
    const headers = { "Content-Type": "application/json" };
    if (useToken && token) headers["Authorization"] = `Bearer ${token}`;
    try {
      const res = await fetch(`${API}${endpoint}`, {
        method,
        headers,
        body: body ? JSON.stringify(body) : undefined,
      });
      const data = await res.json();
      setResponse(JSON.stringify(data, null, 2));
      if (res.ok) {
        setAlert({ type: "success", msg: data.message || "Success" });
      } else {
        setAlert({ type: "error", msg: data.error || "Eroare" });
      }
      return data;
    } catch (err) {
      setAlert({ type: "error", msg: "Network error" });
      setResponse("");
      return {};
    } finally {
      setLoading(false);
    }
  };

  // Autentification
  const register = () => {
    if (!form.name || !form.email || !form.password) {
      setAlert({ type: "error", msg: "Completează nume, email și parolă pentru înregistrare." });
      return;
    }
  // Ensure the role is sent (default: client)
    const payload = { ...form, role: form.role || 'client' };
    call("/register", "POST", payload, false);
  };
  const login = async () => {
    if (!form.email || !form.password) {
      setAlert({ type: "error", msg: "Completează email și parolă pentru login." });
      return;
    }
    const data = await call("/login", "POST", form, false);
    if (data.token) {
      setToken(data.token);
      if (data.user) {
        setRole(data.user.role || 'client');
        setUserEmail(data.user.email || '');
        setUserName(data.user.name || '');
        localStorage.setItem('token', data.token);
        localStorage.setItem('role', data.user.role || 'client');
        localStorage.setItem('email', data.user.email || '');
        localStorage.setItem('name', data.user.name || '');
      }
    }
  };
  const logout = async () => {
    await call("/logout", "POST");
    setToken("");
    setRole("");
    setUserEmail("");
    setUserName("");
    localStorage.removeItem('token');
    localStorage.removeItem('role');
    localStorage.removeItem('email');
    localStorage.removeItem('name');
  };

  // Products
  const listProducts = () => call("/products", "GET", null, false);
  const addProduct = () => {
    if (!form.serial || !form.name || !form.warranty_years) {
      setAlert({ type: "error", msg: "Completează serial, nume și ani garanție pentru produs." });
      return;
    }
  if (!token || role !== 'admin') { setAlert({ type: 'error', msg: 'Trebuie să fii autentificat ca admin.' }); return; }
    call("/admin/products", "POST", form);
  };
  const registerProduct = () => {
    if (!form.serial || !form.purchase_date) {
      setAlert({ type: "error", msg: "Completează serial și data achiziției pentru înregistrare produs." });
      return;
    }
    if (!token) { setAlert({ type: 'error', msg: 'Trebuie să fii autentificat pentru a înregistra produsul.' }); return; }
    call("/register-product", "POST", form);
  };
  const myProducts = () => call("/my-products", "GET");
  const productDetail = () => call(`/product/${serial}`, "GET");

  const containerStyle = { fontFamily: "Inter, sans-serif", background: "#f6f8fa", minHeight: "100vh", padding: 0, margin: 0 };
  const cardStyle = { maxWidth: 900, margin: "24px auto", background: "#fff", borderRadius: 12, boxShadow: "0 2px 16px #0001", padding: 24 };

  const renderRoute = () => {
    switch (route) {
      case '#/products':
        return <ProductsPage listProducts={listProducts} myProducts={myProducts} productDetail={productDetail} serial={serial} setSerial={setSerial} loading={loading} />;
      case '#/admin':
        return <AdminPage onAddProduct={addProduct} handleChange={handleChange} loading={loading} errors={validateFields(route)} />;
      case '#/register-product':
        return <RegisterProductPage onRegisterProduct={registerProduct} handleChange={handleChange} loading={loading} errors={validateFields(route)} />;
      case '#/auth':
      default:
        return <AuthPage form={form} handleChange={handleChange} onRegister={register} onLogin={login} onLogout={logout} loading={loading} errors={validateFields(route)} />;
    }
  };

  function validateFields(currentRoute) {
    const errs = {};
  // Validations for login/registration
    if (currentRoute === '#/auth') {
      if (!form.name) errs.name = 'Nume obligatoriu';
      if (!form.email) errs.email = 'Email obligatoriu';
      else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) errs.email = 'Email invalid';
      if (!form.password) errs.password = 'Parola obligatorie';
      else if (form.password && form.password.length < 6) errs.password = 'Parola trebuie să aibă cel puțin 6 caractere';
    }
  // Validations for adding a product (admin)
    if (currentRoute === '#/admin') {
      if (!form.serial) errs.serial = 'Serial obligatoriu';
      if (!form.name) errs.name = 'Nume produs obligatoriu';
      if (!form.warranty_years && form.warranty_years !== 0) errs.warranty_years = 'Ani garanție obligatorii';
      else if (form.warranty_years && !/^\d+$/.test(String(form.warranty_years))) errs.warranty_years = 'Trebuie un număr întreg';
    }
  // Validations for product registration(client)
    if (currentRoute === '#/register-product') {
      if (!form.serial) errs.serial = 'Serial obligatoriu';
      if (!form.purchase_date) errs.purchase_date = 'Data achiziției obligatorie';
      else if (!/^\d{4}-\d{2}-\d{2}$/.test(String(form.purchase_date))) errs.purchase_date = 'Format așteptat: YYYY-MM-DD';
    }
    return errs;
  }

  return (
    <div style={containerStyle}>
      <div style={cardStyle}>
        <Header />
        {alert.msg && (
          <div style={{
            background: alert.type === "success" ? "#d1fae5" : "#fee2e2",
            color: alert.type === "success" ? "#065f46" : "#991b1b",
            border: `1px solid ${alert.type === "success" ? "#10b981" : "#ef4444"}`,
            borderRadius: 6,
            padding: "8px 12px",
            marginBottom: 16,
            textAlign: "center",
            fontWeight: 500,
          }}>
            {alert.msg}
          </div>
        )}

  {/* Authentication state  */}
        <div style={{ marginBottom: 12 }}>
          {token ? (
            <div style={{ background: '#ecfdf5', color: '#064e3b', padding: '8px 12px', borderRadius: 6, border: '1px solid #bbf7d0' }}>
              Logged in as <strong>{userName || userEmail || 'unknown'}</strong>
              <div style={{ fontSize: 12, color: '#064e3b' }}>{userEmail || ''} • {role || 'client'}</div>
            </div>
          ) : (
            <div style={{ background: '#fff1f2', color: '#7f1d1d', padding: '8px 12px', borderRadius: 6, border: '1px solid #fecaca' }}>
              Not logged in
            </div>
          )}
        </div>

        {renderRoute()}

        <div style={{ marginTop: 24, background: "#f0f4f8", borderRadius: 8, padding: 16, fontFamily: "monospace", fontSize: 14, minHeight: 80 }}>
          <b>Response:</b>
          <pre style={{ margin: 0, whiteSpace: "pre-wrap" }}>{response}</pre>
        </div>
        <div style={{ marginTop: 12, color: "#888", fontSize: 13, textAlign: "center" }}>
          <span>Admin demo: admin@example.com / adminpass</span>
        </div>
      </div>
    </div>
  );
}

export default App;
