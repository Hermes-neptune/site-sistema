import { postFormData } from "/shared/fetch.js";

document.addEventListener("DOMContentLoaded", () => {
  const registerForm = document.getElementById("registerForm");
  const submitButton = document.getElementById("submitButton");
  const messageElement = document.getElementById("message");

  registerForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const formData = new FormData(registerForm);
    const data = Object.fromEntries(formData.entries());

    submitButton.disabled = true;
    submitButton.textContent = "Cadastrando...";
    messageElement.textContent = "";
    messageElement.className = "message";

    try {
      if (
        !data.username ||
        !data.nome_completo ||
        !data.email ||
        !data.password
      ) {
        throw new Error("Por favor, preencha todos os campos.");
      }

      if (data.password.length < 6) {
        throw new Error("A senha deve ter no mínimo 6 caracteres.");
      }

      await postFormData(`/users`, formData);

      messageElement.textContent =
        "Cadastro realizado com sucesso! Redirecionando...";
      messageElement.classList.add("success");

      window.location.href = "../dashboard";
    } catch (error) {
      console.error("Erro no cadastro:", error);
      messageElement.textContent = `Erro: ${error.message}`;
      messageElement.classList.add("error");
    } finally {
      if (!messageElement.classList.contains("success")) {
        submitButton.disabled = false;
        submitButton.textContent = "Cadastrar";
      }
    }
  });
});
