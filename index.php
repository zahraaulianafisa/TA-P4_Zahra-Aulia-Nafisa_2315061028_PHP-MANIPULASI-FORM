<?php
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Manager</title>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<style>
    :root {
        --primary: #2563eb;
        --danger: #ef4444;
        --info: #0ea5e9;
        --bg: #cfe8e0;
        --card: #ffffff;
        --radius: 12px;
    }

    body {
        font-family: Arial, sans-serif;
        background: var(--bg);
        margin: 0;
        padding: 0;
    }

    .auth-container {
        max-width: 420px;
        margin: 50px auto;
        padding: 25px;
        background: var(--card);
        border-radius: var(--radius);
        box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    }
    .auth-form { display: none; }
    .auth-form.active { display: block; }
    .auth-form h2 { margin-bottom: 15px; }

    input, select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: var(--radius);
        margin-bottom: 12px;
    }

    button {
        width: 100%;
        padding: 10px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: var(--radius);
        cursor: pointer;
    }
    button:hover { opacity: .9; }

    .switch-link {
        text-align: center;
        margin-top: 10px;
        cursor: pointer;
        color: var(--primary);
    }

    #dashboard {
        display: none;
        max-width: 850px;
        margin: 20px auto;
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--card);
        padding: 15px;
        border-radius: var(--radius);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .action-btn {
        padding: 8px 15px;
        border-radius: var(--radius);
        color: white;
        cursor: pointer;
        border: none;
    }
    .export { background: green; }
    .logout { background: var(--danger); }

    .contact-card {
        background: var(--card);
        padding: 15px;
        border-radius: var(--radius);
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
    }

    .contact-actions button {
        width: auto;
        padding: 6px 12px;
        margin-left: 5px;
        border-radius: var(--radius);
        color: white;
        border: none;
    }

    .form-card {
        background: white;
        margin-top: 20px;
        padding: 20px;
        border-radius: var(--radius);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>

</head>
<body>


<div class="auth-container">

    <form id="loginForm" class="auth-form active">
        <h2>🔑 Login</h2>
        <input id="loginEmail" type="email" placeholder="Email" required>
        <input id="loginPassword" type="password" placeholder="Password" required>

        <button type="submit">Masuk</button>

        <div class="switch-link" onclick="showForm('register')">Buat Akun</div>
        <div class="switch-link" onclick="showForm('forgot')">Lupa Password?</div>
    </form>

    <form id="registerForm" class="auth-form">
        <h2>Register</h2>
        <input id="regName" placeholder="Nama Lengkap" required>
        <input id="regEmail" type="email" placeholder="Email" required>
        <input id="regPassword" type="password" placeholder="Password" required>

        <select id="securityQuestion" required>
            <option value="">Pilih Pertanyaan Keamanan</option>
            <option value="kota_lahir">Apa nama kota tempat Anda lahir?</option>
        </select>

        <input id="securityAnswer" placeholder="Jawaban" required>

        <button type="submit">Daftar</button>

        <div class="switch-link" onclick="showForm('login')">Kembali ke Login</div>
    </form>

    <form id="forgotForm" class="auth-form">
        <h2>Reset Password</h2>

        <input id="forgotEmail" type="email" placeholder="Email Anda" required>

        <select id="forgotQuestion" required>
            <option value="">Pilih Pertanyaan Keamanan</option>
            <option value="kota_lahir">Apa nama kota tempat Anda lahir?</option>
        </select>

        <input id="forgotAnswer" placeholder="Jawaban Anda" required>

        <input id="newPassword" type="password" placeholder="Password Baru" required>

        <button type="submit">Atur Ulang Password</button>

        <div class="switch-link" onclick="showForm('login')">Kembali ke Login</div>
    </form>

</div>


<div id="dashboard">

    <div class="top-bar">
        <h2>📒 Contact Manager</h2>

        <div>
            <button class="action-btn export" onclick="generatePDF()">Export PDF</button>
            <button class="action-btn logout" onclick="logout()">Logout</button>
        </div>
    </div>

    <div class="form-card">
        <h3>➕ Tambah Kontak</h3>

        <form id="contactForm">
            <input id="contactName" placeholder="Nama" required>
            <input id="contactEmail" type="email" placeholder="Email" required>
            <input id="contactPhone" placeholder="Telepon" required>
            <input id="contactAddress" placeholder="Alamat">

            <button type="submit">💾 Simpan Kontak</button>
        </form>
    </div>

    <div class="form-card">
        <input id="searchInput" placeholder="🔍 Cari kontak...">
    </div>

    <div id="contactsContainer"></div>

</div>



<script>

let users = JSON.parse(localStorage.getItem('users')) || [];
let contacts = JSON.parse(localStorage.getItem('contacts')) || [];
let currentUser = null;
let isEditing = false;
let currentEditId = null;

function showForm(type) {
    document.querySelectorAll(".auth-form").forEach(f => f.classList.remove("active"));
    document.getElementById(type + "Form").classList.add("active");
}

document.getElementById("loginForm").addEventListener("submit", e => {
    e.preventDefault();
    const email = loginEmail.value;
    const pass = loginPassword.value;

    const user = users.find(u => u.email === email && u.password === pass);

    if (!user) return Swal.fire("Gagal", "Email atau password salah", "error");

    currentUser = user;
    document.querySelector(".auth-container").style.display = "none";
    dashboard.style.display = "block";

    displayContacts();
    Swal.fire("Sukses", "Berhasil masuk!", "success");
});

document.getElementById("registerForm").addEventListener("submit", e => {
    e.preventDefault();

    const user = {
        id: Date.now(),
        name: regName.value,
        email: regEmail.value,
        password: regPassword.value,
        security: {
            question: securityQuestion.value,
            answer: securityAnswer.value.toLowerCase()
        }
    };

    if (users.some(u => u.email === user.email))
        return Swal.fire("Gagal", "Email sudah terdaftar!", "error");

    users.push(user);
    localStorage.setItem("users", JSON.stringify(users));

    Swal.fire("Sukses", "Registrasi berhasil!", "success");
    showForm("login");
});

document.getElementById("forgotForm").addEventListener("submit", e => {
    e.preventDefault();

    const email = forgotEmail.value;
    const user = users.find(u => u.email === email);

    if (!user) return Swal.fire("Gagal", "Pengguna tidak ditemukan!", "error");

    if (
        user.security.question !== forgotQuestion.value ||
        user.security.answer !== forgotAnswer.value.toLowerCase()
    ) return Swal.fire("Gagal", "Verifikasi keamanan gagal!", "error");

    user.password = newPassword.value;
    localStorage.setItem("users", JSON.stringify(users));

    Swal.fire("Sukses", "Password berhasil diubah!", "success");
    showForm("login");
});

document.getElementById("contactForm").addEventListener("submit", e => {
    e.preventDefault();

    const data = {
        id: isEditing ? currentEditId : Date.now(),
        userId: currentUser.id,
        name: contactName.value,
        email: contactEmail.value,
        phone: contactPhone.value,
        address: contactAddress.value
    };

    if (isEditing) {
        const i = contacts.findIndex(c => c.id === data.id);
        contacts[i] = data;
        isEditing = false;
    } else {
        contacts.push(data);
    }

    localStorage.setItem("contacts", JSON.stringify(contacts));
    displayContacts();

    Swal.fire("Sukses", isEditing ? "Kontak berhasil diubah!" : "Kontak berhasil disimpan!", "success");
    contactForm.reset();
});

function displayContacts(list = null) {
    const box = contactsContainer;
    box.innerHTML = "";

    const filtered = list || contacts.filter(c => c.userId === currentUser.id);

    if (!filtered.length) {
        box.innerHTML = "<p>Belum ada kontak. Silakan tambahkan kontak!</p>";
        return;
    }

    filtered.forEach(c => {
        box.innerHTML += `
            <div class="contact-card">
                <div>
                    <h4>${c.name}</h4>
                    <p>📧 ${c.email}</p>
                    <p>📱 ${c.phone}</p>
                </div>

                <div class="contact-actions">
                    <button onclick="viewContact(${c.id})" style="background:var(--info)">👁️ Lihat</button>
                    <button onclick="editContact(${c.id})" style="background:var(--primary)">✏️ Edit</button>
                    <button onclick="deleteContact(${c.id})" style="background:var(--danger)">🗑️ Hapus</button>
                </div>
            </div>
        `;
    });
}

function viewContact(id) {
    const c = contacts.find(x => x.id === id);
    Swal.fire({
        title: c.name,
        html: `
            <p><strong>Email:</strong> ${c.email}</p>
            <p><strong>Telepon:</strong> ${c.phone}</p>
            ${c.address ? `<p><strong>Alamat:</strong> ${c.address}</p>` : ""}
        `
    });
}

function editContact(id) {
    const c = contacts.find(x => x.id === id);

    contactName.value = c.name;
    contactEmail.value = c.email;
    contactPhone.value = c.phone;
    contactAddress.value = c.address;

    isEditing = true;
    currentEditId = id;

    window.scrollTo(0,0);
}

function deleteContact(id) {
    Swal.fire({
        title: "Hapus kontak?",
        text: "Tindakan ini tidak dapat dibatalkan!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Hapus'
    }).then(r => {
        if (r.isConfirmed) {
            contacts = contacts.filter(c => c.id !== id);
            localStorage.setItem("contacts", JSON.stringify(contacts));
            displayContacts();

            Swal.fire("Dihapus!", "Kontak telah dihapus.", "success");
        }
    });
}

document.getElementById("searchInput").addEventListener("input", e => {
    const t = e.target.value.toLowerCase();
    const filtered = contacts.filter(c =>
        c.userId === currentUser.id &&
        (c.name.toLowerCase().includes(t) ||
         c.email.toLowerCase().includes(t) ||
         c.phone.toLowerCase().includes(t) ||
         (c.address && c.address.toLowerCase().includes(t)))
    );
    displayContacts(filtered);
});

function generatePDF() {
    const { jsPDF } = window.jspdf;

    const doc = new jsPDF();

    doc.setFontSize(18);
    doc.text("Daftar Kontak", 14, 20);

    const rows = contacts
        .filter(c => c.userId === currentUser.id)
        .map(c => [c.name, c.email, c.phone, c.address || "N/A"]);

    doc.autoTable({
        head: [["Nama", "Email", "Telepon", "Alamat"]],
        body: rows,
        startY: 30
    });

    doc.save("contacts.pdf");
}

function logout() {
    currentUser = null;
    dashboard.style.display = "none";
    document.querySelector(".auth-container").style.display = "block";
    showForm("login");
}
</script>

</body>
</html>
