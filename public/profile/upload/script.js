import { getData, patchFormData } from "/shared/fetch.js";

const previewImg = document.getElementById("photoPreview");
const fileInput = document.getElementById("profilePhoto");
const submitBtn = document.getElementById("submitBtn");
const statusDiv = document.getElementById("statusMessage");
const usernameEl = document.getElementById("usernameDisplay");
const emailEl = document.getElementById("emailDisplay");
const rmEl = document.getElementById("rmDisplay");

async function loadUserInfo() {
  try {
    const { user } = await getData("/users/me");
    // preenche dados
    previewImg.src = user.photo || previewImg.src;
    usernameEl.textContent = `Usuário: ${user.username}`;
    emailEl.textContent = `Email: ${user.email}`;
    rmEl.textContent = `RM: ${user.rm}`;
  } catch (err) {
    console.warn("Erro ao obter dados do usuário:", err.message);
    showStatus("Não foi possível carregar dados do usuário.", "error");
  }
}

fileInput.addEventListener("change", (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (evt) => (previewImg.src = evt.target.result);
  reader.readAsDataURL(file);
});

submitBtn.addEventListener("click", async () => {
  const file = fileInput.files[0];
  if (!file)
    return showStatus("Selecione um arquivo antes de enviar.", "error");
  if (file.size > 5 * 1024 * 1024)
    return showStatus("O arquivo é muito grande (máx. 5MB).", "error");

  const form = new FormData();
  form.append("photo", file);

  try {
    const id = localStorage.getItem("userId");

    if (!id) {
      window.location.href = "/login/index.html";
      throw new Error("id do user não encontrado.");
    }

    const res = await patchFormData(`/users/${id}/photo`, form);
    showStatus(res.message || "Foto atualizada com sucesso!", "success");
    await loadUserInfo();
  } catch (err) {
    showStatus(`Erro: ${err.message}`, "error");
  }
});

function showStatus(msg, type) {
  statusDiv.textContent = msg;
  statusDiv.className = `status-message ${type}`;
  statusDiv.hidden = false;
}

// carrega na inicialização
loadUserInfo();
