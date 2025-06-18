import { publicPostData } from "/shared/fetch.js";

document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  const submitButton = document.getElementById("submitButton");
  const messageElement = document.getElementById("message");

  loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const formData = new FormData(loginForm);
    const rm = parseInt(formData.get("rm"));
    const password = formData.get("password");

    submitButton.disabled = true;
    submitButton.textContent = "Entrando...";
    messageElement.textContent = "";
    messageElement.className = "message";

    try {
      if (!rm || !password) {
        throw new Error("Usuário e senha são obrigatórios.");
      }

      const result = await publicPostData(`/auth/login`, {
        rm,
        password,
      });

      if (result.token) {
        localStorage.setItem("jwtToken", result.token);
        localStorage.setItem("userId", result.user.id);

        messageElement.textContent =
          "Login realizado com sucesso! Redirecionando...";
        messageElement.classList.add("success");

        window.location.href = "/dashboard";
      } else {
        throw new Error(result.message || "Credenciais inválidas.");
      }
    } catch (error) {
      console.error("Erro ao fazer login:", error);
      messageElement.textContent = `Erro: ${error.message}`;
      messageElement.classList.add("error");
    } finally {
      if (!messageElement.classList.contains("success")) {
        submitButton.disabled = false;
        submitButton.textContent = "Login";
      }
    }
  });
});
