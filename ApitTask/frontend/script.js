// غيّر المسار حسب مكان مجلد المشروع عندك في htdocs / www
const API_URL = "http://localhost/OOP_API_CRUD/api/users.php";

const form      = document.getElementById("userForm");
const idInput   = document.getElementById("userId");
const nameInput = document.getElementById("name");
const emailInput= document.getElementById("email");
const passInput = document.getElementById("password");
const submitBtn = document.getElementById("submitBtn");
const cancelBtn = document.getElementById("cancelBtn");
const tbody     = document.getElementById("usersBody");
const msg       = document.getElementById("msg");

// ---------- READ ----------
async function loadUsers() {
    const res = await fetch(API_URL);
    const users = await res.json();

    tbody.innerHTML = "";
    users.forEach(u => {
        const tr = document.createElement("tr");

        // textContent بدل innerHTML عشان نحمي من XSS
        const tdName = document.createElement("td");
        tdName.textContent = u.name;
        const tdEmail = document.createElement("td");
        tdEmail.textContent = u.email;

        const editBtn = document.createElement("button");
        editBtn.textContent = "Edit";
        editBtn.onclick = () => startEdit(u);
        const tdEdit = document.createElement("td");
        tdEdit.appendChild(editBtn);

        const delBtn = document.createElement("button");
        delBtn.textContent = "Delete";
        delBtn.onclick = () => deleteUser(u.id);
        const tdDel = document.createElement("td");
        tdDel.appendChild(delBtn);

        tr.append(tdName, tdEmail, tdEdit, tdDel);
        tbody.appendChild(tr);
    });
}

// ---------- CREATE + UPDATE ----------
form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const body = {
        name: nameInput.value,
        email: emailInput.value,
        password: passInput.value
    };

    const isEdit = idInput.value !== "";
    const url = isEdit ? `${API_URL}?id=${idInput.value}` : API_URL;

    const res = await fetch(url, {
        method: isEdit ? "PUT" : "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body)
    });
    const data = await res.json();

    msg.textContent = data.message || data.error;
    if (res.ok) {
        resetForm();
        loadUsers();
    }
});

// ---------- فتح وضع التعديل ----------
function startEdit(user) {
    idInput.value    = user.id;
    nameInput.value  = user.name;
    emailInput.value = user.email;
    passInput.value  = "";
    passInput.placeholder = "New password (optional)";
    submitBtn.textContent = "Save Changes";
    cancelBtn.hidden = false;
}

function resetForm() {
    form.reset();
    idInput.value = "";
    passInput.placeholder = "Password (min 8)";
    submitBtn.textContent = "Add User";
    cancelBtn.hidden = true;
}
cancelBtn.onclick = resetForm;

// ---------- DELETE ----------
async function deleteUser(id) {
    if (!confirm("Are you sure?")) return;

    const res = await fetch(`${API_URL}?id=${id}`, { method: "DELETE" });
    const data = await res.json();
    msg.textContent = data.message || data.error;
    loadUsers();
}

loadUsers();
