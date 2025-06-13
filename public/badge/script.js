import { getData } from "/shared/fetch.js";

document.addEventListener("DOMContentLoaded", () => {
  const pageTitle = document.getElementById("page-title");
  const crachaCodigo = document.getElementById("cracha-codigo");
  const userPhoto = document.getElementById("user-photo");
  const crachaNome = document.getElementById("cracha-nome");
  const qrcodeContainer = document.getElementById("cracha-qrcode");
  const messageElement = document.getElementById("message");

  const loadCrachaData = async () => {
    try {
      const data = await getData("/users/me");

      if (!data || !data.user) {
        throw new Error(
          "Dados do usuário não encontrados na resposta do servidor."
        );
      }

      const { user } = data;

      pageTitle.textContent = `Crachá - ${user.username}`;
      crachaNome.textContent = user.username;
      crachaCodigo.textContent = `Código: ${user.rm}`;
      userPhoto.src = user.photo || "/badge/img/user.png";
      userPhoto.alt = `Foto de ${user.username}`;

      generateQRCode(user.id, qrcodeContainer);
    } catch (error) {
      console.error("Falha ao carregar dados do crachá:", error);
      messageElement.textContent = `Erro: ${error.message}`;
      messageElement.classList.add("error");
      window.location.href = "/login/index.html";
    }
  };

  const generateQRCode = (text, container) => {
    QRCode.toCanvas(container, text, { width: 150, margin: 1 }, (err) => {
      if (err) {
        console.error("Erro ao gerar QR Code:", err);
        container
          .getContext("2d")
          .clearRect(0, 0, container.width, container.height);
        container.style.display = "none";
      }
    });
  };

  loadCrachaData();
});
