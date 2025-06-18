import { getData } from "/shared/fetch.js";

if (!localStorage.getItem("jwtToken")) {
  window.location.href = "/login/index.html";
}

function isSuccessfulResponse(response) {
  return response && !response.error && !response.message;
}

document.addEventListener("DOMContentLoaded", async function () {
  const logoutButton = document.getElementById("logoutBtn");
  if (logoutButton) {
    logoutButton.addEventListener("click", function (event) {
      event.preventDefault();
      localStorage.clear();
      window.location.href = "/login/index.html";
    });
  }

  try {
    try {
      const checkinResponse = await getData("/users/checkin");
      if (checkinResponse.error) {
        console.warn("Checkin:", checkinResponse.error);
      }
    } catch (error) {
      console.warn("Erro ao registrar presença:", error);
    }

    const userData = await getData("/users/me");
    if (isSuccessfulResponse(userData) && userData.user) {
      const user = userData.user;

      setTextContent("headerUsername", user.username);
      setTextContent("sidebarUsername", user.username);
      setTextContent("sidebarEmail", user.email);
      setTextContent("welcomeUsername", user.username);

      const userPhotoUrl = user.photo || "/dashboard/img/user.png";
      setImageSource("userPhoto", userPhotoUrl);
      setImageSource("cardUserPhoto", userPhotoUrl);

      const [attendanceData, messagesData, pendingData, newsData, creditsData] =
        await Promise.all([
          getData(`/users/${user.id}/presencas/semana-atual`),
          getData("/mensagens"),
          getData(`/users/${user.id}/pendencias`),
          getData("/noticias"),
          getData(`/creditos/user/${user.username}`),
        ]);

      if (isSuccessfulResponse(attendanceData) && attendanceData.dias) {
        renderAttendance(attendanceData.dias);
      }
      if (Array.isArray(messagesData)) {
        renderMessages(messagesData);
      }
      if (Array.isArray(pendingData)) {
        renderPending(pendingData);
      }
      if (Array.isArray(newsData)) {
        renderNews(newsData);
      }
      if (creditsData && creditsData.totalCreditos !== undefined) {
        setTextContent("totalCreditos", creditsData.totalCreditos);
      }
    }
  } catch (error) {
    console.error("Erro ao carregar dados da página:", error);
    if (
      error.message.includes("401") ||
      error.message.includes("autenticado")
    ) {
      localStorage.clear();
      window.location.href = "/login/index.html";
    } else {
      alert("Erro ao carregar dados. Verifique sua conexão.");
    }
  }
});

function setTextContent(elementId, text) {
  const element = document.getElementById(elementId);
  if (element) element.textContent = text || "";
}

function setImageSource(elementId, src) {
  const element = document.getElementById(elementId);
  if (element) element.src = src;
}

function renderAttendance(dias) {
  const checklist = document.getElementById("attendanceChecklist");
  if (!checklist) return;
  const daysOfWeek = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"];
  checklist.innerHTML = dias
    .map(
      (dia, index) => `
    <div class="day">
      <div class="circle ${dia.presente ? "present" : ""}" aria-label="${
        daysOfWeek[index]
      }: ${dia.presente ? "Presente" : "Ausente"}">
        ${dia.presente ? "✔️" : ""}
      </div>
      <div class="day-name">${daysOfWeek[index]}</div>
    </div>
  `
    )
    .join("");
}

function renderMessages(mensagens) {
  const messagesList = document.getElementById("messagesList");
  if (!messagesList) return;
  messagesList.innerHTML = mensagens.length
    ? mensagens
        .map(
          (msg) => `
    <li class="message-item" tabindex="0" data-overlay-text="${msg.mensagem}">
      <span class="message-date">${new Date(msg.data_criacao).toLocaleString(
        "pt-BR"
      )}</span>
      <span class="message-content">${msg.mensagem}</span>
    </li>
  `
        )
        .join("")
    : '<li class="no-messages">Nenhuma mensagem disponível.</li>';
}

function renderPending(pendencias) {
  const pendingList = document.getElementById("pendingList");
  if (!pendingList) return;
  pendingList.innerHTML = pendencias.length
    ? pendencias
        .map(
          (pend) => `
    <li class="pending-item" tabindex="0" data-overlay-text="${pend.descricao}">
      ${pend.descricao} - ${pend.status}
    </li>
  `
        )
        .join("")
    : '<li class="no-pending">Nenhuma pendência disponível.</li>';
}

function renderNews(noticias) {
  const newsList = document.getElementById("newsList");
  if (!newsList) return;
  newsList.innerHTML = noticias.length
    ? noticias
        .map(
          (noticia) => `
    <li class="news-item" tabindex="0" data-overlay-text="${noticia.detalhes}">
      <span class="news-date">${new Date(noticia.data).toLocaleDateString(
        "pt-BR"
      )}</span>
      <span class="news-title">${noticia.assunto}</span>
    </li>
  `
        )
        .join("")
    : '<li class="no-news">Nenhuma notícia disponível.</li>';
}
